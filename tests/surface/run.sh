#!/bin/bash
# set -e  # exit on error
for f in *.php; do
  echo -n "Running $f: "
  php "$f"
done
