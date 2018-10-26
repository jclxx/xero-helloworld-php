
It can be tricky reading through all the Xero documentation
to get your first Xero application running.

This project is just the simplest "Hello World" type Xero application,
using Xero-PHP interface.

This is for a "Private Application", which is only used for
connecting to a single Xero Organisation.  It's the simplest kind
of app.

It was tested on freshly installed Ubuntu 18.04.1 LTS Server (64-bit),
and so if anything doesn't work, you might care to start from there,
on a virtual machine (tested with VirtualBox).

Make a directory for your project
Copy these files into it
sh install.sh
(give password for sudo if/when prompted)

Now log in at https://developer.xero.com/myapps and create your app
* Private App
* App Name: helloworldprivate (or whatever you choose)
* Organisation: Demo Company (or whichever you choose)
* Public Key: Paste from clipboard or select contents of secret/publickey.cer)

If you already have a suitable private app registered at Xero,
you can upload your public key to the existing one.


On the resulting page you will be shown your "Consumer Key"
* Copy the Consumer Key, a 30-character random string
* Edited the file secret/consumerkey and paste it in
* Private applications don't need the "Consumer Secret", ignore it.

Now test

$ php xerohwpriv1.php 
  1. Name "Demo Company (UK)" Tax Number "GB 123456789"
happy ending

Troubleshooting

# https://developer.xero.com/documentation/auth-and-limits/oauth-issues


PRIVATE

ERROR: missing composer parts
  did you run "composer require calcinai/xero-php" ?

ERROR: ./secretprivapp/consumerkey is missing or empty
  did you run "sh createprivapp.sh" ?

WARNING: dummy consumer key
XERO ERROR 401: Consumer key was not recognised

WARNING: dummy consumer key
  expect "Consumer key was not recognised" error from xero
  fix by copying consumer key from xero app page to ./secretprivapp/consumerkey
connecting to xero private app with consumer key DU...00
public key certificate fingerprint b914e9214163bd1c74745dea9716619b6ada61b3
XERO ERROR 401: Consumer key was not recognised

WARNING: ./secretprivapp/NAGABOUTKEY exists
  expect "Failed to validate signature" from xero
  fix by copying ./secretprivapp/publickey.cer to xero app page
  delete ./secretprivapp/NAGABOUTKEY to remove this warning
connecting to xero private app with consumer key OQ...HD
public key certificate fingerprint b914e9214163bd1c74745dea9716619b6ada61b3
XERO ERROR 401: Failed to validate signature


check Public Key Certificate Thumbprint at xero against output


PUB


ERROR: ./secretpubapp/consumerkey is missing or empty
  did you run "sh createpubapp.sh" ?


WARNING: dummy consumer key
  expect "Unknown Consumer" error from xero
  fix by copying consumer key from xero app page to ./secretpubapp/consumerkey
...
XERO ERROR 401 (during REQUESTTOKEN): Unknown Consumer (Realm: , Key: DUMMYPUBLICAPPCONSUMERKEY00000)
Problem: consumer_key_unknown
  Unknown Consumer (Realm: , Key: DUMMYPUBLICAPPCONSUMERKEY00000)


WARNING: dummy consumer secret
  expect "Consumer "Failed to validate signature" error from xero
  fix by copying consumer secret from xero app page to ./secretpubapp/consumerkey
XERO ERROR 401 (during REQUESTTOKEN): Failed to validate signature
Problem: signature_invalid
  Failed to validate signature

Means the private key used by this php program doesn't match the
public key that was uploaded to Xero app page.  Either upload it
again or recreate the keys and upload it.

XERO ERROR 401 (during ACCESSTOKEN): The consumer was denied access to this resource.
Problem: permission_denied
  The consumer was denied access to this resource.


XERO ERROR 400 (during REQUESTTOKEN): Bad Request
Problem: parameter_rejected
  Callback url is not in the registered callback domain



(end)
