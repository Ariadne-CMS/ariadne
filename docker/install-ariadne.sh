#!/bin/bash

chown www-data:www-data /opt/ariadne/lib/configs/ariadne.phtml
chown -R www-data:www-data /opt/ariadne/lib/configs/svn/
chown -R www-data:www-data /opt/ariadne/files/

# Reinstall composer packages
cd /opt/ariadne/
rm -rf /opt/ariadne/vendor/*
rm composer.lock
composer install

# Setup default ariadne.inc
echo "<?php\n\$ariadne='/opt/ariadne/lib';\n" > /var/www/html/ariadne/ariadne.inc

# Run Ariadne installer
curl --data "language=en&step=step6&database=mysql&database_host=mysql&database_user=ariadne&database_pass=${MYSQL_PASSWORD}&database_name=ariadne&admin_pass=${ARIADNE_PASSWORD}&admin_pass_repeat=${ARIADNE_PASSWORD}&ariadne_location&enable_svn=1&enable_workspaces=0&install_demo=1" http://localhost/ariadne/install/index.php

# Add vhosts
cp /opt/ariadne/docker/000-default.conf /etc/apache2/sites-enabled/000-default.conf
apache2ctl graceful
