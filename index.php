<?php
// Pi-hole Block Page: Show "Website Blocked" on blacklisted domains
// by WaLLy3K 06SEP16 for Pi-hole

// Define "flagType" of indivudual adlists.list URLs
// TODO: This could be done better
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
  "raw.githubusercontent.com/eladkarako/hosts.eladkarako.com/master/_raw__hosts.txt",
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
  "raw.githubusercontent.com/StevenBlack/hosts/master/data/WindowsSpyBlocker/spy-win81/hosts",
  "raw.githubusercontent.com/StevenBlack/hosts/master/data/WindowsSpyBlocker/spy-win10/hosts",
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

// External config options outside WWW/HTML folder
if (file_exists("/var/phbp.php")) include "/var/phbp.php";

// Merge custom flagTypes with default flagTypes
if (!empty($suspicious_custom)) $suspicious = array_merge($suspicious, $suspicious_custom);
if (!empty($advertising_custom)) $advertising = array_merge($advertising, $advertising_custom);
if (!empty($tracking_custom)) $tracking = array_merge($tracking, $tracking_custom);
if (!empty($malicious_custom)) $malicious = array_merge($malicious, $malicious_custom);

// Default Config Options
if (empty($selfDomain)) $selfDomain = NULL;
if (empty($customCss)) $customCss = "https://wally3k.github.io/style/pihole.css"; // Default CSS
if (empty($customIcon)) $customIcon = "/admin/img/favicon.png"; // Default Favicon
if (empty($customLogo)) $customLogo = "https://wally3k.github.io/style/phv.svg"; // Default Logo
if (empty($blockImage)) $blockImage = "https://wally3k.github.io/style/blocked.svg"; // Default Block Image
if (empty($ignoreUpdate)) $ignoreUpdate = NULL;

// Default: Blank GIF Enabled
if (!isset($blankGif) || $blankGif == "true" || $blankGif == "1") {
  $blankGif = "true";
}else{
  $blankGif = "false";
} 

// Default: Allow whitelisting
if (!isset($allowWhitelisting) || $allowWhitelisting == "true" || $allowWhitelisting == "1") { 
  $allowWhitelisting = "true";
}else{
  $allowWhitelisting = "false";
}

// "Should" prevent arbitrary commands from being run as www-data when using wget
$serverName = escapeshellcmd($_SERVER['SERVER_NAME']);

// Email address config option
if (!empty($adminEmail)) {
  $noticeStr = "<a href='mailto:$adminEmail?subject=Site Blocked: $serverName'>ask to have it whitelisted</a>";
}else{
  $noticeStr = "ask the owner of the Pi-hole in your network to have it whitelisted";
}

// Define which URI extensions get rendered as "Website Blocked"
// Index files should always be rendered as "Website Blocked" anyway
$webRender = array("asp", "htm", "html", "php", "rss", "xml");

// Retrieve serverName URI extension (EG: jpg, exe, php)
$uriExt = pathinfo($_SERVER['REQUEST_URI'], PATHINFO_EXTENSION);

// Handle type of block page
if ($serverName == "pi.hole") {
  header("Location: admin");
}elseif (!empty($landPage) && $serverName == $_SERVER['SERVER_ADDR'] || !empty($landPage) && $serverName == $selfDomain) {
  // When browsing to RPi, redirect to custom landing page
  include $landPage;
  exit();
}elseif (in_array($uriExt, $webRender)) {
  // Valid URL extension to render as "Website Blocked"
}elseif (substr_count($_SERVER['REQUEST_URI'], "?") && isset($_SERVER['HTTP_REFERER']) && $blankGif == "true") {
  // Serve a 1x1 blank gif to POTENTIAL iframe with query string
  die("<img src='data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'>");
}elseif (!empty($uriExt) || substr_count($_SERVER['REQUEST_URI'], "?")) {
  // Invalid URL extension or non-iframed query string
  die('<head><meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/></head><img src="'.$blockImage.'"/>');
}

// Some error handling
if (empty(glob("/etc/pihole/*domains"))) die("[ERROR]: There are no blacklists in the Pi-hole folder! Please update the list of ad-serving domains.");
if (!file_exists("/etc/pihole/adlists.list")) die("[ERROR]: There is no 'adlists.list' in the Pi-hole folder!");

// Crudely check for update
function checkUpdate() {
  global $ignoreUpdate;
  if (!isset($ignoreUpdate) || $ignoreUpdate == "false" || $ignoreUpdate == "0") {
    $localIndex = hash_file('crc32', __FILE__);
    $remoteIndex = hash_file('crc32', 'https://raw.githubusercontent.com/WaLLy3K/Pi-hole-Block-Page/master/index.php');
    if (empty($remoteIndex)) return; // In case retrieval fails
    if ($localIndex !== $remoteIndex) echo " (Update available)";
  }
}

// Get all URLs starting with "http" from adlists.list
// $urlList array key expected to match .domains list // in $listMatches!!
// This may not work if admin updates gravity, and later inserts a new hosts URL at anywhere but the end before re-running gravity
$urlList = array_values(preg_grep("/(^http)|(^www)/i", file("/etc/pihole/adlists.list", FILE_IGNORE_NEW_LINES)));

// Strip any combo of HTTP, HTTPS and WWW
$urlList_match = preg_replace("/https?\:\/\/(www.)?/i", "", $urlList);

// Exact search, returning a numerically sorted # list of matching .domains
// Returns "txt" if manually blacklisted
exec('wget -qO - "http://pi.hole/admin/scripts/pi-hole/php/queryads.php?domain="'.$serverName.'"&exact" | grep -E "(\.domains|blacklist\.txt).*\([1-9]" | awk -F "[\/ \.]" \'{print $7}\' | sort -un', $listMatches);

