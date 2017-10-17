# Purpose

This is a simple mock of the student payment API. It returns static content.

# Usage

## First-time virtual environment setup

	$ virtualenv venv
	$ . venv/bin/activate
	$ pip install -r requirements.txt
	$ deactivate

## Subsequent usage

	$ . venv/bin/activate
	$ ./student_pay_api.py

# Notes

Because this mock returns static content,
[student_pay_api.py](student_pay_api.py)
will need to be edited to change mocked responses.

