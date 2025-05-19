#!/bin/bash

rm /var/www/html/install/.htaccess
chown www-data:www-data /opt/ariadne/lib/configs/ariadne.phtml
chown -R www-data:www-data /opt/ariadne/lib/configs/svn/
chown -R www-data:www-data /opt/ariadne/files/

curl --data "language=en&step=step6&database=mysql&database_host=mysql&database_user=ariadne&database_pass=Hohtai6shaht&database_name=ariadne&admin_pass=test&admin_pass_repeat=test&ariadne_location&enable_svn=1&enable_workspaces=0&install_demo=1" http://localhost/ariadne/install/index.php

# Add vhosts
cp /opt/ariadne/docker/000-default.conf /etc/apache2/sites-enabled/000-default.conf
apache2ctl graceful