// Return how many lists serverName is featured in
if (in_array("txt", $listMatches)) {
  $featuredTotal = "-1";
}else{
  $featuredTotal = count(array_values(array_unique($listMatches)));
}

if ($featuredTotal == "-1") {
    $notableFlag = "Blacklisted manually";
}elseif ($featuredTotal == "0") {
    $notableFlag = "No landing page specified";
}else{
  $in = NULL;
  // Define "Featured Flag"
  foreach ($listMatches as $num) {
    // Create a string of flags for serverName
    if(in_array($urlList_match[$num], $suspicious)) $in .= "sus ";
    if(in_array($urlList_match[$num], $advertising)) $in .= "ads ";
    if(in_array($urlList_match[$num], $tracking)) $in .= "trc ";
    if(in_array($urlList_match[$num], $malicious)) $in .= "mal ";
    
    // Return value of worst flag to user (EG: Malicious more notable than Suspicious)
    if (substr_count($in, "sus")) $notableFlag = "Suspicious";
    if (substr_count($in, "ads")) $notableFlag = "Advertising";
    if (substr_count($in, "trc")) $notableFlag = "Tracking & Telemetry";
    if (substr_count($in, "mal")) $notableFlag = "Malicious";
    
    // Do not show primary flag if we are unable to find one
    if (empty($in)) $notableFlag = "-1";
  }
}
?>
<!DOCTYPE html><head>
  <meta charset='UTF-8'/>
  <title>Website Blocked</title>
  <link rel='stylesheet' href='<?php echo $customCss; ?>'/>
  <link rel='shortcut icon' href='<?php echo $customIcon; ?>'/>
  <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no'/>
  <meta name='robots' content='noindex,nofollow'/>
  <script src="http://pi.hole/admin/scripts/vendor/jquery.min.js"></script>
  <script>
    function tgVis(id) {
      var e = document.getElementById('querylist');
      if(e.style.display == 'block') {
        e.style.display = 'none';
        document.getElementById("info").innerHTML = "More Info";
      }else{
        e.style.display = 'block';
        document.getElementById("info").innerHTML = "Less Info";
      }
    }
  </script>
  <style>
    header h1:before, header h1:after { background-image: url('<?php echo $customLogo; ?>'); }
  </style>
  <noscript><style>
    #querylist { display: block; }
    .buttons { display: none; }
  </style></noscript>
</head><body><header>
  <h1><a href='/'>Website Blocked</a></h1>
</header><main>
  <div class="url">
    Access to the following site has been blocked:
    <span class="msg"><?php echo $serverName; ?></span>
  </div>
  <?php if ($notableFlag !== "-1") { ?>
  <div class="flag">
    This is primarily due to being flagged as:
    <span class='msg'><?php echo $notableFlag; ?></span>
  </div>
  <?php } ?>
  <div class="notice">
    If you have an ongoing use for this website, please <?php echo $noticeStr; ?>.
  </div>
  <div class='buttons'>
    <a id='back' href='javascript:history.back()'>Back to safety</a>
    <?php if ($featuredTotal != "0") echo "<a id='info' onclick='tgVis(\"querylist\");'>More Info</a>"; ?>
  </div> 
  <div id='querylist'>This site is found in <?php echo "$featuredTotal of ".count($urlList); ?> lists:
    <pre id='output'><?php foreach ($listMatches as $num) { echo "[$num]:\t<a href='$urlList[$num]'>$urlList[$num]</a><br/>"; } ?></pre>
    <?php if ($allowWhitelisting == "true") { ?>
    <form class='buttons'>
      <input id='domain' value='<?php echo $serverName; ?>' disabled>
      <input type='password' id='pw' name='pw' placeholder='Pi-hole Password'/>
      <button id='whitelist' type='button'>Whitelist</button>
     </form>
     <pre id='notification' hidden='true'></pre>
     <?php } ?>
  </div>
</main>
<footer>Generated <?php echo date("D g:i A, M d"); ?> by <a href='https://github.com/WaLLy3K/Pi-hole-Block-Page'>Pi-hole Block Page</a><?php checkUpdate(); ?></footer>
<script>
  function add() {
    var domain = $("#domain");
    var pw = $("#pw");
    if(domain.val().length === 0){
      return;
    }

    $.ajax({
      url: "admin/scripts/pi-hole/php/add.php",
      method: "post",
      data: {"domain":domain.val(), "list":"white", "pw":pw.val()},
      success: function(response) {
        $( "#notification" ).removeAttr( "hidden" );
        if(response.indexOf("Pi-hole blocking") !== -1){
          // Reload page after 5 seconds
          setTimeout(function(){window.location.reload(1);}, 5000);
          $( "#notification" ).html("Success! You may have to flush your DNS cache");
        }else{
          $( "#notification" ).html(""+response+"");
        }

      },
      error: function(jqXHR, exception) {
        $( "#notification" ).removeAttr( "hidden" );
        // Assume javascript is enabled, but external files are being blocked (EG: Noscript/Scriptsafe)
        $( "#notification" ).html("Unable to load external jQuery script");
      }
    });
  }

  // Handle enter button for adding domains
  $(document).keypress(function(e) {
      if(e.which === 13 && $("#pw").is(":focus")) {
          add();
      }
  });

  // Handle buttons
  $("#whitelist").on("click", function() {
      add();
  });
</script>
</body></html>
