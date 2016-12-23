<?php
# Pi-hole Block Page: Show "Website Blocked" on blacklisted domains
# by WaLLy3K 06SEP16 for Pi-hole

# If user browses to Raspberry Pi's IP manually, where should they be directed?
# Assumes default folder of /var/www/html/, leave blank for none
$landPage = "landing.php";

# Who should whitelist emails go to?
$adminEmail = "admin@domain.com";

# What is the name of your domain, if any? (EG: mypi.dyndns.net)
$selfDomain = "";

# Define "flagType" of indivudual adlists.list URLs
# Please add any domains here that has been manually placed in adlists.list
# TODO: This could be done better
$suspicious = array(
  "raw.githubusercontent.com/AdAway/adaway.github.io/master/hosts.txt",
  "raw.githubusercontent.com/StevenBlack/hosts/master/hosts",
  "adblock.gjtech.net/?format=unix-hosts",
  "sysctl.org/cameleon/hosts",
  "hosts-file.net/ad_servers.txt",
  "adblock.mahakala.is",
  "raw.githubusercontent.com/crazy-max/WindowsSpyBlocker/master/data/hosts/win10/spy.txt",
  "securemecca.com/Downloads/hosts.txt",
  "raw.githubusercontent.com/BreakingTheNews/BreakingTheNews.github.io/master/hosts",
  "raw.githubusercontent.com/Dawsey21/Lists/master/main-blacklist.txt",
  "raw.github.com/notracking/hosts-blocklists/master/hostnames.txt",
  "raw.github.com/notracking/hosts-blocklists/master/domains.txt",
  "raw.githubusercontent.com/mat1th/Dns-add-block/master/hosts",
  "bitbucket.org/ethanr/dns-blacklists/raw/8575c9f96e5b4a1308f2f12394abd86d0927a4a0/bad_lists/hosts.txt",
  "bitbucket.org/ethanr/dns-blacklists/raw/8575c9f96e5b4a1308f2f12394abd86d0927a4a0/bad_lists/dom-bl-base.txt",
  "bitbucket.org/ethanr/dns-blacklists/raw/8575c9f96e5b4a1308f2f12394abd86d0927a4a0/bad_lists/Mandiant_APT1_Report_Appendix_D.txt",
  "hostsfile.org/Downloads/hosts.txt",
  "raw.githubusercontent.com/joeylane/hosts/master/hosts",
  "winhelp2002.mvps.org/hosts.txt",
  "hostsfile.mine.nu/hosts0.txt",
  "raw.githubusercontent.com/piwik/referrer-spam-blacklist/master/spammers.txt",
  "raw.githubusercontent.com/ReddestDream/reddestdream.github.io/master/Projects/MinimalHosts/etc/MinimalHostsBlocker/minimalhosts",
  "raw.githubusercontent.com/StevenBlack/hosts/master/data/add.Dead/hosts",
  "raw.githubusercontent.com/StevenBlack/hosts/master/data/KADhosts/hosts",
  "someonewhocares.org/hosts/zero/hosts",
  "raw.githubusercontent.com/vokins/yhosts/master/hosts",
  "raw.githubusercontent.com/vokins/yhosts/master/hosts",
);

$advertising = array(
  "s3.amazonaws.com/lists.disconnect.me/simple_ad.txt",
  "optimate.dl.sourceforge.net/project/adzhosts/HOSTS.txt",
  "raw.githubusercontent.com/quidsup/notrack/master/trackers.txt",
  "pgl.yoyo.org/adservers/serverlist.php?hostformat=nohtml",
  "raw.githubusercontent.com/StevenBlack/hosts/master/data/UncheckyAds/hosts",
  "raw.githubusercontent.com/StevenBlack/hosts/master/data/SpotifyAds/hosts",
);

$tracking = array(
  "s3.amazonaws.com/lists.disconnect.me/simple_tracking.txt",
  "raw.githubusercontent.com/crazy-max/WindowsSpyBlocker/master/data/hosts/win10/spy.txt",
  "raw.githubusercontent.com/quidsup/notrack/master/trackers.txt",
  "raw.githubusercontent.com/quidsup/notrack/master/trackers.txt",
  "raw.githubusercontent.com/StevenBlack/hosts/master/data/add.2o7Net/hosts",
  "raw.githubusercontent.com/StevenBlack/hosts/master/data/Telemetry/hosts",
  "raw.githubusercontent.com/StevenBlack/hosts/master/data/tyzbit/hosts",
);

