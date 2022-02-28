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


# def get_data(_user):
#     url = _user['url']
#     headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.3'}
#     req = Request(url=url, headers=headers)
#     data = urlopen(req).read().decode('iso-8859-1')

#     path = './events/'+_user['code']+'.json'
#     if os.path.exists(path):
#         f = open(path, 'r')
#         userdata = json.loads(f.read())
#         f.close()
#     else:
#         userdata = {}


#     for read in vobject.readComponents(data):
#         for component in read.components():
#             if component.name == "VEVENT":
#                 desc = component.description.valueRepr().split()
#                 if len(desc) < 4:
#                     continue
#                 old_name = component.summary.valueRepr().replace('2122-', '').split()
#                 del old_name[0]
#                 if desc[0] == '[X]':
#                     summary = "[X] "
#                     n = 3
#                     if desc[1] == "[!]":
#                         n=4
#                     location = "Lokaal " + desc[n]
#                     teacher = desc[n+1]
#                     vak = desc[n-2]
#                     group = desc[n-1]
#                     status = "CANCELLED"
#                 elif desc[0] == '[!]':
#                     summary = "[!] "
#                     location = "Lokaal " + component.description.valueRepr().split(');')[1].split(' ')[1]
#                     teacher = component.description.valueRepr().split(');')[1].split(' ')[2]
#                     s = component.description.valueRepr()
#                     group = s[s.find('(')+1:s.find(');')]
#                     vak = desc[1]
#                     # for i in range(len(desc)):
#                     #     try:
#                     #         if (desc[i+1] == f"({group});"):
#                     #             print(desc[i])
#                     #             vak = desc[i]
#                     #     except:
#                     #         print(f'Error while looking for ({group}); in:')
#                     #         print(desc)
#                     #         print(f'But found: {desc[i]}')
#                     #         exit()
                        
#                         # if desc[i+1] == f"({group});":
#                         #     vak = desc[i]
#                         #     break
#                     status = "TENTATIVE"
#                 elif desc[0] == '[Ongeldig]':
#                     summary = "[X] "
#                     n = 3
#                     if desc[1] == "[!]":
#                         n=4
#                     location = "Lokaal " + desc[n]
#                     teacher = desc[n+1]
#                     vak = desc[n-2]
#                     group = desc[n-1]
#                     status = "CANCELLED"
#                 else:
#                     summary = ""
#                     location = "Lokaal " + desc[2]
#                     teacher = desc[3]
#                     vak = desc[0]
#                     group = desc[1]
#                     status = "CONFIRMED"
#                 if "docent is vervangen" in component.description.valueRepr():
#                     status = "TENTATIVE"
#                 summary += ' '.join(old_name)
#                 del desc[0:4]
#                 summary += ' ' + ' '.join(desc)
#                 id = component.uid.valueRepr()
#                 group = re.sub("(\(|\)|\;|\\n|\\r)", "", group).replace(' ', ';')
#                 if len(group) == 4:
#                     if (group[0:2] == "vw"):
#                         group = group.replace('vw', 'vwo')
#                         group = group[0:len(group)-1]+'.'+group[-1]+'('+vak+')'
#                     else:
#                         group = group+"."+vak
#                 userdata[id] = {
#                     "summary": summary,
#                     "location": location,
#                     "teacher": re.sub("(\(|\)|\\n|\\r)", "", teacher),
#                     "group": group,
#                     "vak": vak,
#                     "status": status,
#                     "start": str(component.dtstart.valueRepr()),
#                     "end": str(component.dtend.valueRepr()),
#                     "stamp": str(component.dtstamp.valueRepr()),
#                     "description": component.description.valueRepr(),
#                     "dertig": convert_period(str(component.dtstart.valueRepr()).split()[1], "30"),
#                     "vijftig": convert_period(str(component.dtstart.valueRepr()).split()[1], "50")
#                 }
#     with open(path, 'w') as f:
#         f.write(json.dumps(userdata))
#     user = _user
#     user['last_updated'] = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
#     return user


def dateInRange(s, e, x):
    if s <= e:
        return s <= x <= e
    else:
        return s <= x or x <= e


def mergeFiles(_users):
    events = {}
    groups = []
    for user in _users:
        # Get user file
        with open('./events/'+user['code']+'.json', 'r') as f:
            data = json.loads(f.read())
            for key, event in data.items():
                if dateInRange(datetime.now().strftime('%Y-%m-%d'), (datetime.now() + timedelta(days=1)).strftime('%Y-%m-%d'), datetime.strptime(event['start'].split(' ')[0], '%Y-%m-%d').strftime('%Y-%m-%d')) and event['status'] != "CONFIRMED":
                    events[key] = event
                if event['group'] not in groups:
                    groups.append(event['group'])
    with open('./data/combined.json', 'w') as f:
        f.write(json.dumps(events))
    with open('./data/groups.json', 'w') as f:
        f.write(json.dumps(groups))


