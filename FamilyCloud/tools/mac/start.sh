#!/bin/bash -e

MYDIR=`dirname $0`
cd $MYDIR
cd ../../
TOP_DIR=`pwd`
cd -
FAMILYCLOUD_DIR="$TOP_DIR/www"
DATA_DIR="$TOP_DIR/data"
TMP_DIR="$TOP_DIR/tmp"

cp -f FamilyCloud.conf /etc/apache2/other/


echo "setup FamilyCloud ..."

WWW_DIR=`grep "^[ \t]*DocumentRoot" /etc/apache2/httpd.conf | grep -o "\/[^\"]*"`
echo "www dir: $WWW_DIR"
echo "FamilyCloud: $FAMILYCLOUD_DIR"
rm -f $WWW_DIR/FamilyCloud
ln -s $FAMILYCLOUD_DIR $WWW_DIR/FamilyCloud

echo -n "$TOP_DIR" > $FAMILYCLOUD_DIR/top_dir

#keep below at the end of script
chmod 0777 $FAMILYCLOUD_DIR
chmod -R 0777 $FAMILYCLOUD_DIR
chmod 0777 $DATA_DIR
chmod -R 0777 $DATA_DIR
chmod 0777 $TMP_DIR
chmod -R 0777 $TMP_DIR
rm -rf $TMP_DIR/*

echo "start apache ..."
apachectl restart
echo "done"
sleep 3

open "http://127.0.0.1/FamilyCloud/"

