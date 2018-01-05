#!/usr/bin/env bash

dir=$(dirname "$0")
bin="$dir/../vendor/bin/apigen"

$bin generate "$dir/../classes" --destination "$dir/../docs"