#!/bin/bash
# Get the login screen
curl --output ${TMPDIR}/ariadne.login.txt --no-progress-meter ${URL}

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

