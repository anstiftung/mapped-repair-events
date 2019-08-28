#!/usr/bin/env bash
bin/cake i18n extract --ignore-model-validation --output src\\Locale --paths src,config --overwrite --extract-core yes --merge no --no-location --exclude plugins
