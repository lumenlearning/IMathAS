#!/usr/bin/env python

import json

from flask import Flask, request

app = Flask(__name__)


# Valid status strings:  (and sample live responses)
#   trial_not_started
#       {"status":"trial_not_started","section_requires_student_payment":true,"trial_expired_in":0}
#   trial_started
#       {"status":"trial_started"}
#   in_trial
#       {"status":"in_trial","section_requires_student_payment":true,"trial_expired_in":1209463}
#   can_extend
#       ??
#   expired
#       ??
#   activation_code_claimed
#       {"message":"You have successfully submitted your code.","status":"activation_code_claimed"}
#   has_access
#       {"status":"has_access","section_requires_student_payment":true,"trial_expired_in":1209422}

# /student_pay/v1/student_pay?userid=asdf&auth_code=1234
@app.route("/student_pay", methods=['GET'])
def get_payment_status():
    print("\nRequest data: " + request.data)
    print("Authorization: " + request.headers.get('Authorization'))
    result = {
        "status": "trial_not_started",
        "section_requires_student_payment": True,
        "trial_expired_in": 1024567
    }
    return json.dumps(result)


@app.route("/student_pay", methods=['POST'])
def activate_access_code():
    print("\nRequest data: " + request.data)
    print("Authorization: " + request.headers.get('Authorization'))
    result = {
        "status": "activation_code_claimed",
        "message": "You have successfully submitted your code."
    }
    return json.dumps(result)


@app.route("/student_pay/trials", methods=['POST'])
def begin_trial():
    print("\nRequest data: " + request.data)
    print("Authorization: " + request.headers.get('Authorization'))
    result = {
        "status": "trial_started",
    }
    return json.dumps(result)


@app.route("/enrollment_events", methods=['POST'])
def enrollment_events():
    print("\nRequest data: " + request.data)
    print("Authorization: " + request.headers.get('Authorization'))
    result = {
        "status": "ok",
    }
    return json.dumps(result)


@app.route("/institutions/<int:id>", methods=['GET'])
def get_institution_data(id):
    print("\nRequest data: " + request.data)
    print("Authorization: " + request.headers.get('Authorization'))
    result = {
        "id": "957c5216-7857-4b5a-9cb8-17c0c32bb608",
        "name": "Hogwarts School of Witchcraft and Wizardry",
        "external_ids": {
            "4": "43627281-b00b-4142-8e4c-1e435fe4f1c1",
            "2204": "43627281-b00b-4142-8e4c-1e435fe4f1c1"
        },
        "bookstore_information": "This is a great book store. It has many books.",
        "bookstore_url": "https://localhost/"
    }
    return json.dumps(result)


if __name__ == '__main__':
    app.run()
