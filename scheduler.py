import json
import os
from datetime import date, datetime, timedelta
import vobject
from urllib.request import urlopen, Request
import re
import functions

# Get data for user
def get_data(_user):
    url = _user['url']
    headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.3'}
    req = Request(url=url, headers=headers)
    data = urlopen(req).read().decode('iso-8859-1')

    # Try to get previous user file to init userdata
    path = './events/'+_user['code']+'.json'
    if os.path.exists(path):
        f = open(path, 'r')
        userdata = json.loads(f.read())
        f.close()
    else:
        userdata = {}

    replace = ["\\r", "\n"]

    # Loop over events in ItsLearning data
    for read in vobject.readComponents(data):
        for component in read.components():
            if component.name == "VEVENT":
                id = component.uid.valueRepr()
                description = component.description.valueRepr()
                for r in replace:
                    description = description.replace(r, '')
                print(description)
                desc = description.split()
                # Filter out invalid events
                if len(desc) < 4:
                    continue
                old_name = component.summary.valueRepr().replace('2122-', '').split()
                del old_name[0]
                # If event starts with [!]
                summary = ""
                status = "CONFIRMED"
                if '[!]' in desc or 'docent is vervangen' in description:
                    summary = "[!] "
                    status = "TENTATIVE"
                if '[X]' in desc:
                    summary = "[X] "
                    status = "CANCELLED"
                if '[Ongeldig]' in desc:
                    summary = "[Ongeldig] "
                    status = "TENTATIVE"
                summary += ' '.join(old_name)
                vak = re.search(r'[A-Z]{2,}', description).group(0)
                group = ""
                for g in re.search(r'((\())\K.*?(?=\)\;)', description).group(0).split(' '):
                    if len(g) == 4:
                        if (g[0:2] == "vw"):
                            g = g.replace('vw', 'vwo')
                            g = g[0:len(g)-1]+'.'+g[-1]+'('+vak+')'
                        else:
                            g = g+"."+vak
                    group+=g+";"
                userdata[id] = {
                    "summary": summary,
                    "location": re.search(r'(?<=(\)\; )).*?(?= )', description).group(0),
                    "teacher": re.search(r'(([0-9][a-z]? \())\K.*?(?=\))', description).group(0),
                    "group": group[:-1],
                    "vak": vak,
                    "status": status,
                    "start": str(component.dtstart.valueRepr()),
                    "end": str(component.dtend.valueRepr()),
                    "stamp": str(component.dtstamp.valueRepr()),
                    "description": component.description.valueRepr(),
                    "dertig": functions.convert_period(str(component.dtstart.valueRepr()).split()[1], "30"),
                    "vijftig": functions.convert_period(str(component.dtstart.valueRepr()).split()[1], "50")
                }
    with open(path, 'w') as f:
        f.write(json.dumps(userdata))
    user = _user
    user['last_updated'] = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    return user


def calculateChanges():
    old_data = {}
    changes = []
    with open('./data/combined.old.json', 'r') as f:
        old_data = json.loads(f.read())
    
    with open('./data/combined.json', 'r') as f:
        for key, event in json.loads(f.read()).items():
            if key in old_data:
                if (old_data[key]['status'] == event['status']):
                    continue
            changes.append(event)
    return changes


def prepare_update(_changes):
    messages = []
    for change in _changes:
        start = change['start'].split('+')[0].split(' ')[1].split(':')
        end = change['end'].split('+')[0].split(' ')[1].split(':')
        desc = change['description'].split(' ')
        start_time = ':'.join([str(int(start[0])+1), str(start[1])])
        end_time = ':'.join([str(int(end[0])+1), str(end[1])])
        if "docent is vervangen" in change['description']:
            original_teacher = re.search('(was [a-z]{3}[0-9]{2})', change['summary']).group(0).split(' ')[1].upper()
            try:
                new_teacher = re.search('([A-Z]{3}[0-9]{2})', change['description']).group(0)
            except:
                new_teacher = re.search('([A-Z]{5}[0-9]{1})', change['description']).group(0)
            message = {
                "color": "#ffff00",
                "title": "Docent vervangen",
                "vak": change['vak'],
                "description": change['vak']+' '+change['group'],
                "time": f'{start_time}-{end_time}',
                "dertig": change['dertig'],
                "vijftig": change['vijftig'],
                "date": change['start'].split('+')[0].split(' ')[0],
                "teacher": original_teacher,
                "fields": [
                    {"name": "Lesuur", "value": change['vijftig']},
                    {"name": "Tijd", "value": f'{start_time}-{end_time}'},
                    {"name": "Originele docent", "value": original_teacher},
                    {"name": "Nieuwe docent", "value": new_teacher},
                    {"name": "Datum", "value": datetime.strptime(change['start'].split('+')[0].split(' ')[0], '%Y-%m-%d').strftime('%d-%m-%Y')}
                ]
            }
            messages.append(message)
        if "lokaal is gewijzigd" in change['description']:
            old_room = change['summary'].split(')')[1].split(' ')[-1]
            message = {
                "color": "#0000ff",
                "title": "Lokaal gewijzigd",
                "vak": change['vak'],
                "description": desc[1]+' '+change['group'],
                "time": f'{start_time}-{end_time}',
                "dertig": change['dertig'],
                "vijftig": change['vijftig'],
                "date": change['start'].split('+')[0].split(' ')[0],
                "teacher": original_teacher,
                "fields": [
                    {"name": "Lesuur", "value": change['vijftig']},
                    {"name": "Tijd", "value": f'{start_time}-{end_time}'},
                    {"name": "Oude lokaal", "value": old_room},
                    {"name": "Nieuwe lokaal", "value": change['location'].split(' ')[1]},
                    {"name": "Datum", "value": datetime.strptime(change['start'].split('+')[0].split(' ')[0], '%Y-%m-%d').strftime('%d-%m-%Y')}
                ]
            }
            messages.append(message)
        if "Les vervalt" in change['description']:
            message = {
                "color": "#ff0000",
                "title": "Les vervalt",
                "description": change['vak']+' '+change['group'],
                "time": f'{start_time}-{end_time}',
                "dertig": change['dertig'],
                "vijftig": change['vijftig'],
                "date": change['start'].split('+')[0].split(' ')[0],
                "vak": change['vak'],
                "teacher": change['teacher'],
                "fields": [
                    {"name": "Lesuur", "value": change['vijftig']},
                    {"name": "Tijd", "value": f'{start_time}-{end_time}'},
                    {"name": "Docent", "value": re.sub('(\(|\))', '', desc[4])},
                    {"name": "Datum", "value": datetime.strptime(change['start'].split('+')[0].split(' ')[0], '%Y-%m-%d').strftime('%d-%m-%Y')}
                ]
            }
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