#!/bin/bash -x

set -e

( cat ${TRAVIS_BUILD_DIR}/tests/check.txt ; echo '---' ) | lynx -post_data http://localhost/ariadne/install/index.php

# Install and check install result, is should contain "Ariadne is installed!" and no "Error", "Warning", "Notice"
wget -q -O installer.output.txt http://localhost/ariadne/install/index.php --post-file=${TRAVIS_BUILD_DIR}/tests/install.${DB}.txt
INSTALLED=`grep "Ariadne is installed" installer.output.txt| wc -l`
INSTALL_ERRORS=`grep -i error installer.output.txt|wc -l`
INSTALL_WARNINGS=`grep -i warning installer.output.txt|wc -l`
INSTALL_NOTICE=`grep -i notice installer.output.txt|wc -l`

if [ $INSTALLED -lt 1 ]; then
	echo "Installer did not say 'Ariadne is installed!'";
	cat installer.output.txt
	exit 1;
fi

if [ $INSTALL_ERRORS -gt 0 ]; then
	echo "Installer reported errors:";
	grep -i error installer.output.txt
	exit 1;
fi

if [ $INSTALL_WARNINGS -gt 0 ]; then
	echo "Installer reported warnings.";
	grep -i warning installer.output.txt
	exit 1;
fi

if [ $INSTALL_ERRORS -gt 0 ]; then
	echo "Installer reported notices.";
	grep -i notice installer.output.txt
	exit 1;
fi


# Check the demo site
wget -q -O demosite.txt http://localhost/ariadne/loader.php/projects/demo/demo/
DEMOSITE_MENU_CURRENT=`grep "menuCurrent" demosite.txt|wc -l`
DEMOSITE_END_HTML=`grep "</html>" demosite.txt|wc -l`
DEMOSITE_TAGLINE=`grep "This is your demo site's tagline" demosite.txt | wc -l`
DEMOSITE_ERRORS=`grep -i error demosite.txt|wc -l`
DEMOSITE_WARNINGS=`grep -i warning demosite.txt|wc -l`
DEMOSITE_NOTICE=`grep -i notice demosite.txt|wc -l`

if [ $DEMOSITE_ERRORS -gt 0 ]; then
	echo "Demosite reported errors:";
	grep -i error demosite.txt;
	exit 1;
fi

if [ $DEMOSITE_WARNINGS -gt 0 ]; then
	echo "Demosite reported warnings.";
	grep -i warning demosite.txt
	exit 1;
fi

if [ $DEMOSITE_ERRORS -gt 0 ]; then
	echo "Demosite reported notices.";
	grep -i notice demosite.txt
	exit 1;
fi
if [ $DEMOSITE_TAGLINE -lt 1 ]; then
	echo "Demosite does not contain tagline";
	exit 1;
fi
if [ $DEMOSITE_END_HTML -lt 1 ]; then
	echo "Demosite does not contain closing html tag";
	exit 1;
fi
if [ $DEMOSITE_MENU_CURRENT -lt 1 ]; then
	echo "Demosite does not contain 'menuCurrent'";
	exit 1;
fi

# Get the login screen
wget -q -O ariadne.login.txt http://localhost/ariadne/
LOGIN_TITLE=`grep 'Ariadne - Authorization required.' ariadne.login.txt | wc -l`
LOGIN_FORM=`grep -i '<form' ariadne.login.txt | wc -l`
LOGIN_END_HTML=`grep "</html>" ariadne.login.txt|wc -l`
LOGIN_ERRORS=`grep -i error ariadne.login.txt|wc -l`
LOGIN_WARNINGS=`grep -i warning ariadne.login.txt|wc -l`
LOGIN_NOTICE=`grep -i notice ariadne.login.txt|wc -l`


if [ $LOGIN_ERRORS -gt 0 ]; then
	echo "Login screen reported errors:";
	grep -i error ariadne.login.txt;
	exit 1;
fi

if [ $LOGIN_WARNINGS -gt 0 ]; then
	echo "Login screen reported warnings.";
	grep -i warning ariadne.login.txt
	exit 1;
fi

if [ $LOGIN_ERRORS -gt 0 ]; then
	echo "Login screen reported notices.";
	grep -i notice ariadne.login.txt
	exit 1;
fi
if [ $LOGIN_TITLE -lt 1 ]; then
	echo "Login screen does not contain title 'Ariadne - Authorization required.'";
	exit 1;
fi
if [ $LOGIN_FORM -lt 1 ]; then
	echo "Login screen does not contain form'";
	exit 1;
fi
if [ $LOGIN_END_HTML -lt 1 ]; then
	echo "Login screen does not contain closing html tag'";
	exit 1;
fi


# Go to the main explore.php
wget -q -O ariadne.explore.txt http://localhost/ariadne/ --post-file=${TRAVIS_BUILD_DIR}/tests/login.txt

