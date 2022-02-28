#!/bin/bash
# service mysql start

export PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin

echo ""
echo "Starting GoIP SMS System install "
echo ""

if id | grep root > /dev/null
then
        :
else
        echo "You must be root to install these tools."
        exit 1
fi

DISTRIBUTION=DEB
APACHE_PATH="/etc/apache2/sites-enabled"
# [ ! -L /var/lib/mysql/mysql.sock ] && ln -s /var/run/mysqld/mysqld.sock /var/lib/mysql/mysql.sock  

HTTP_PATH=$APACHE_PATH

if [ ! -d ${HTTP_PATH} ]
then
	echo "${HTTP_PATH} do not exist"
	exit 1
fi
# MYSQL_PATH="/usr/bin/mysql"
# ${MYSQL_PATH} -u root < goip/goipinit.sql


# if [ $? = "0" ]
# then
# 	:
# else
# 	echo "Mysql Database error"	
# 	exit 1
# fi


echo '
Alias /goip "/var/www/goip"
<Directory "/var/www/goip">
    Options FollowSymLinks Indexes MultiViews
    AllowOverride None
    Order allow,deny
    Allow from all
</Directory>
' > $HTTP_PATH/goip.conf
echo "Copying file to /usr/local/goip"
if ps aux | grep "goipcron" | grep -v "grep" > /dev/null
then
    killall goipcron
fi
cp -r /goip_install/goip /usr/local/
chmod -R 777 /usr/local/goip
[ ! -L "/var/www/goip" ] && ln -s /usr/local/goip /var/www/goip

[ -f "/etc/conf.d/local.start" ] && local="/etc/conf.d/local.start";
[ -f "/etc/rc.d/rc.local" ] && local="/etc/rc.d/rc.local"
[ -f "/etc/rc.local" ] && local="/etc/rc.local"


rclocaltmp=`mktemp /tmp/rclocal.XXXXXXXXXX`

if grep -q "goipcron" $local
then
        sed /goip/d $local > $rclocaltmp
        cat $rclocaltmp > $local
        rm -f $rclocaltmp
fi

if grep -q "^exit 0$" $local
then
    sed -i '/exit\ 0/i\/usr\/local\/goip\/run_goipcron' /etc/rc.local
else
    echo "/usr/local/goip/run_goipcron" >>$local
fi
/usr/local/goip/run_goipcron

sed -i -e 's/<VirtualHost \*:80>/<VirtualHost \*:44444>/g' /etc/apache2/sites-enabled/000-default
sed -i -e 's/<VirtualHost \*:80>/<VirtualHost \*:44444>/g' /etc/apache2/sites-available/default
sed -i -e 's/NameVirtualHost \*:80/NameVirtualHost \*:44444/g' /etc/apache2/ports.conf
sed -i -e 's/Listen 80/Listen 44444/g' /etc/apache2/ports.conf

echo "Install finished."
echo "SMS SERVER management URL: http://your_ip:44444/goip"
