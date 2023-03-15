#!/usr/bin/env bash

source $(dirname $0)/locales.sh

#get and merge translations for main app
#to extract core strings change --extract-core to "yes"

bash bin/cake i18n extract --output resources/locales --paths config,src,templates --overwrite --extract-core no --merge no --no-location --exclude plugins
for locale in "${LOCALES[@]}"
do
    msgmerge resources/locales/$locale/cake.po resources/locales/cake.pot --output-file=resources/locales/$locale/cake.po --width=1000
    msgmerge resources/locales/$locale/default.po resources/locales/default.pot --output-file=resources/locales/$locale/default.po --width=1000
done
