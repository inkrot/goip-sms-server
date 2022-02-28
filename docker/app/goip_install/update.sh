#!/bin/sh

killall goipcron$1 >/dev/null 2>/dev/null
sleep 0.5

echo "Copying file to /usr/local/goip$1"
if [ $# = 1 ] ; then
 rm goip$1 -R
 cp -rf goip goip$1
fi
cp -rf /usr/local/goip$1/inc/config.inc.php /usr/local/goip$1/inc/config.inc.php0
cp -rf /usr/local/goip$1/inc/ussd_white.php /usr/local/goip$1/inc/ussd_white.php0
cp -rf goip$1 /usr/local/
chmod -R 777 /usr/local/goip$1

cd /usr/local/goip$1/
mv -f /usr/local/goip$1/inc/config.inc.php0 /usr/local/goip$1/inc/config.inc.php 
mv -f /usr/local/goip$1/inc/ussd_white.php0 /usr/local/goip$1/inc/ussd_white.php 

if [ $# = 1 ] ; then
cp goipcron goipcron$1
#port=`expr $1 + 44444`
#sed -i "s/='goip'/='goip$1'/g" inc/config.inc.php 
#sed -i "s/44444/$port/g" inc/config.inc.php

echo "#!/bin/sh

killall goipcron$1 >/dev/null 2>/dev/null
sleep 0.5
cd /usr/local/goip$1
./goipcron$1 inc/config.inc.php
echo \"goipcron$1 start\"" > run_goipcron

find . -type f| xargs sed -i "s/_SESSION\['goip/_SESSION\['goip$1/g"
fi

php ./update.php
sleep 0.5

./run_goipcron
echo "update done!"
