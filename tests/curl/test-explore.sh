#!/bin/bash
# Go to the main explore.php
curl --output ${TMPDIR}/ariadne.explore.txt --no-progress-meter --location --max-redirs 2 --data "ARLogin=admin&ARPassword=test" --cookie ${TMPDIR}/aridane.cookies.txt ${URL}

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
