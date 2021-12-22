#!/bin/bash

LOGDIR=/var/log/httpd/question_api

mkdir -p "$LOGDIR"
chown webapp:webapp "$LOGDIR"
chmod 755 "$LOGDIR"

