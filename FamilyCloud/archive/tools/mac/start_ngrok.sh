#!/bin/bash

cd `dirname $0`

if [ -e pid ]; then
	kill -9 `cat pid`
	sleep 3
fi
python -c 'import pty, sys; pty.spawn(sys.argv[1:])' ./ngrok http 80 &
echo $! > pid

sleep 5
while true; do
	addr=`wget http://127.0.0.1:4040 -q -O - | grep -o "http://[^,]*.ngrok.io"`
	echo "myaddr: $addr"
	sleep 60
done

