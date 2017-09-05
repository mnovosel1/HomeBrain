#!/usr/bin/python

# Send to single device.
from pyfcm import FCMNotification

push_service = FCMNotification(api_key="AAAAmadg5-I:APA91bFtQtjnp9899CTRWeWCeI39OobdY-mEmk4FUktw5ZRDiIYZ9NQ07scDNJ1R1tEDLdNJ0_DSUbXVhJGd4uH1bM1P8XlKg_Ia7eQF4n6miHb36jkf3NljXUodWFKi62Se0qg1oFRJ", sound='Default')


registration_id = "c_h1xXEaaz4:APA91bGSfmV7GuilfV2rxZklKs7KakjA2Nd7MtC1Og1WAPtF1yJNqDT_KaMDTqQ8QNrcgMx3jycBKzs3H0x4K_W584OORvXj9IJQaCr_XSgO7gGW7L7DakPIJMFMcSDz4jaZw-T2qU4L"
message_title = "HomeBrain"
message_body = "Hi john, your customized news for today is ready"
extra_kwargs = {
    'sound': 'default'
}
result = push_service.notify_single_device(registration_id=registration_id, message_title=message_title, message_body=message_body, extra_kwargs=extra_kwargs)

# Send to multiple devices by passing a list of ids.
#registration_ids = ["<device registration_id 1>", "<device registration_id 2>", ...]
#registration_ids = ["<device registration_id 1>", "<device registration_id 2>", ...]
#message_title = "Uber update"
#message_body = "Hope you're having fun this weekend, don't forget to check today's news"
#result = push_service.notify_multiple_devices(registration_ids=registration_ids, message_title=message_title, message_body=message_body)

print result
