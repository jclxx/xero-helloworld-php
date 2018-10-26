#!/bin/sh

# createpubapp.sh
# set up for xero-helloworld public app
# jcl/jclxx/2018-10-24

set -e

D=secretpubapp

echo "== making config files for public app =="

mkdir -p $D

# consumer key
#
if [ -f $D/consumerkey ]; then
	echo "  leaving existing consumerkey for public app"
else
	echo 'DUMMYPUBLICAPPCONSUMERKEY00000' > $D/consumerkey
	echo "  made placeholder consumerkey for public app"
fi

# check if dummy
#
if fgrep -q DUMMY $D/consumerkey ; then
	echo "  !!! please edit $D/consumerkey with value from xero app page"
fi

# make consumer secret
#
if [ -f $D/consumersecret ]; then
	echo "  leaving existing consumersecret for public app"
else
	echo 'DUMMYPUBLICAPPCONSUMERSECRET00' > $D/consumersecret
	echo "  made placeholder consumersecret for public app"
fi

# check if dummy
#
if fgrep -q DUMMY $D/consumersecret ; then
	echo "  !!! please edit $D/consumersecret with value from xero app page"
fi

# end
