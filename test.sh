#!/bin/bash

profile="production"
isProduction=true

if [[ "production" != $profile ]]; then
	isProduction=false
fi

phpArgs="-F"
case "$profile" in
"production")
	echo is production!
	;;
*)
	phpArgs=""
	echo is not production!
	;;
esac


#if [ true = $isProduction ]; then
#fi

#if [ false = $isProduction ]; then
#fi

cat $phpArgs
