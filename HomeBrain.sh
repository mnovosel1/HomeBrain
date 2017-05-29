#!/bin/bash

case "$1" in

	"Testing")  echo "Sending TESTING signal"
	   ;;

	*) eval $1
	   ;;
esac
