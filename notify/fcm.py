#!/usr/bin/python

import sys
import sqlite3
from pyfcm import FCMNotification

push_service = FCMNotification(api_key="AAAAmadg5-I:APA91bFtQtjnp9899CTRWeWCeI39OobdY-mEmk4FUktw5ZRDiIYZ9NQ07scDNJ1R1tEDLdNJ0_DSUbXVhJGd4uH1bM1P8XlKg_Ia7eQF4n6miHb36jkf3NljXUodWFKi62Se0qg1oFRJ")

if len(sys.argv) == 1: sys.exit()

conn = sqlite3.connect('/srv/HomeBrain/var/fcm.db')
c = conn.cursor()
c.execute("select token from tokens")

rows = c.fetchall() 
tokens = []
for row in rows:
	tokens.append(row[0])

tokens = push_service.clean_registration_ids(tokens)

message_title = "HomeBrain"
message_body = sys.argv[1]

print push_service.notify_multiple_devices(registration_ids=tokens, message_title=message_title, message_body=message_body, sound='Default')
