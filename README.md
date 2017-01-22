# Pi-hole Block Page
A stylish and user friendly 'Website Blocked' page to add onto a [Pi-hole installation](https://pi-hole.net).

## What does this do?
The goal is to provide concise and relevant feedback to the end user, so they are better informed about the site they are trying to visit. This is very useful because an Internet connection is generally shared with a number of other people (and not just the person who set up Pi-hole on the network).

When a user browses to a blacklisted domain (For example, `doubleclick.net`), they will be presented with the block page *as pictured below*. This clearly shows what domain was blocked, what the most 'notable' list it was featured in, how they can go about resolving the issue and whether they would like to see [more information](http://i.imgur.com/KIn6Yxp.png).

In this case, `doubleclick.net` was found in `https://s3.amazonaws.com/lists.disconnect.me/simple_malvertising.txt` which has been classed as Tracking & Telemetry from within the [definitions list](https://github.com/WaLLy3K/wally3k.github.io/blob/master/classification.ini).

![Pi-hole Block Page](http://i.imgur.com/t30mis4.png)

When a user (or site on the user's behalf) attempts to access any non HTML resource (i.e: not HTML, PHP, XML or RSS), the page will interpret this request as a 'file' and will show the following image:

![Blocked by Pi-hole](https://wally3k.github.io/style/blocked.svg)

If the 'Website Blocked' page is accessed through an iframe, a 1x1 transparent GIF will be shown (by default).

Finally: If a landing page has been specified by the Pi-hole admin, a user will be direct to that page if they enter the Pi-hole IP address (or domain name, if configured).

## Customisation:
Everyone has different needs! Therefore, the admin of the Pi-hole Block Page has the ability to customise the following options:

* **Set the class file**: Use your own mirror for blacklist classifications (By default, hosted on [wally3k.github.io](https://github.com/WaLLy3K/wally3k.github.io/blob/master/classification.ini))
* **Set the class file update time**: How often the definitions file will be checked for an update
* **Set a landing page**: Use your Lighttpd install for something more than just Pi-hole
* **Set a domain name**: If you have a website you'd like to make publicly facing, you can set your domain name and have it direct to the landing page
* **Set an admin email**: When users come across the block page, they will be presented with a link to email the admin in case they want it whitelisted
* **Set custom style sheet**: Not everyones sense of asthetics is the same
* **Set custom favicon**: Change the bookmark icon to suit your needs
* **Set custom logo**: Change the logo on the top left of the page to suit your needs
* **Set block image**: Display an image of your choosing when a user comes across blocked 'file' content
* **Display blank gif**: Choose whether iframed ads should display nothing, or the block image
* **Allow whitelisting**: Choose whether users have the option to enter a password to whitelist a blocked page
* **Ignore updates**: Don't get notifications of updates (which would make me very sad)
* **Execution time**: Display how long it took to generate the 'Website Blocked' page
* **Definitions**: Add your own blocklist URLs and define if they are Suspicious, Advertising, Tracking or Malicious

## Install & Update:
You will need to open up your preferred SSH client and enter the following commands:

````
[ -f "/var/phbp.ini" ] && sudo mv /var/phbp.ini /var/phbp.ini.BAK
html=$(grep server.document-root /etc/lighttpd/lighttpd.conf | awk -F\" '{print $2}')
sudo wget -q https://raw.githubusercontent.com/WaLLy3K/Pi-hole-Block-Page/master/index.php -O "$html/index.php"
sudo wget -q https://raw.githubusercontent.com/WaLLy3K/Pi-hole-Block-Page/master/phbp.ini -O "/var/phbp.ini"
sudo chmod 755 "$html/index.php"
[ -f "/var/phbp.php" ] && sudo mv /var/phbp.php /var/phbp.old.BAK
[ ! -d "/etc/lighttpd/conf-enabled" ] && sudo mkdir -m 755 /etc/lighttpd/conf-enabled
[ ! -f "/etc/lighttpd/conf-enabled/phbp.conf" ] && echo -e '# Pi-hole "server.error-handler-404" override\nurl.rewrite-once = ( "pihole/index.php" => "/index.php" )' | sudo tee /etc/lighttpd/conf-enabled/phbp.conf
echo "Done! Please edit '/var/phbp.ini' to customise your install"
sudo service lighttpd force-reload
````

This script will not presume where the default document-root is, as [installations such as DietPi](https://github.com/Fourdee/DietPi/blob/master/dietpi/dietpi-software#L3552) are known to change this.

## Uninstall:
If you would like to remove Pi-hole Block Page, you can enter the following via SSH:

````
html=$(grep server.document-root /etc/lighttpd/lighttpd.conf | awk -F\" '{print $2}')
sudo rm -rf $html/index.php /var/phbp.ini /etc/lighttpd/conf-enabled/phbp.conf
[ -f "/var/phbp.ini.BAK" ] rm -f /var/phbp.ini.BAK
sudo service lighttpd force-reload
````

## Website Test Cases:

* http://pi.hole -- redirect to Pi-hole Admin Interface
* http://192.168.1.x (Pi-hole IP) -- landing page, if defined
* http://pi.domain.com (Pi-hole Domain) -- landing page, if defined
* http://doubleclick.net/ -- Website Blocked
* http://doubleclick.net/?debug -- Block Page Debug Output
* http://doubleclick.net/?debug=conf -- Block Page Debug Output (with redacted user config)
* http://doubleclick.net/some/folder -- Website Blocked
* http://doubleclick.net/some/content.php -- Website Blocked
* http://doubleclick.net/some/content.php?query=true -- Website Blocked
* http://doubleclick.net/file.exe -- Blocked by Pi-hole Image
* http://doubleclick.net/some/image.gif -- Blocked by Pi-hole Image
 
 
## Troubleshooting:
If you are having *any* issues with Pi-hole Block Page, please open a [new ticket](https://github.com/WaLLy3K/Pi-hole-Block-Page/issues) so it can be checked out. If necessary, include the output of either `?debug` or `?debug=conf` (e.g: `http://doubleclick.net?debug=conf`).

 
## Postscript:

I am more than happy to take on board suggestions and feature requests, through the [issues tracker](https://github.com/WaLLy3K/Pi-hole-Block-Page/issues). On top of this, you are welcome to modify and adapt this project as you see fit. However, please remember that the name 'Pi-hole', the vortex logo, 'boxed-bg.jpg' and Javascript whitelisting code remains property of [Pi-hole, LLC](https://pi-hole.net/).