$malicious = array(
  "mirror1.malwaredomains.com/files/justdomains",
  "s3.amazonaws.com/lists.disconnect.me/simple_malvertising.txt",
  "zeustracker.abuse.ch/blocklist.php?download=domainblocklist",
  "ransomwaretracker.abuse.ch/downloads/RW_DOMBL.txt",
  "malwaredomains.lehigh.edu/files/domains.txt",
  "hosts-file.net/emd.txt",
  "hosts-file.net/exp.txt",
  "mirror.cedia.org.ec/malwaredomains/immortal_domains.txt",
  "malwaredomainlist.com/hostslist/hosts.txt",
  "mirror.cedia.org.ec/malwaredomains/justdomains",
  "raw.githubusercontent.com/quidsup/notrack/master/malicious-sites.txt",
  "raw.githubusercontent.com/StevenBlack/hosts/master/data/add.Risk/hosts",
);

# Define which URL extensions get rendered as "Website Blocked"
# Index files should always be rendered as "Website Blocked" anyway
$webRender = array('asp', 'htm', 'html', 'php', 'rss', 'xml');

# "Should" prevent arbitrary commands from being run as www-data when using grep
$serverName = escapeshellcmd($_SERVER['SERVER_NAME']);

# Retrieve server URI extension (EG: jpg, exe, php)
$uriExt = pathinfo($_SERVER['REQUEST_URI'], PATHINFO_EXTENSION);

# Define URI types
if ($serverName == "pi.hole") {
  header('Location: admin');
}elseif (!empty($landPage) && $serverName == $_SERVER['SERVER_ADDR'] || !empty($landPage) && $serverName == $selfDomain) {
  # When browsing to RPi, redirect to custom landing page
  include $landPage;
  exit();
}elseif (substr_count($_SERVER['REQUEST_URI'], "pihole=more")) {
  # "pihole=more" is set
  $uriType = "more";
}elseif (in_array($uriExt, $webRender)) {
  $uriType = "site";
}elseif (!empty($uriExt) || substr_count($_SERVER['REQUEST_URI'], "?")) {
  # If file extension, or query string
  $uriType = "file";
}else{
  $uriType = "site";
}

