#!/usr/bin/env bash

tmp=$(mktemp -u)

../vendor/bin/phpunit --coverage-html "$tmp" --configuration ../phpunit.xml

# MacOS
open "$tmp/index.html"

# Linux
xdg-open "$tmp/index.html"

# Windows
start "$tmp/index.html"