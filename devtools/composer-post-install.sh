#!/usr/bin/env bash

rm -Rf vendor/studio-42/elfinder/.git
mkdir -p webroot/js/elfinder
cp -Rp vendor/studio-42/elfinder/* webroot/js/elfinder
rm -Rf vendor/studio-42

cp -Rp config/tcpdf-fonts/* vendor/tecnickcom/tcpdf/fonts