EXPLORE_ITEM=`grep '<li class="explore_item"' ariadne.explore.txt | wc -l`
EXPLORE_ERRORS=`grep -i error ariadne.explore.txt|wc -l`
EXPLORE_WARNINGS=`grep -i warning ariadne.explore.txt|wc -l`
EXPLORE_NOTICE=`grep -i notice ariadne.explore.txt|wc -l`


if [ $EXPLORE_ERRORS -gt 0 ]; then
	echo "Explore reported errors:";
	grep -i error ariadne.explore.txt;
	exit 1;
fi

if [ $EXPLORE_WARNINGS -gt 0 ]; then
	echo "Explore reported warnings.";
	grep -i warning ariadne.explore.txt
	exit 1;
fi

if [ $EXPLORE_ERRORS -gt 0 ]; then
	echo "Explore reported notices.";
	grep -i notice ariadne.explore.txt
	exit 1;
fi
if [ $EXPLORE_ITEM -lt 1 ]; then
	echo "Explore does not contain explore items.";
	cat ariadne.explore.txt;
	exit 1;
fi
# Export /projects/demo/ from the commandline
sudo ${TRAVIS_BUILD_DIR}/bin/export --verbose /projects/demo/ /tmp/demo.ax | tee exportresult.txt
EXPORTRESULT_ITEM=`grep 'processing(/projects/demo/demo/)' exportresult.txt | wc -l`
EXPORTRESULT_ERRORS=`grep -i error exportresult.txt|wc -l`
EXPORTRESULT_WARNINGS=`grep -i warning exportresult.txt|wc -l`
EXPORTRESULT_NOTICE=`grep -i notice exportresult.txt|wc -l`

tar -ztf /tmp/demo.ax > /dev/null

if [ $EXPORTRESULT_ERRORS -gt 0 ]; then
	echo "Commandline export reported errors:";
	grep -i error exportresult.txt;
	exit 1;
fi

if [ $EXPORTRESULT_WARNINGS -gt 0 ]; then
	echo "Commandline export reported warnings.";
	grep -i warning exportresult.txt
	exit 1;
fi

if [ $EXPORTRESULT_ERRORS -gt 0 ]; then
	echo "Commandline export reported notices.";
	grep -i notice exportresult.txt
	exit 1;
fi
if [ $EXPORTRESULT_ITEM -lt 1 ]; then
	echo "Commandline export did not report processing /projects/demo/demo/";
	exit 1;
fi

if [ $EXPORTRESULT_ITEM -lt 1 ]; then
	echo "Commandline export resulted in an empty file";
	exit 1;
fi

mkdir -p /tmp/original /tmp/export
tar -C /tmp/export -zxf /tmp/demo.ax
tar -C /tmp/original -zxf ${TRAVIS_BUILD_DIR}/www/install/packages/demo.ax

EXPORT_EMPTY_COUNT=`find /tmp/export/files /tmp/export/templates  -type f -size 0 -ls  2> /dev/null | tee /tmp/exportlist.txt | wc -l`

EXPORT_BASE_FILES_COUNT=`find /tmp/original/files  -type f -ls  2> /dev/null | tee /tmp/BASE_FILES.txt | wc -l`
EXPORT_EXPORT_FILES_COUNT=`find /tmp/export/files  -type f -ls  2> /dev/null | tee /tmp/EXPORT_FILES.txt | wc -l`
EXPORT_BASE_TEMPLATES_COUNT=`find /tmp/original/templates  -type f -ls  2> /dev/null | tee /tmp/BASE_TEMPLATES.txt | wc -l`
EXPORT_EXPORT_TEMPLATES_COUNT=`find /tmp/export/templates  -type f -ls  2> /dev/null | tee /tmp/EXPORT_TEMPLATES.txt | wc -l`

if [ ${EXPORT_EMPTY_COUNT} -gt 0 ] ; then
	echo "Export generated empty files"
	cat /tmp/exportlist.txt
	exit 1;
fi

if [ ${EXPORT_BASE_FILES_COUNT} -ne ${EXPORT_EXPORT_FILES_COUNT} ] ; then
	echo "Base and export files differ"
	cat /tmp/BASE_FILES.txt /tmp/EXPORT_FILES.txt
	exit 1
fi

if [ ${EXPORT_BASE_TEMPLATES_COUNT} -ne ${EXPORT_EXPORT_TEMPLATES_COUNT} ] ; then
	echo "Base and export templates differ"
	cat /tmp/BASE_TEMPLATES.txt /tmp/EXPORT_TEMPLATES.txt
	exit 1
fi

cd ${TRAVIS_BUILD_DIR}
SYNTAX_ERROR_COUNT=`(find www -type f -name \*php ; find www/install/conf lib ftp soap webdav -type f)  | xargs -n1 --replace={} bash -c 'php5 -d short_open_tag=off -l {} || true' | grep -v 'No syntax errors detected in'  | tee /tmp/syntax.errors.txt | wc -l`

if [ ${SYNTAX_ERROR_COUNT} -ge 1 ]; then
	echo "syntax errors found in build";
	cat /tmp/syntax.errors.txt
	exit 1;
fi
