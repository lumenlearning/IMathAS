#!/usr/bin/env python

import json

from flask import Flask, request

app = Flask(__name__)


# /student_pay/v1/student_pay?userid=asdf&auth_code=1234
@app.route("/studentpay_api/v1/student_pay", methods=['GET'])
def get_payment_status():
    print("\nRequest data: " + request.data)
    result = {
        "student_status": "paid",
        "course_requires_student_payment": True
    }
    return json.dumps(result)


@app.route("/studentpay_api/v1/student_pay/activation_code", methods=['POST'])
def activate_access_code():
    print("\nRequest data: " + request.data)
    result = {
        "status": "ok",
        "message": "Your code has been activated"
    }
    return json.dumps(result)


@app.route("/studentpay_api/v1/student_pay/trial", methods=['POST'])
def begin_trial():
    print("\nRequest data: " + request.data)
    result = {
        "status": "in_trial",
    }
    return json.dumps(result)


@app.route("/studentpay_api/v1/student_pay/payment_settings", methods=['POST'])
def update_payment_settings():
    print("\nRequest data: " + request.data)
    result = {
        "course_id": 42,
    }
    return json.dumps(result)


app.run()
