#!/bin/bash

LOGDIR=/var/log/httpd/lti

mkdir -p "$LOGDIR"
chown webapp:webapp "$LOGDIR"
chmod 755 "$LOGDIR"

