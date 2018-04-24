#!/bin/bash

if [ "${TRAVIS}" = "true" ] ; then
	BUILDROOT="${TRAVIS_BUILD_DIR}"
	URL='http://localhost/ariadne/';
	TESTDB='ariadne'
	DB="${DB}"
else
	BUILDROOT="${1}"
	URL="${2}"
	TESTDB="${3}"
	DB="${4}"
fi

if [ -z "${BUILDROOT}" -o -z "${URL}" -o -z "${TESTDB}" -o -z "${DB}" ] ; then
	echo "Error: run as ./tests/run.sh `pwd` url dbname dbtype";
	exit 1;
fi

TMPDIR=`mktemp -d `

echo 'language=en&step=step2' | lynx -post_data ${URL}install/index.php >  ${TMPDIR}/installer.check.txt

cat ${TMPDIR}/installer.check.txt

CHECKFAILED=`grep -i failed ${TMPDIR}/installer.check.txt | wc -l`
if [ ${CHECKFAILED} -ge 3 ] ; then
	echo 'Failed pre installer checks'
	grep -i failed ${TMPDIR}/installer.check.txt
	exit 1
fi

# Install and check install result, is should contain "Ariadne is installed!" and no "Error", "Warning", "Notice"
if [ ${DB:=mysql} = 'postgresql' ] ; then
	INSTALLDATA="language=en&step=step6&database=postgresql&database_host=&database_user=postgres&database_pass=&database_name=${TESTDB}&admin_pass=test&admin_pass_repeat=test&ariadne_location&enable_svn=0&enable_workspaces=0&install_demo=1"
else
	INSTALLDATA="language=en&step=step6&database=mysql&database_host=localhost&database_user=root&database_pass=&database_name=${TESTDB}&admin_pass=test&admin_pass_repeat=test&ariadne_location&enable_svn=0&install_demo=1"
	if [ ${WORKSPACE:=no} = 'yes' ] ; then
		set -x
		INSTALLDATA="${INSTALLDATA}&enable_workspaces=1"
	else
		INSTALLDATA="${INSTALLDATA}&enable_workspaces=0"
	fi
fi

echo  "${INSTALLDATA}" | lynx -post_data  ${URL}install/index.php > ${TMPDIR}/installer.output.txt


INSTALLED=`grep "Ariadne is installed" ${TMPDIR}/installer.output.txt| wc -l`
INSTALL_ERRORS=`grep -i error ${TMPDIR}/installer.output.txt|wc -l`
INSTALL_WARNINGS=`grep -i warning ${TMPDIR}/installer.output.txt|wc -l`
INSTALL_NOTICE=`grep -i notice ${TMPDIR}/installer.output.txt|wc -l`

if [ $INSTALLED -lt 1 ]; then
	echo "Installer did not say 'Ariadne is installed!'";
	cat ${TMPDIR}/installer.output.txt
	exit 1;
fi

if [ $INSTALL_ERRORS -gt 0 ]; then
	echo "Installer reported errors:";
	grep -i error ${TMPDIR}/installer.output.txt
	exit 1;
fi

if [ $INSTALL_WARNINGS -gt 0 ]; then
	echo "Installer reported warnings.";
	grep -i warning ${TMPDIR}/installer.output.txt
	exit 1;
fi

if [ $INSTALL_ERRORS -gt 0 ]; then
	echo "Installer reported notices.";
	grep -i notice ${TMPDIR}/installer.output.txt
	exit 1;
fi


# Check the demo site
wget -q -O ${TMPDIR}/demosite.txt ${URL}loader.php/projects/demo/demo/
DEMOSITE_MENU_CURRENT=`grep "menuCurrent" ${TMPDIR}/demosite.txt|wc -l`
DEMOSITE_END_HTML=`grep "</html>" ${TMPDIR}/demosite.txt|wc -l`
DEMOSITE_TAGLINE=`grep "This is your demo site's tagline" ${TMPDIR}/demosite.txt | wc -l`
DEMOSITE_ERRORS=`grep -i error ${TMPDIR}/demosite.txt|wc -l`
DEMOSITE_WARNINGS=`grep -i warning ${TMPDIR}/demosite.txt|wc -l`
DEMOSITE_NOTICE=`grep -i notice ${TMPDIR}/demosite.txt|wc -l`

if [ $DEMOSITE_ERRORS -gt 0 ]; then
	echo "Demosite reported errors:";
	grep -i error ${TMPDIR}/demosite.txt;
	exit 1;
fi

if [ $DEMOSITE_WARNINGS -gt 0 ]; then
	echo "Demosite reported warnings.";
	grep -i warning ${TMPDIR}/demosite.txt
	exit 1;
fi

if [ $DEMOSITE_ERRORS -gt 0 ]; then
	echo "Demosite reported notices.";
	grep -i notice ${TMPDIR}/demosite.txt
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
wget -q -O ${TMPDIR}/ariadne.login.txt ${URL}
LOGIN_TITLE=`grep 'Ariadne - Authorization required.' ${TMPDIR}/ariadne.login.txt | wc -l`
LOGIN_FORM=`grep -i '<form' ${TMPDIR}/ariadne.login.txt | wc -l`
LOGIN_END_HTML=`grep "</html>" ${TMPDIR}/ariadne.login.txt|wc -l`
LOGIN_ERRORS=`grep -i error ${TMPDIR}/ariadne.login.txt|wc -l`
LOGIN_WARNINGS=`grep -i warning ${TMPDIR}/ariadne.login.txt|wc -l`
LOGIN_NOTICE=`grep -i notice ${TMPDIR}/ariadne.login.txt|wc -l`

