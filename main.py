from datetime import datetime
import vobject
from urllib.request import urlopen, Request
import json
import os
import requests

users = []


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


def add_event(id, summary, location, teacher, status, start, end, stamp, description, user):
    path = './events/'+user+'.json'
    if os.path.exists(path):
        f = open(path, 'r')
        data = json.loads(f.read())
        f.close()
    else:
        data = {}
    data[id] = {
        "summary": summary,
        "location": location,
        "teacher": teacher,
        "status": status,
        "start": str(start),
        "end": str(end),
        "stamp": str(stamp),
        "description": description,
        "dertig": convert_period(str(start).split()[1], "30"),
        "vijftig": convert_period(str(start).split()[1], "50")
    }
    with open(path, 'w') as f:
        f.write(json.dumps(data))


def get_data(_user):
    url = _user['url']
    headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.3'}
    req = Request(url=url, headers=headers)
    data = urlopen(req).read().decode('iso-8859-1')

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
                    status = "CANCELLED"
                elif desc[0] == '[!]':
                    summary = "[!] "
                    location = "Lokaal " + desc[3]
                    teacher = desc[4]
                    status = "TENTATIVE"
                else:
                    summary = ""
                    location = "Lokaal " + desc[2]
                    teacher = desc[3]
                    status = "CONFIRMED"
                summary += ' '.join(old_name)
                del desc[0:4]
                summary += ' ' + ' '.join(desc)
                id = component.uid.valueRepr()
                add_event(id, summary, location, teacher, status, component.dtstart.valueRepr(), component.dtend.valueRepr(), component.dtstamp.valueRepr(), component.description.valueRepr(), _user['code'])
    user = _user
    user['last_updated'] = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    users.append(user)

if __name__ == "__main__":
    with open('./config/users.json', 'r') as f:
        for user in json.loads(f.read()):
            get_data(user)
    with open('./config/users.json', 'w') as f:
        f.write(json.dumps(users))