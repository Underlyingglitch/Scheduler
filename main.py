import json
import shutil
from datetime import datetime, timedelta

import functions


users = []

if __name__ == "__main__":
    # Load in users
    with open('./config/users.json', 'r') as f:
        # For each registered user
        for user in json.loads(f.read()):
            # Get new user data
            users.append(functions.get_data(user))
    # Update users file with new "last_updated" value
    with open('./config/users.json', 'w') as f:
        f.write(json.dumps(users))
    
    # Duplicate combined.json to calculate changes
    shutil.copy('./data/combined.json', './data/combined.old.json')

    # Merge files to combined.json
    functions.mergeFiles(users)

    # Calculate changes
    changes = functions.calculateChanges()

    # Prepare and send out updates to Discord
    functions.prepareUpdate(changes)