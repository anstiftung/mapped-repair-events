#!/usr/bin/env bash

# allows script to be called from /webroot and root directory
SCRIPT=$(readlink -f "$0")
APP=$(dirname "$SCRIPT")/..

# files not compatible with license GPLv.3
rm -f $APP/webroot/node_modules/jquery-knob/excanvas.js
rm -f $APP/webroot/node_modules/featherlight/assets/stylesheets/bootstrap.min.css

cp -R $APP/webroot/node_modules/@fortawesome/fontawesome-free/webfonts $APP/webroot
cp -R $APP/webroot/node_modules/jquery-ui/dist/themes/smoothness/images $APP/webroot/cache
cp -R $APP/webroot/node_modules/leaflet/dist/images $APP/webroot/cache

cp $APP/config/elfinder/php/connector.minimal.php $APP/webroot/js/elfinder/php/connector.minimal.php
