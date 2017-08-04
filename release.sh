#!/bin/sh

if [ -z "$1" ]; then
    echo "Usage $0 <version>";
    exit 1;
fi;

filename="komtet-kassa-$1.ocmod.zip";
rm -f $filename;
sed -i -r "s#<version>(.*)</version>#<version>$1</version>#" install.xml;
zip -r $filename install.xml upload;
