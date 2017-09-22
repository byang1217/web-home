#!/bin/bash

MYDIR=`dirname $0`
cd $MYDIR
export TOP_DIR=`pwd`

/usr/bin/osascript -e "do shell script \"$TOP_DIR/tools/mac/start.sh\" with administrator privileges"

sleep 5

