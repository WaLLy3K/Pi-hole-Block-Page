# Pi-hole-Block-Page
A "Website Blocked" page to go with https://pi-hole.net

## Disclaimer:
This repo is a work in progress and consider all code to be **completely untested** until further notice.

## Install:
````
sudo wget -q https://raw.githubusercontent.com/WaLLy3K/Pi-hole-Block-Page/master/index.php -O /var/www/html/index.php
sudo chmod 755 /var/www/html/index.php
sudo sed -i 's:pihole/index.html:index.php:' /etc/lighttpd/lighttpd.conf
sudo service lighttpd force-reload
````

## Website Test Cases:

* http://192.168.1.x (Raspberry Pi IP) -- landing page
* http://pi.hole -- site, blacklisted manually
* http://doubleclick.net/ -- site
* http://doubleclick.net?pihole=more -- site, more
* http://doubleclick.net/some/folder -- site
* http://doubleclick.net/some/content.php -- site
* http://doubleclick.net/some/content.php?query=true -- file
* http://doubleclick.net/some/content.php?query=true&pihole=more -- site, more
* http://doubleclick.net/file.exe -- file
* http://doubleclick.net/file.exe?query=true -- file
* http://doubleclick.net/some/image.gif -- file
* http://doubleclick.net/image.gif?query=true -- file
 
 
## Changelog:

Update 10SEP16: Removed $ from grep search
