<?php
# Pi-hole Block Page: Configuration

# If user browses to Raspberry Pi's IP manually, where should they be directed?
# Assumes default folder of /var/www/html/, leave blank for none
$landPage = "landing.php";

# Who should whitelist emails go to?
$adminEmail = "admin@domain.com";

# What is the name of your domain, if any? (EG: subdomain.domain.com)
$selfDomain = "";

# Please add any domains here that has been manually placed in adlists.list
# Do not include HTTP/HTTPS/WWW or trailing slash
$suspicious_custom = array(
  "pilotfiber.dl.sourceforge.net/project/adzhosts/HOSTS.txt",
  "securemecca.com/Downloads/hosts.txt",
);

$advertising_custom = array(
  "raw.githubusercontent.com/BreakingTheNews/BreakingTheNews.github.io/master/hosts",
);

$tracking_custom = array(
  "raw.githubusercontent.com/crazy-max/WindowsSpyBlocker/master/data/hosts/win10/spy.txt",
);

$malicious_custom = array(
  #"malwaredomains.lehigh.edu/files/domains.txt",
);

?>
