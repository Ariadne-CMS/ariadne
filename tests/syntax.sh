#!/bin/bash

if [ "${TRAVIS}" = "true" ] ; then
	BUILDROOT="${TRAVIS_BUILD_DIR}"
else
	BUILDROOT="${1}"
fi

TMPDIR=`mktemp -d `

cd ${BUILDROOT}

SYNTAX_ERROR_COUNT=`(find www -type f -name \*php ; find www/install/conf lib ftp soap webdav -type f)  | grep -v 'lib/ar/beta/' | xargs -n1 --replace={} bash -c 'php -d "error_reporting=E_ALL & ~E_STRICT & ~E_NOTICE " -d short_open_tag=off -l {} || true' | grep -v 'No syntax errors detected in'  | tee ${TMPDIR}/syntax.errors.txt | wc -l`

if [ ${SYNTAX_ERROR_COUNT} -ge 1 ]; then
	echo "syntax errors found in build";
	cat ${TMPDIR}/syntax.errors.txt
	exit 1;
fi

