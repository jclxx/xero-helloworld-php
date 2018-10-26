#!/bin/sh

# createprivateapp.sh
# make config files for xero-helloworld
# jcl/jclxxx/2018-10-24

set -e

D=secretprivapp

echo "== making config files for private app =="

mkdir -p $D

if [ -f $D/consumerkey ]; then
	echo "  leaving existing consumerkey for private app"
else
	echo 'DUMMYPRIVATEAPPCONSUMERKEY0000' > $D/consumerkey
	echo "  made placholder consumerkey for private app"
fi

# check if consumer key is dummy
#
if fgrep -q DUMMY $D/consumerkey ; then
	echo "  !!! please edit $D/consumerkey with value from xero app page"
fi

if [ -f $D/privatekey.pem ]; then
	echo "  leaving existing privatekey.pem for private app"
else
	openssl genrsa -out $D/privatekey.pem 1024
	echo "  made privatekey for private app"
	# remove old public cert if exists
	rm -f $D/publickey.cer
fi

# create public key certificate from private key
# you probably want a more sensible set of values for the cert
#
if [ -f $D/publickey.cer ]; then
	echo "  leaving existing publickey.cer for private app"
else
	openssl req -new -x509 -key $D/privatekey.pem \
	 -out $D/publickey.cer -days 1825 \
	 -subj "/C=GB/ST=Here/L=Here/O=Hello/OU=Xero Testers/CN=example.com"
	echo "  made publickey cert for private app"

	# following file just generates messages
	echo "delete me when you have uploaded $D/publickey.cer to xero" \
		> $D/NAGABOUTKEY
	echo "  made nag file to remind you about updating xero"
fi

# check about uploading
#
if [ -f $D/NAGABOUTKEY ]; then
	echo "  !!! please update xero app page with $D/publickey.cer"
fi

# end
