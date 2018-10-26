# Xero Hello World #

This is about Xero the accounting software and getting your own program running against the Xero API, using the Xero-PHP library.

It can be tricky reading through all the Xero documentation
to get your first Xero application running.  In particular there are errors at Xero-PHP and the information is not in a single place.

This project has a single goal: **Error free first Xero Applications**

It contains:

 - Installation scripts for all dependencies
 - Creation scripts for the secrets
 - PHP Scripts for the apps
 - These are command line scripts with much error-checking and no web interfacing whatsoever

It has both what Xero calls a Private App and a Public App.

## Getting started ##

You need to have

- A developer account at Xero
- A Demo Company

Go to http://developer.xero.com to get these.

You also need a Linux computer with outgoing internet access

- Tested on Ubuntu 18.04.1 LTS server, on VirtualBox
- Tested on Xubuntu 18.04 desktop
- It is likely to work on many other systems but hasn't been tested

These notes were tested on freshly installed systems.  If you find errors running, you might try a new virtual computer and install from there.

## What is a Xero App? ##

A Xero app software which interacts with the Xero API, typically for making custom reports from your accounts, or to interface a stock control system to your accounts.

In these notes

- **program** means the code which does something,
- **app** means the registration of authentication secrets at your Xero developer page

Apps are registered at your page at https://developer.xero.com/myapps.

Xero apps use Oauth 1.0a to authenticate themselves.

### Private App ###

There are several kinds of Xero App: "private", "public", and "partner".

A "private application" works with Xero like this:

- You create a X.509 private key and make a certificate
- You register the app at https://developer.xero.com/myapps
- You choose which organisation's information is connected to this app
- Only a single organisations accounts are connected to this app
- Xero generates a "consumer key", a 30-character unguessable ID for this application
- You upload the certificate to the app page, which Xero calls the "Public Key"
- You download the consumer key and tell it to your program
- When the program runs, it exchanges encrypted information with Xero over its API

The consumer key is just an unguessable identifier for the app, not particularly secret.  You can change the consumer key at any time on the app's page.  (Xero also generates a "consumer secret", but this isn't used at all for a private app.)

The public key is the way that Xero validates requests from the actual program, which signs them with the private key.  You can upload a new public key whenever you like.  (It's not actually a public key, it's a signed certificate.)

There is nothing about what the app actually does here.  It's just about authentication.  You can register an app without any program code at all (obviously it will do nothing), but you do have to have a signed certificate.

### Public Apps ###

A "public application" works like this

- You register the app at https://developer.xero.com/myapps
- Xero generates a "consumer key", a 30-character unguessable ID for this application
- Xero generates a "consumer secret", a 30-character secret for signing requests
- You don't need any X.509 keys or certificates
- When the program runs, it presents a one-time URL to the user at https://api.xero.com/oauth/Authorize?...
- The user clicks through and has to log in
- The user grants (or doesn't) access to a given organisations's accounts
- Xero gives you a magic validation number (currently 7-digit)
- The user inputs the validation code into your program
- Your program converts the validation code into an access token
- Now your program can make API calls and find out details from the accounts

There are two methods for Xero to give you the validation code, chosen by your program.

When the user clicks through the authorisation
- Xero presents a page saying "Enter this code in *appname* to finish the process", or
- Xero redirects to a a "callback URL" which you choose, and it makes a GET request with oauth_verifier=*magicnumber*

## Partner Apps ##

A "partner application" is a variety of public application which has been specially upgraded by Xero.  You create a public app and then get them to upgrade it.  We don't consider partner apps any further.

## Using the Private App Hello World ##

This project is just the simplest "Hello World" type Xero application,
using Xero-PHP interface.

This is for a "Private Application", which is only used for
connecting to a single Xero Organisation.  It's the simplest kind
of app.

    git clone https://github.com/jclxx/xero-helloworld-php.git
    cd xero-helloworld-php/
    sudo sh installpackages.sh
    sh createprivapp.sh
    cat secretprivapp/publickey.cer

Now go to https://developer.xero.com/myapps and click "New App" (top right).

- Choose "Private App"
- Give the App name "Hello World Private" (or whatever you like)
- Choose Organisation "Demo Company (UK)" (or whatever you like)
- Copy/paste your certificate into the "Public Key)" (or select file to upload)
- Accept the conditions and press Create APp

You will see a confirmation page
- Find the "OAuth 1.0a Credentials" and see the "Consumer key"
- Click "Copy"

Edit this key into your file:

    nano secretprivapp/consumerkey

That completes the setup of the Xero Private App at Xero.

Now get the program running:

    composer require calcinai/xero-php
    php xero-privapp-helloworld.php

Your should see:

It was tested on freshly installed Ubuntu 18.04.1 LTS Server (64-bit),
and so if anything doesn't work, you might care to start from there,
on a virtual machine (tested with VirtualBox).

-Make a directory for your project
-Copy these files into it


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
