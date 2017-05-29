#!/bin/bash

case "$1" in

"Testing")  echo "Sending TESTING signal"
   ;;
*) echo "Signal number $1 is not processed"
   ;;
esac