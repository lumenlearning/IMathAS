#!/bin/bash

if [ -d 'venv' ]; then
	. venv/bin/activate
fi

export FLASK_APP=student_pay_api.py
export FLASK_DEBUG=1
flask run

