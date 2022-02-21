import json
import os
from datetime import date, datetime, timedelta
import vobject
from urllib.request import urlopen, Request
import re

def convert_period(x, y):
    table = {
        "50": {
            "07:40:00+00:00": 1,
            "08:30:00+00:00": 2,
            "09:20:00+00:00": 3,
            "10:30:00+00:00": 4,
            "11:20:00+00:00": 5,
            "12:40:00+00:00": 6,
            "13:30:00+00:00": 7,
            "14:25:00+00:00": 8,
            "15:15:00+00:00": 9
        },
        "30": {
            "07:40:00+00:00": 1,
            "08:10:00+00:00": 2,
            "08:40:00+00:00": 3,
            "09:30:00+00:00": 4,
            "10:00:00+00:00": 5,
            "10:30:00+00:00": 6,
            "11:30:00+00:00": 7,
            "12:00:00+00:00": 8,
            "12:30:00+00:00": 9
        }
    }
    try:
        value = table[y][x]
    except:
        value = 0
    return value


def get_data(_user):
    print('Executing for user: '+_user['code'])
    url = _user['url']
    headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.3'}
    req = Request(url=url, headers=headers)
    data = urlopen(req).read().decode('iso-8859-1')

    path = './events/'+_user['code']+'.json'
    if os.path.exists(path):
        f = open(path, 'r')
        userdata = json.loads(f.read())
        f.close()
    else:
        userdata = {}


    for read in vobject.readComponents(data):
        for component in read.components():
            if component.name == "VEVENT":
                desc = component.description.valueRepr().split()
                if len(desc) < 4:
                    continue
                old_name = component.summary.valueRepr().replace('2122-', '').split()
                del old_name[0]
                if desc[0] == '[X]':
                    summary = "[X] "
                    n = 3
                    if desc[1] == "[!]":
                        n=4
                    location = "Lokaal " + desc[n]
                    teacher = desc[n+1]
                    group = desc[n-1]
                    status = "CANCELLED"
                elif desc[0] == '[!]':
                    summary = "[!] "
                    location = "Lokaal " + component.description.valueRepr().split(');')[1].split(' ')[1]
                    teacher = component.description.valueRepr().split(');')[1].split(' ')[2]
                    s = component.description.valueRepr()
                    group = s[s.find('(')+1:s.find(');')]
                    status = "TENTATIVE"
                else:
                    summary = ""
                    location = "Lokaal " + desc[2]
                    teacher = desc[3]
                    group = desc[1]
                    status = "CONFIRMED"
                if "docent is vervangen" in component.description.valueRepr():
                    status = "TENTATIVE"
                summary += ' '.join(old_name)
                del desc[0:4]
                summary += ' ' + ' '.join(desc)
                id = component.uid.valueRepr()
                userdata[id] = {
                    "summary": summary,
                    "location": location,
                    "teacher": re.sub("(\(|\)|\\n|\\r)", "", teacher),
                    "group": re.sub("(\(|\)|\;|\\n|\\r)", "", group).replace(' ', ';'),
                    "status": status,
                    "start": str(component.dtstart.valueRepr()),
                    "end": str(component.dtend.valueRepr()),
                    "stamp": str(component.dtstamp.valueRepr()),
                    "description": component.description.valueRepr(),
                    "dertig": convert_period(str(component.dtstart.valueRepr()).split()[1], "30"),
                    "vijftig": convert_period(str(component.dtstart.valueRepr()).split()[1], "50")
                }
                print('next event')
    print('Dumping data')
    with open(path, 'w') as f:
        f.write(json.dumps(userdata))
    user = _user
    user['last_updated'] = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    return user


def dateInRange(s, e, x):
    if s <= e:
        return s <= x <= e
    else:
        return s <= x or x <= e


def mergeFiles(_users):
    events = {}
    for user in _users:
        # Get user file
        with open('./events/'+user['code']+'.json', 'r') as f:
            data = json.loads(f.read())
            for key, event in data.items():
                if dateInRange(datetime.now().strftime('%Y-%m-%d'), (datetime.now() + timedelta(days=1)).strftime('%Y-%m-%d'), datetime.strptime(event['start'].split(' ')[0], '%Y-%m-%d').strftime('%Y-%m-%d')) and event['status'] != "CONFIRMED":
                    events[key] = event
    with open('./events/combined.json', 'w') as f:
        f.write(json.dumps(events))


def calculateChanges():
    old_data = {}
    changes = []
    with open('./events/combined.old.json', 'r') as f:
        old_data = json.loads(f.read())
    
    with open('./events/combined.json', 'r') as f:
        for key, event in json.loads(f.read()).items():
            if key in old_data:
                if (old_data[key]['status'] == event['status']):
                    continue
            changes.append(event)

    print(changes)

def prepareUpdate(_changes):
    messages = []
    for i,change in _changes.items():
        start = change['start'].split('+')[0].split(' ')[1].split(':')
        desc = change['description'].split(' ')
        if "docent is vervangen" in change['description']:
            message = {}
            original_teacher = change['summary'][change['summary'].find('(')+5:change['summary'].find(').')].upper()
            message['color'] = "#ff6f00"
            message['title'] = "Docent vervangen"
            message['description'] = desc[0]+' '+change['group']
            message['vak'] = desc[0]
            message['fields'] = [
                {"name": "Lesuur", "value": change['vijftig']},
                {"name": "Tijd", "value": ':'.join([
                    str(int(start[0])+1),
                    str(start[1])])},
                {"name": "Originele docent", "value": original_teacher},
                {"name": "Nieuwe docent", "value": re.sub('(\(|\))', '', desc[3])}
            ]
            messages.append(message)
        if "Les vervalt" in change['description']:
            message = {}
            message['color'] = "#ff0000"
            message['title'] = "Les vervalt"
            message['description'] = desc[1]+' '+change['group']
            message['vak'] = desc[1]
            message['fields'] = [
                {"name": "Lesuur", "value": change['vijftig']},
                {"name": "Tijd", "value": ':'.join([
                    str(int(start[0])+1),
                    str(start[1])])},
                {"name": "Docent", "value": re.sub('(\(|\))', '', desc[4])}
            ]
            messages.append(message)
        if "lokaal is gewijzigd" in change['description']:
            message = {}
            old_room = change['summary'].split(')')[1].split(' ')[-1]
            message['color'] = "#ff6f00"
            message['title'] = "Lokaal gewijzigd"
            message['description'] = desc[1]+' '+change['group']
            message['vak'] = desc[1]
            message['fields'] = [
                {"name": "Lesuur", "value": change['vijftig']},
                {"name": "Tijd", "value": ':'.join([
                    str(int(start[0])+1),
                    str(start[1])])},
                {"name": "Oude lokaal", "value": old_room},
                {"name": "Nieuwe lokaal", "value": change['location'].split(' ')[1]}
            ]
            
    with open('./data/changes.json', 'w') as f:
        f.write(json.dumps(messages))