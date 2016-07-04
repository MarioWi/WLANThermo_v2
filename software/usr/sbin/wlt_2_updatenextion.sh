#!/bin/bash

echo 'Displaydienst wird beendet' > /var/www/tmp/nextionupdatelog
service WLANThermoWD stop
if [ -e '/var/run/wlt_2_nextion.pid' ]
then
   kill $(< /var/run/wlt_2_nextion.pid)
   sleep 5
fi
python /usr/sbin/nextionupload.py $1 >> /var/www/tmp/nextionupdatelog
RETVAL=$?
[ $RETVAL -eq 0 ] && echo 'Erfolgreich upgedatet!' && rm /var/www/tmp/nextionupdate
[ $RETVAL -ne 0 ] && echo 'Fehler beim Upload!'
sleep 1
echo 'WLANThermodienste werden neu gestartet' >> /var/www/tmp/nextionupdatelog
service WLANThermo restart
service WLANThermoWD restart
mv /var/www/tmp/nextionupdatelog /var/www/tmp/nextionupdatelog.old
