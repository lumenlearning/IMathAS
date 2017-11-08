#!/usr/bin/env python

import json

from flask import Flask, request

app = Flask(__name__)


# /student_pay/v1/student_pay?userid=asdf&auth_code=1234
@app.route("/student_pay", methods=['GET'])
def get_payment_status():
    print("\nRequest data: " + request.data)
    print("Authorization: " + request.headers.get('Authorization'))
    result = {
        "status": "trial_not_started",
        "section_requires_student_payment": True
    }
    return json.dumps(result)


@app.route("/student_pay", methods=['POST'])
def activate_access_code():
    print("\nRequest data: " + request.data)
    print("Authorization: " + request.headers.get('Authorization'))
    result = {
        "status": "ok",
        "message": "Your code has been activated"
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


@app.route("/student_pay_settings", methods=['POST'])
def update_payment_settings():
    print("\nRequest data: " + request.data)
    print("Authorization: " + request.headers.get('Authorization'))
    result = {
        "section_id": 42,
    }
    return json.dumps(result)


app.run()