# def calculateChanges():
#     old_data = {}
#     changes = []
#     with open('./data/combined.old.json', 'r') as f:
#         old_data = json.loads(f.read())
    
#     with open('./data/combined.json', 'r') as f:
#         for key, event in json.loads(f.read()).items():
#             if key in old_data:
#                 if (old_data[key]['status'] == event['status']):
#                     continue
#             changes.append(event)
#     return changes
    

def prepareUpdate(_changes):
    messages = []
    for change in _changes:
        start = change['start'].split('+')[0].split(' ')[1].split(':')
        end = change['end'].split('+')[0].split(' ')[1].split(':')
        desc = change['description'].split(' ')
        start_time = ':'.join([str(int(start[0])+1), str(start[1])])
        end_time = ':'.join([str(int(end[0])+1), str(end[1])])
        if "docent is vervangen" in change['description']:
            message = {}
            original_teacher = re.search('(was [a-z]{3}[0-9]{2})', change['summary']).group(0).split(' ')[1].upper()
            message['color'] = "#ffff00"
            message['title'] = "Docent vervangen"
            if desc[0] == "[!]":
                message['vak'] = desc[1]
            else:
                message['vak'] = desc[0]
            message['description'] = message['vak']+' '+change['group']
            message['time'] = f'{start_time}-{end_time}'
            message['dertig']=change['dertig']
            message['vijftig']=change['vijftig']
            message['date'] = change['start'].split('+')[0].split(' ')[0]
            message['teacher'] = original_teacher
            try:
                new_teacher = re.search('([A-Z]{3}[0-9]{2})', change['description']).group(0)
            except:
                new_teacher = re.search('([A-Z]{5}[0-9]{1})', change['description']).group(0)
            message['fields'] = [
                {"name": "Lesuur", "value": change['vijftig']},
                {"name": "Tijd", "value": f'{start_time}-{end_time}'},
                {"name": "Originele docent", "value": original_teacher},
                {"name": "Nieuwe docent", "value": new_teacher},
                {"name": "Datum", "value": datetime.strptime(change['start'].split('+')[0].split(' ')[0], '%Y-%m-%d').strftime('%d-%m-%Y')}
            ]
            messages.append(message)
        if "Les vervalt" in change['description']:
            message = {}
            message['color'] = "#ff0000"
            message['title'] = "Les vervalt"
            message['description'] = desc[1]+' '+change['group']
            message['time'] = f'{start_time}-{end_time}'
            message['dertig']=change['dertig']
            message['vijftig']=change['vijftig']
            message['date'] = change['start'].split('+')[0].split(' ')[0]
            message['vak'] = desc[1]
            message['teacher'] = change['teacher']
            message['fields'] = [
                {"name": "Lesuur", "value": change['vijftig']},
                {"name": "Tijd", "value": f'{start_time}-{end_time}'},
                {"name": "Docent", "value": re.sub('(\(|\))', '', desc[4])},
                {"name": "Datum", "value": datetime.strptime(change['start'].split('+')[0].split(' ')[0], '%Y-%m-%d').strftime('%d-%m-%Y')}
            ]
            messages.append(message)
        if "lokaal is gewijzigd" in change['description']:
            message = {}
            old_room = change['summary'].split(')')[1].split(' ')[-1]
            message['color'] = "#0000ff"
            message['title'] = "Lokaal gewijzigd"
            message['description'] = desc[1]+' '+change['group']
            message['time'] = f'{start_time}-{end_time}'
            message['dertig']=change['dertig']
            message['vijftig']=change['vijftig']
            message['date'] = change['start'].split('+')[0].split(' ')[0]
            message['vak'] = desc[1]
            message['teacher'] = change['teacher']
            message['fields'] = [
                {"name": "Lesuur", "value": change['vijftig']},
                {"name": "Tijd", "value": f'{start_time}-{end_time}'},
                {"name": "Oude lokaal", "value": old_room},
                {"name": "Nieuwe lokaal", "value": change['location'].split(' ')[1]},
                {"name": "Datum", "value": datetime.strptime(change['start'].split('+')[0].split(' ')[0], '%Y-%m-%d').strftime('%d-%m-%Y')}
            ]
            messages.append(message)
    with open('./data/changes.json', 'w') as f:
        f.write(json.dumps(messages))
    with open('./data/changes_web.json', 'r') as f:
        current_changes = json.loads(f.read())
        for c in current_changes:
            if (str(datetime.strptime(c['date'], '%Y-%m-%d').strftime('%Y-%m-%d')) != str(date.today() - timedelta(days=1))):
                messages.append(c)
    with open('./data/changes_web.json', 'w') as f:
        f.write(json.dumps(messages))
