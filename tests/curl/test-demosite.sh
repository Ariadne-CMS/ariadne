#!/bin/bash

# Check the demo site
curl --output ${TMPDIR}/demosite.txt --no-progress-meter ${URL}loader.php/projects/demo/demo/

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
	cat ${TMPDIR}/demosite.txt
	exit 1;
fi
if [ $DEMOSITE_END_HTML -lt 1 ]; then
	echo "Demosite does not contain closing html tag";
	cat ${TMPDIR}/demosite.txt
	exit 1;
fi
if [ $DEMOSITE_MENU_CURRENT -lt 1 ]; then
	echo "Demosite does not contain 'menuCurrent'";
	cat ${TMPDIR}/demosite.txt
	exit 1;
fi
