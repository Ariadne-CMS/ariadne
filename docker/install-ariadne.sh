#!/bin/bash

chown www-data:www-data /opt/ariadne/lib/configs/ariadne.phtml
chown -R www-data:www-data /opt/ariadne/lib/configs/svn/
chown -R www-data:www-data /opt/ariadne/files/

# Reinstall composer packages
cd /opt/ariadne/
rm -rf /opt/ariadne/vendor/*
rm composer.lock
composer install

# Run Ariadne installer
curl --data "language=en&step=step6&database=mysql&database_host=mysql&database_user=ariadne&database_pass=${MYSQL_PASSWORD}&database_name=ariadne&admin_pass=${ARIADNE_PASSWORD}&admin_pass_repeat=${ARIADNE_PASSWORD}&ariadne_location&enable_svn=1&enable_workspaces=0&install_demo=1" https://localhost/ariadne/install/index.php
