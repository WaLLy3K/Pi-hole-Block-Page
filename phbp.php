<?php
# Pi-hole Block Page: Configuration

# If user browses to Raspberry Pi's IP manually, where should they be directed?
# Assumes default folder of /var/www/html/, leave blank for none
$landPage = "landing.php";

# Who should whitelist emails go to?
$adminEmail = "admin@domain.com";

# What is the name of your domain, if any? (EG: subdomain.domain.com)
$selfDomain = "";

# What is the URL for your preferred Style Sheet?
#$customCss = "https://wally3k.github.io/style/pihole.css";

# What is the URL for your preferred Favicon?
#$customIcon = "http://pi.hole/admin/img/favicon.png";

# What is the URL for your preferred Logo?
#$customLogo = "https://wally3k.github.io/style/phv.svg";

# What is the URL for your preferred "Blocked by Pi-hole" image?
#$blockImage = "https://wally3k.github.io/style/blocked.svg";

# Should we show a 1x1 blank GIF for iframes from blocked domains?
#$blankGif = "false";

# Should we allow users to whitelist from the block page, with the correct password?
#$allowWhitelisting = "false";

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