if [ $LOGIN_ERRORS -gt 0 ]; then
	echo "Login screen reported errors:";
	grep -i error ${TMPDIR}/ariadne.login.txt;
	exit 1;
fi

if [ $LOGIN_WARNINGS -gt 0 ]; then
	echo "Login screen reported warnings.";
	grep -i warning ${TMPDIR}/ariadne.login.txt
	exit 1;
fi

if [ $LOGIN_ERRORS -gt 0 ]; then
	echo "Login screen reported notices.";
	grep -i notice ${TMPDIR}/ariadne.login.txt
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
wget -q -O ${TMPDIR}/ariadne.explore.txt ${URL} --post-data="ARLogin=admin&ARPassword=test"

EXPLORE_ITEM=`grep '<li class="explore_item"' ${TMPDIR}/ariadne.explore.txt | wc -l`
EXPLORE_ERRORS=`grep -i error ${TMPDIR}/ariadne.explore.txt|wc -l`
EXPLORE_WARNINGS=`grep -i warning ${TMPDIR}/ariadne.explore.txt|wc -l`
EXPLORE_NOTICE=`grep -i notice ${TMPDIR}/ariadne.explore.txt|wc -l`


if [ $EXPLORE_ERRORS -gt 0 ]; then
	echo "Explore reported errors:";
	grep -i error ${TMPDIR}/ariadne.explore.txt;
	exit 1;
fi

if [ $EXPLORE_WARNINGS -gt 0 ]; then
	echo "Explore reported warnings.";
	grep -i warning ${TMPDIR}/ariadne.explore.txt
	exit 1;
fi

if [ $EXPLORE_ERRORS -gt 0 ]; then
	echo "Explore reported notices.";
	grep -i notice ${TMPDIR}/ariadne.explore.txt
	exit 1;
fi
if [ $EXPLORE_ITEM -lt 1 ]; then
	echo "Explore does not contain explore items.";
	cat ${TMPDIR}/ariadne.explore.txt;
	exit 1;
fi
# Export /projects/demo/ from the commandline
${BUILDROOT}/bin/export --verbose /projects/demo/ ${TMPDIR}/demo.ax | tee ${TMPDIR}/exportresult.txt
EXPORTRESULT_ITEM=`grep 'processing(/projects/demo/demo/)' ${TMPDIR}/exportresult.txt | wc -l`
EXPORTRESULT_ERRORS=`grep -i error ${TMPDIR}/exportresult.txt|wc -l`
EXPORTRESULT_WARNINGS=`grep -i warning ${TMPDIR}/exportresult.txt|wc -l`
EXPORTRESULT_NOTICE=`grep -i notice ${TMPDIR}/exportresult.txt|wc -l`

tar -ztf ${TMPDIR}/demo.ax > /dev/null

if [ $EXPORTRESULT_ERRORS -gt 0 ]; then
	echo "Commandline export reported errors:";
	grep -i error ${TMPDIR}/exportresult.txt;
	exit 1;
fi

if [ $EXPORTRESULT_WARNINGS -gt 0 ]; then
	echo "Commandline export reported warnings.";
	grep -i warning ${TMPDIR}/exportresult.txt
	exit 1;
fi

if [ $EXPORTRESULT_ERRORS -gt 0 ]; then
	echo "Commandline export reported notices.";
	grep -i notice ${TMPDIR}/exportresult.txt
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

mkdir -p ${TMPDIR}/original ${TMPDIR}/export
tar -C ${TMPDIR}/export -zxf ${TMPDIR}/demo.ax
tar -C ${TMPDIR}/original -zxf ${BUILDROOT}/www/install/packages/demo.ax

EXPORT_EMPTY_COUNT=`find ${TMPDIR}/tmp/export/files ${TMPDIR}/tmp/export/templates  -type f -size 0 -ls  2> /dev/null | tee ${TMPDIR}/exportlist.txt | wc -l`

EXPORT_BASE_FILES_COUNT=`find ${TMPDIR}/original/files  -type f -ls  2> /dev/null | tee ${TMPDIR}/BASE_FILES.txt | wc -l`
EXPORT_EXPORT_FILES_COUNT=`find ${TMPDIR}/export/files  -type f -ls  2> /dev/null | tee ${TMPDIR}/EXPORT_FILES.txt | wc -l`
EXPORT_BASE_TEMPLATES_COUNT=`find ${TMPDIR}/original/templates  -type f -ls  2> /dev/null | tee ${TMPDIR}/BASE_TEMPLATES.txt | wc -l`
EXPORT_EXPORT_TEMPLATES_COUNT=`find ${TMPDIR}/export/templates  -type f -ls  2> /dev/null | tee ${TMPDIR}/EXPORT_TEMPLATES.txt | wc -l`

if [ ${EXPORT_EMPTY_COUNT} -gt 0 ] ; then
	echo "Export generated empty files"
	cat ${TMPDIR}/exportlist.txt
	exit 1;
fi

if [ ${EXPORT_BASE_FILES_COUNT} -ne ${EXPORT_EXPORT_FILES_COUNT} ] ; then
	echo "Base and export files differ"
	cat ${TMPDIR}/BASE_FILES.txt ${TMPDIR}/EXPORT_FILES.txt
	exit 1
fi

if [ ${EXPORT_BASE_TEMPLATES_COUNT} -ne ${EXPORT_EXPORT_TEMPLATES_COUNT} ] ; then
	echo "Base and export templates differ"
	cat ${TMPDIR}/BASE_TEMPLATES.txt ${TMPDIR}/EXPORT_TEMPLATES.txt
	exit 1
fi
echo "Tests done"