# Handle incoming URI types
if ($uriType == "file"){
  # Serve this SVG to URI's defined as file
  die('<head><meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/></head><svg width="110" height="18"><defs><style>.c1 {fill: #942525; fill-rule: evenodd;} .c2 {fill: rgba(0,0,0,0.3); font-size: 11px; font-family: Arial;}</style></defs>
    <path class="c1" d="M8,0A8,8,0,1,1,0,8,8,8,0,0,1,8,0ZM8,2A6,6,0,1,1,2,8,6,6,0,0,1,8,2Z"/><path class="c1" d="M2,12.625L11.625,3,13,4.375,3.375,14Z"/>
    <text x="18" y="12" class="c2">Blocked by Pi-hole</text></svg>');
}else{
  # Some error handling
  $domainList = glob('/etc/pihole/*domains');
  if (empty($domainList)) die("[ERROR]: There are no blacklists in the Pi-hole folder! Please update the list of ad-serving domains.");
  if (!file_exists("/etc/pihole/adlists.list")) die("[ERROR]: There is no 'adlists.list' in the Pi-hole folder!");
  
  # Grep exact search $serverName within individual blocked .domains lists
  # Returning a numerically sorted array of the "list #" of matching .domains
  exec('sudo pihole -q "'.$serverName.'" | grep -v "0 results" | cut -d. -f2 | sort -un', $listMatches);
  
  # Remove blank entries created by grep -v, but not 0 value
  $listMatches = array_filter($listMatches, 'strlen');

  # Get all URLs starting with "http" from adlists.list
  # $urlList array key expected to match .domains list # in $listMatches!!
  # This may not work if admin updates gravity, and later inserts a new hosts URL at anywhere but the end
  # Pi-hole seemingly will not update .domains correctly if this occurs, as of 10SEP16
  $urlList = array_values(preg_grep("/(^http)|(^www)/i", file('/etc/pihole/adlists.list', FILE_IGNORE_NEW_LINES)));
  
  # Strip any combo of HTTP, HTTPS and WWW
  $urlList_match = preg_replace('/https?\:\/\/(www.)?/i', '', $urlList);
  
  # Return how many lists URL is featured in, and total lists count
  $featuredTotal = count(array_values(array_unique($listMatches)));
  $totalLists = count($urlList);

  # Featured total will be 0 for a manually blacklisted site
  # Or for a domain not found within "flagType" array
  if ($featuredTotal == "0") {
      $notableFlag = "Blacklisted manually";
  }else{
    $in = NULL;
    # Define "Featured Flag"
    foreach ($listMatches as $num) {
      # Create a string of flags for URL
      if(in_array($urlList_match[$num], $suspicious)) $in .= "sus ";
      if(in_array($urlList_match[$num], $advertising)) $in .= "ads ";
      if(in_array($urlList_match[$num], $tracking)) $in .= "trc ";
      if(in_array($urlList_match[$num], $malicious)) $in .= "mal ";
      
      # Return value of worst flag to user (EG: Malicious more notable than Suspicious)
      if (substr_count($in, "sus")) $notableFlag = "Suspicious";
      if (substr_count($in, "ads")) $notableFlag = "Advertising";
      if (substr_count($in, "trc")) $notableFlag = "Tracking & Telemetry";
      if (substr_count($in, "mal")) $notableFlag = "Malicious";
      if (empty($in)) $notableFlag = "Unspecified Flag";
    }
  }

  # Probably redundant since this page should only display if dnsmasq working
  $piStatus = exec('pgrep dnsmasq | wc -l');
  if ($piStatus > "0") {
    $piInfo = "class='active'>Active &#10003;";
  }else{
    $piInfo = "class='inactive'>Offline &#10007;";
  }

  echo "<!DOCTYPE html><head>
      <meta charset='UTF-8'/>
      <title>Website Blocked</title>
      <link rel='stylesheet' href='https://cdn.rawgit.com/WaLLy3K/Pi-hole-Block-Page/master/style.css'/>
      <link rel='shortcut icon' href='/admin/img/favicon.png' type='image/png'/>
      <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no'/>
      <meta name='robots' content='noindex,nofollow'/>
    </head><body><header id='block'>
      <h1><a href='/'>Website Blocked</a></h1>
      <div class='alt'>Pi-hole Status:<br/><span $piInfo</span></div>
    </header><main>
    <div class='blocked'>
      Access to the following site has been blocked:<br/>
      <span class='phmsg'>$serverName</span>
      This is primarily due to being flagged as:<br/>
      <span class='phmsg'>$notableFlag</span>
      If you have an ongoing use for this website, please <a href='mailto:$adminEmail?subject=Site Blocked: $serverName'>ask to have it whitelisted</a>.
    </div>
    <div class='buttons'><a class='safe' href='javascript:history.back()'>Back to safety</a>
  ";

  # More Information, for the technically inclined
  if ($uriType == "more" && $featuredTotal != "0") {
    # Remove pihole=more string for hyperlink
    $uriStrip = preg_replace("/.pihole=more/", "", $_SERVER['REQUEST_URI']);
    echo "&nbsp;<a class='warn' href='http://$serverName$uriStrip'>Less Info</a></div>";
    echo "<br/><div>This site is found in $featuredTotal of $totalLists .domains ".(count($listMatches) == 1 ? 'list' : 'lists').": ".implode(', ', $listMatches)."</div>";
    # Native scrolling on iOS is a nice touch
    echo "<div style='font-family: monospace; font-size: 0.8em;margin: 2px 0 0 8px; overflow: auto; white-space: nowrap; -webkit-overflow-scrolling: touch; width: 100%;'>";
    foreach ($listMatches as $num) {
      echo "  [$num]: <a href='$urlList[$num]'>$urlList[$num]</a><br/>";
    }
    echo "</div>";
  }elseif ($featuredTotal != "0") {
    # Strip query string for hyperlink
    $uriStrip = preg_replace("/\?.*/", "", $_SERVER['REQUEST_URI']);
    echo "&nbsp;<a class='warn' href='http://$serverName$uriStrip?pihole=more'>More Info</a></div>";
  }

  echo "  
    </main>
    <footer>Generated ".date('D g:i A, M d')." by <a href='https://github.com/WaLLy3K/Pi-hole-Block-Page'>Pi-hole Block Page</a></footer>
    </body></html>
  ";
}
?>
