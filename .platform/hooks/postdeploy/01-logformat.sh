#!/bin/bash
sudo sed -i '/^CustomLog/d' /etc/httpd/conf/httpd.conf
echo 'CustomLog logs/access_log "%{X-Forwarded-For}i %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\""' | sudo tee -a /etc/httpd/conf/httpd.conf
sudo apachectl restart
