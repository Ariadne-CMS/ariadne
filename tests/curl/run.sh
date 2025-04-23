#!/bin/bash

export URL="http:/localhost/";
export TMPDIR="/tmp";

bash test-demosite.sh
bash test-login.sh 
bash test-explore.sh

echo "Tests done"
