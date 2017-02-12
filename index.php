<?php
// Pi-hole Block Page: Show "Website Blocked" on blacklisted domains
// by WaLLy3K 06SEP16 for Pi-hole
$phbpVersion = "2.2.1";

// Debugging
$debug = (isset($_GET["debug"]) ? true : false);

// Parse user config
$phbpConfig = (is_file("/var/phbp.ini") ? "/var/phbp.ini" : false);
if (!$phbpConfig) die("[ERROR]: User configuration was not found at: /var/phbp.ini");
$usrIni = parse_ini_file($phbpConfig, true);

// Retrieve essential user config options
$selfDomain = (!isset($usrIni["selfDomain"])  ? false : $usrIni["selfDomain"]);
$landPage   = (!isset($usrIni["landPage"])    ? false : $usrIni["landPage"]);
$blankGif   = (!isset($usrIni["blankGif"])    ? true : false); // Unset: Enabled
$blockImage = (!isset($usrIni["blockImage"])  ? true : $usrIni["blockImage"]);

// Handle configured variables
if (empty($blankGif)) $blankGif = (in_array($usrIni["blankGif"], array('true','TRUE','yes','YES','1')) ? true : false);

// Config error checking
if ($selfDomain && !is_valid_domain_name($selfDomain)) die("[ERROR]: The configured selfDomain '$selfDomain' does not appear to be valid. Please correct this within $phbpConfig");
if ($landPage && !is_file($landPage)) die("[ERROR]: The configured landpage '$landPage' does not exist. Please create this file locally, or correct this within $phbpConfig");

// Sanitise FQDN input
$domainName = filter_var($_SERVER["SERVER_NAME"], FILTER_SANITIZE_SPECIAL_CHARS);

// Define which URI extensions get rendered as 'Website Blocked' (Including empty for index.ext)
$webRender = array("asp", "htm", "html", "php", "rss", "xml", "");

// Retrieve serverName URI extension (e.g: jpg, exe, php)
$uriExt = pathinfo($_SERVER["REQUEST_URI"], PATHINFO_EXTENSION);

// Load Lighttpd config for use with set_xpihole_header() function
$lighttpdConf = (is_file("/etc/lighttpd/lighttpd.conf") ? file("/etc/lighttpd/lighttpd.conf") : false);

// Mobile Viewport String
$viewPort = "<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no'/>";

// Handle block page redirects
if ($domainName == "pi.hole") {
  // Redirect user to Pi-hole Admin Console
  header("Location: /admin");
  exit();
} elseif ($domainName == $_SERVER["SERVER_ADDR"] && $landPage || $domainName == $selfDomain && $landPage) {
  // Redirect IP addr, or configured selfDomain to custom landing page
  include $landPage;
  exit();
} elseif ($debug) {
  // Skip checks and render block page
} elseif (substr_count($_SERVER["REQUEST_URI"], "?") && isset($_SERVER["HTTP_REFERER"]) && $blankGif) {
  // Assume that REQUEST_URI with query string and HTTP_REFERRER is PHBP being called from an iframe
  // Serve a 1x1 blank gif
  set_xpihole_header();
  die("<img src='data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACwAAAAAAQABAAACAkQBADs='>");
} elseif (!in_array($uriExt, $webRender) || substr_count($_SERVER["REQUEST_URI"], "?")) {
  // Non HTML renderable URL extension or non-iframed query string
  // Serve 'Mobile Friendly' block image
  set_xpihole_header();
  $blockHtml = ($blockImage !== true ? '<a href="/"><img src="$blockImage"/></a>' : '<a href="/"><svg xmlns="http://www.w3.org/2000/svg" width="110" height="16"><defs><style>a {text-decoration: none;} circle {stroke: rgba(152,2,2,0.5); fill: none; stroke-width: 2;} rect {fill: rgba(152,2,2,0.5);} text {opacity: 0.3; font: 11px Arial;}</style></defs><circle cx="8" cy="8" r="7"/><rect x="10.3" y="-6" width="2" height="12" transform="rotate(45)"/><text x="19.3" y="12">Blocked by Pi-hole</text></svg></a>');
  die("<head>$viewPort</head>$blockHtml");
}

// Now to render the block page
set_xpihole_header();

if ($debug) {
  // Print redacted user config if ?debug=conf used
  $debugConf = (strpos($_GET["debug"], "conf") !== false ? true : false);
  echo "<title>Debug Output</title><style>body { background: #e0e0e0; font-size: 12px; } md { font-size: 0; } pre { margin: 0; }</style>$viewPort<body><pre>";
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
}

// Check setupVars for WEBPASSWORD
if (!is_file("/etc/pihole/setupVars.conf")) die("[ERROR]: Unable to retrieve file: /etc/pihole/setupVars.conf");
$setupVars = file("/etc/pihole/setupVars.conf", FILE_IGNORE_NEW_LINES);
$webPassword = (preg_replace("/(.*=)/", "", array_stripos("WEBPASSWORD", $setupVars)) ? true : false);
$setupVars = null;

// Confirm that Pi-hole has generated at least one block list
if (empty(glob("/etc/pihole/list.0.*.domains"))) die("[ERROR]: There are no generated block lists (list.#.site.com.domains) in the Pi-hole folder! Please update gravity by running pihole -g.");

// Determine which adlists file to use
$adlist = (is_file("/etc/pihole/adlists.list") ? "/etc/pihole/adlists.list" : false);
if (!$adlist) $adlist = (is_file("/etc/pihole/adlists.default") ? "/etc/pihole/adlists.default" : false);
if (!$adlist) die("[ERROR]: Neither 'adlists.list' or 'adlists.default' were found within /etc/pihole");

// Strip HTTP/HTTPS/WWW and final / for URL matching
$strip = "/(https?:\/\/)|(www\.)|(\/$)/i";

// Parse remaining content of user config file
$iniUrl       = (!isset($usrIni["classFile"])       ? "https://raw.githubusercontent.com/WaLLy3K/wally3k.github.io/master/classification.ini" : $usrIni["classFile"]);
$iniUpdTime   = (!isset($usrIni["classUpdateTime"]) ? "172800" : $usrIni["classUpdateTime"]); // Default: 48 hours
$adminEmail   = (!isset($usrIni["adminEmail"])      ? false : $usrIni["adminEmail"]);
$allowWhitel  = (!isset($usrIni["allowWhitelist"])  ? true : false); // Unset: Enabled
$checkUpdates = (!isset($usrIni["checkUpdates"])    ? true : false); // Unset: Enabled
$exeTime      = (!isset($usrIni["exeTime"])         ? false : true); // Unset: Disabled

// Block page custom strings
$bpTitle    = (!isset($usrIni["customTitle"])     ? "Website Blocked" : $usrIni["customTitle"]);
$bpCss      = (!isset($usrIni["customCss"])       ? "https://wally3k.github.io/style/pihole.css" : $usrIni["customCss"]);
$bpIcon     = (!isset($usrIni["customIcon"])      ? "/admin/img/favicon.png" : $usrIni["customIcon"]);
$bpHeading  = (!isset($usrIni["customHeading"])   ? "Website Blocked" : $usrIni["customHeading"]);
$bpLogo     = (!isset($usrIni["customLogo"])      ? "https://wally3k.github.io/style/phv.svg" : $usrIni["customLogo"]);

$bpBlock    = (!isset($usrIni["blockText"])       ? "Access to the following site has been denied:" : $usrIni["blockText"]);
$bpFlag     = (!isset($usrIni["flagText"])        ? "This is primarily due to being flagged as:" : $usrIni["flagText"]);
$bpNotice   = (!isset($usrIni["wlNotice"])        ? "If you have an ongoing use for this website, please " : $usrIni["wlNotice"]);
$bpNEmail   = (!isset($usrIni["wlEmail"])         ? "<a href='mailto:$adminEmail?subject=Site Blocked: $domainName'>ask to have it whitelisted</a>." : $usrIni["wlEmail"]);
$bpNText    = (!isset($usrIni["wlNoEmail"])       ? "ask the owner of the Pi-hole in your network to have it whitelisted." : $usrIni["wlNoEmail"]);
$bpSafe     = (!isset($usrIni["safeText"])        ? "Back to safety" : $usrIni["safeText"]);
$bpMore     = (!isset($usrIni["moreText"])        ? "More Info" : $usrIni["moreText"]);
$bpLess     = (!isset($usrIni["lessText"])        ? "Less Info" : $usrIni["lessText"]);
$bpQFound   = (isset($usrIni["siteFound"])        ? $usrIni["siteFound"] : false); // Text needs variables declared later
$bpWInput   = (!isset($usrIni["whiteInput"])      ? "Pi-hole Password" : $usrIni["whiteInput"]);
$bpWhite    = (!isset($usrIni["whiteText"])       ? "Whitelist" : $usrIni["whiteText"]);
$bpFooter   = (!isset($usrIni["footerText"])      ? "Generated %date% by %phbp%" : $usrIni["footerText"]);
$bpUpdate   = (!isset($usrIni["updateText"])      ? "(Update Available)" : $usrIni["updateText"]);
$bpExec     = (!isset($usrIni["execText"])        ? "Execution time:" : $usrIni["execText"]);

$bpSus      = (!isset($usrIni["suspiciousText"])  ? "Suspicious" : $usrIni["suspiciousText"]);
$bpAds      = (!isset($usrIni["advertisingText"]) ? "Advertising" : $usrIni["advertisingText"]);
$bpTrc      = (!isset($usrIni["trackingText"])    ? "Tracking & Telemetry" : $usrIni["trackingText"]);
$bpMal      = (!isset($usrIni["maliciousText"])   ? "Malicious" : $usrIni["maliciousText"]);
$bpBlk      = (!isset($usrIni["blacklistText"])   ? "Manually Blacklisted" : $usrIni["blacklistText"]);
$bpFlu      = (!isset($usrIni["flushNotice"])     ? "This site is not blocked.<br/>Please flush your DNS cache and/or restart your browser." : $usrIni["flushNotice"]);
$bpWSuccess = (!isset($usrIni["wlSuccess"])       ? "Success! You may have to flush your DNS cache" : $usrIni["wlSuccess"]);
$bpWExcept  = (!isset($usrIni["wlException"])     ? "Unable to load jQuery. Is Javascript blocked?" : $usrIni["wlException"]);

// Handled configured variables
if (empty($allowWhitel))
  $allowWhitel  = (in_array($usrIni["allowWhitelist"], array('true','TRUE','yes','YES','1')) ? true : false);
if (empty($checkUpdates))
  $checkUpdates = (in_array($usrIni["checkUpdates"], array('false','FALSE','no','NO','0')) ? false : true);
if (!empty($exeTime))
  $exeTime      = (in_array($usrIni["exeTime"], array('true','TRUE','yes','YES','1')) ? true : false);

// Config error checking
if (filter_var($iniUrl, FILTER_VALIDATE_URL) === false) die ("[ERROR]: User config variable <i>classFile</i> does not appear to be a URL: '$iniUrl'");
if (!is_numeric($iniUpdTime)) die ("[ERROR]: User config variable <i>classUpdateTime</i> is not a number: '$iniUpdTime'");
if (!empty($adminEmail) && filter_var($adminEmail, FILTER_VALIDATE_EMAIL) === false) die ("[ERROR]: User config variable <i>adminEmail</i> is not valid: '$adminEmail'");

// Retrieve configured notableFlags
$customGeneric     = preg_replace("$strip", "", $usrIni["blocklists"]["suspicious"]);
$customAdvertising = preg_replace("$strip", "", $usrIni["blocklists"]["advertising"]);
$customTracking    = preg_replace("$strip", "", $usrIni["blocklists"]["tracking"]);
$customMalicious   = preg_replace("$strip", "", $usrIni["blocklists"]["malicious"]);

// Retrieve remote classifications file
$iniFile = basename("$iniUrl");
cache_ini($iniUrl, $iniFile, $iniUpdTime);
$ini = parse_ini_file("$iniFile", true);

$latestPubVersion = $ini["version"];
$generic      = preg_replace("$strip", "", $ini["blocklist"]["generic"]);
$advertising  = preg_replace("$strip", "", $ini["blocklist"]["advertising"]);
$tracking     = preg_replace("$strip", "", $ini["blocklist"]["tracking"]);
$malicious    = preg_replace("$strip", "", $ini["blocklist"]["malicious"]);

// Merge configured notableFlags with default notableFlags
if (!empty($customGeneric))     $generic = array_merge($generic, $customGeneric);
if (!empty($customAdvertising)) $advertising = array_merge($advertising, $customAdvertising);
if (!empty($customTracking))    $tracking = array_merge($tracking, $customTracking);
if (!empty($customMalicious))   $malicious = array_merge($malicious, $customMalicious);

// Unset large arrays
if (!$debug) $usrIni = null;
$ini = null;
$customGeneric = null;
$customAdvertising = null;
$customTracking = null;
$customMalicious = null;

// Get all URLs starting with "http" or "www" from $adlist indexed numerically
$adlistUrls = array_values(preg_grep("/(^http)|(^www)/i", file($adlist, FILE_IGNORE_NEW_LINES)));
$adlistUrlCount = count($adlistUrls);
if ($adlistUrlCount == 0) die("[ERROR]: There was an issue parsing adlist '$adlist': Reading the file returned no results");

// Strip any combo of HTTP, HTTPS and WWW
$adlistUrlMatch = preg_replace("/https?\:\/\/(www.)?/i", "", $adlistUrls);

// Exact search, returning a numerically sorted array of matching .domains
// Will contain "list" if manually blacklisted
try {
  $adQuery = preg_grep("/(\.domains|blacklist\.txt).*\([1-9]/", file("http://pi.hole/admin/scripts/pi-hole/php/queryads.php?domain=$domainName&exact"));
} catch (Exception $e) {
  die("[ERROR]: Exception while retrieving results from Pi-hole API: ".$e->getMessage());
}
if (!empty($adQuery)) {
  $adQuery = preg_replace("/(data: ::: \/etc\/pihole\/.....)|(\.(.*)\s)/i", "", $adQuery);
  sort($adQuery, SORT_NUMERIC);

  // Return -1 if manually blacklisted, otherwise count total matches
  $featuredTotal = ($adQuery[0] == "list" ? "-1" : count($adQuery));
} else {
  $featuredTotal = 0;
}

// Error correction (EG: If gravity has been updated and adlists.list has been removed)
if ($featuredTotal > $adlistUrlCount) {
  die("[ERROR]: The # of blocklists that site was featured in, was larger than the total number of lists in '$adlist'");
}

// Define $notableFlag
if ($featuredTotal == "-1") {
  $notableFlag = $bpBlk;
} elseif ($featuredTotal == "0" && !$landPage) {
  $notableFlag = "-1";
  $notice = "This site is not blocked, but <i>landPage</i> is not configured.<br/>Did you mean to go to <a href='http://pi.hole'>Pi-hole Admin Console</a>?<br/>";
} elseif ($featuredTotal == "0" && !$selfDomain) {
  $notableFlag = "-1";
  $notice = "This site is not blocked, but <i>selfDomain</i> is not configured.<br/>Did you mean to go to <a href='http://pi.hole'>Pi-hole Admin Console</a>?<br/>";
} elseif ($featuredTotal == "0") {
  $notableFlag = "-1";
  $notice = $bpFlu;
} elseif ($featuredTotal >= "1") {
  $in = null;
  foreach ($adQuery as $num) {
    // Create a string of flags for domainName
    if(in_array(strtolower($adlistUrlMatch[$num]), array_map('strtolower', $generic))) $in .= "sus ";
    if(in_array(strtolower($adlistUrlMatch[$num]), array_map('strtolower', $advertising))) $in .= "ads ";
    if(in_array(strtolower($adlistUrlMatch[$num]), array_map('strtolower', $tracking))) $in .= "trc ";
    if(in_array(strtolower($adlistUrlMatch[$num]), array_map('strtolower', $malicious))) $in .= "mal ";
    
    // Return value of worst flag to user (EG: Malicious more notable than Suspicious)
    if (substr_count($in, "sus")) $notableFlag = $bpSus;
    if (substr_count($in, "ads")) $notableFlag = $bpAds;
    if (substr_count($in, "trc")) $notableFlag = $bpTrc;
    if (substr_count($in, "mal")) $notableFlag = $bpMal;
  }
}

// Do not show primary flag if we are unable to find one
if(empty($notableFlag)) $notableFlag = "-1";

// Email address Replacements
if (!$adminEmail) $bpNEmail = $bpNText;
$bpNEmail = ($adminEmail ? str_replace("%email%", $adminEmail, $bpNEmail) : $bpNEmail);
$bpNEmail = str_replace("%sitename%", $domainName, $bpNEmail);
$bpNEmail = str_replace("%flag%", $notableFlag, $bpNEmail);
$bpNEmail = str_replace("%adCount%", $featuredTotal, $bpNEmail);
$bpNEmail = str_replace("%totalLists%", $adlistUrlCount, $bpNEmail);
$bpNotice = "$bpNotice$bpNEmail";

// Site found Replacements
if (!empty($bpQFound)) {
  $bpQFound = str_replace("%adCount%", $featuredTotal, $bpQFound);
  $bpQFound = str_replace("%totalLists%", $adlistUrlCount, $bpQFound);
}
$bpQFound = (empty($bpQFound) ? "This site is found in $featuredTotal of $adlistUrlCount lists:" : $bpQFound);

// Footer Date
$bpFooter = str_replace("%date%", date("D g:i A"), $bpFooter);
$bpFooter = str_replace("%phbp%", "<a href='https://github.com/WaLLy3K/Pi-hole-Block-Page'>Pi-hole Block Page</a>", $bpFooter);

// Print debugging output
if ($debug) {
  if (!$allowWhitel) $allowWhitel = "0"; if (!$webPassword) $webPassword = "0"; if (!$checkUpdates) $checkUpdates = "0"; if (!$exeTime) $exeTime = "0";
  os_cpustats(); 
  os_ramload();
  ph_parseapi();
  echo "<md>```\n</md>";
  $bp = "<md>&#8250; </md>";
  echo "${bp}Server/OS: ".os_release()."\n";
  echo "${bp}OS Uptime: ".os_uptime()."\n";
  echo "${bp}Task Load: ".os_taskload()." ($cpuProcActive/$cpuProcTotal active)\n";
  echo "${bp}CPU usage: $cpuPerc% (Temp: ".os_cputemp().", Cores: ".os_cpu_num().")\n";
  echo "${bp}RAM usage: $ramPerc% (".convert($ramUsed)." of ".convert($ramTotal)." used)\n";
  echo "${bp}PHP usage: ".convert(memory_get_peak_usage("true"))." (".ini_get("memory_limit")." limit)\n";
  echo "${bp}P-H usage: $domains_being_blocked domains ($ads_blocked_today/$dns_queries_today blocked)\n";
  echo "${bp}PHBP info: v$phbpVersion".check_update()." (RCI: v$latestPubVersion, ".substr(fgets(fopen($iniFile, 'r')), 2, -1).")\n";
  echo "${bp}PHBP conf: Passwd: $webPassword, WL: $allowWhitel, Updates: $checkUpdates\n";
  echo "${bp}".ucfirst(basename($adlist)).": $adlistUrlCount blocklists\n";
  echo "${bp}Queryads.php: ".count($adQuery)." results (".implode(',', $adQuery).")\n";
  echo "${bp}".ucfirst($domainName).": $featuredTotal lists ($notableFlag)\n";
  echo "${bp}Generated in: ".load_time()."\n";
  
  if ($debugConf) {
    echo "\nUser Config ";
    if (!is_array($usrIni["blocklists"])) die("\n[ERROR]: There appears to be a syntax issue with phbp.ini");
    if ($adminEmail) $usrIni["adminEmail"] = "user@redacted.com";
    if ($selfDomain !== "none") $usrIni["selfDomain"] = "redacted.com";
    // Do not return 'default' config entries
    foreach($usrIni["blocklists"]["suspicious"] as $k => $v) if(strpos($v, "firebog")) unset($usrIni["blocklists"]["suspicious"]["$k"]);
    foreach($usrIni["blocklists"]["advertising"] as $k => $v) if(strpos($v, "firebog")) unset($usrIni["blocklists"]["advertising"]["$k"]);
    foreach($usrIni["blocklists"]["tracking"] as $k => $v) if(strpos($v, "firebog")) unset($usrIni["blocklists"]["tracking"]["$k"]);
    foreach($usrIni["blocklists"]["malicious"] as $k => $v) if(strpos($v, "firebog")) unset($usrIni["blocklists"]["malicious"]["$k"]);
    $usrIni = array_filter_empty($usrIni);
    print_r($usrIni);
  }
  die("<md>```</md>");
}
?>

<!DOCTYPE html><head>
  <meta charset='UTF-8'/>
  <title><?=$bpTitle ?></title>
  <link rel='stylesheet' href='<?=$bpCss ?>'/>
  <link rel='shortcut icon' href='<?=$bpIcon ?>'/>
  <?=$viewPort ?>
  <meta name='robots' content='noindex,nofollow'/>
  <?php if ($featuredTotal > "0") echo '<script src="http://pi.hole/admin/scripts/vendor/jquery.min.js"></script>'; ?>
  <script>
    function tgVis(id) {
      var e = document.getElementById('querylist');
      if(e.style.display == 'block') {
        e.style.display = 'none';
        document.getElementById("info").innerHTML = "<?=$bpMore ?>";
      } else {
        e.style.display = 'block';
        document.getElementById("info").innerHTML = "<?=$bpLess ?>";
      }
    }
  </script>
  <style>
    header h1:before, header h1:after { background-image: url('<?=$bpLogo ?>'); }
  </style>
  <noscript><style>
    #querylist { display: block; }
    .buttons { display: none; }
  </style></noscript>
</head><body><header>
  <h1><a href='/'><?=$bpHeading ?></a></h1>
</header><main>
  <div class="block">
    <?=$bpBlock ?>
    <span class="msg"><?=$domainName ?></span>
  </div>
  <?php if ($notableFlag !== "-1") { ?>
  <div class="flag">
    <?=$bpFlag ?>
    <span class='msg'><?=$notableFlag ?></span>
  </div>
  <?php } ?>
  <?php if (!isset($notice)) { ?>
  <div class="notice">
     <?=$bpNotice ?>
  </div>
  <div class='buttons'>
    <a id='back' href='javascript:history.back()'><?=$bpSafe ?></a>
    <?php if ($featuredTotal > "0") echo "<a id='info' onclick='tgVis(\"querylist\");'>$bpMore</a>"; ?>
  </div> 
  <?php } ?>
  <div id='querylist'><?=$bpQFound ?>
    <pre id='output'><?php foreach ($adQuery as $num) { echo "<span>[$num]:</span><a href='$adlistUrls[$num]'>$adlistUrls[$num]</a>\n"; } ?></pre>
    <?php if ($allowWhitel && $webPassword) { ?>
    <form class='buttons'>
      <input id='domain' value='<?=$domainName ?>' disabled>
      <input type='password' id='pw' name='pw' placeholder='<?=$bpWInput ?>'/>
      <button id='whitelist' type='button'><?=$bpWhite ?></button>
     </form>
     <pre id='notification' hidden></pre>
     <?php } ?>
  </div>
  <?php if (isset($notice)) echo "<pre id='notification'>$notice</pre>"; ?>
</main>
<footer><?=$bpFooter ?><?=check_update(); if($exeTime) printf("<br/>$bpExec %.2fs\n", microtime(true)-$_SERVER["REQUEST_TIME_FLOAT"]); ?></footer>

<?php // Cr: http://bit.ly/2irWj8d ?>
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
          $( "#notification" ).html("<?php echo $bpWSuccess; ?>");
        } else {
          $( "#notification" ).html(""+response+"");
        }

      },
      error: function(jqXHR, exception) {
        $( "#notification" ).removeAttr( "hidden" );
        // Assume javascript is enabled, but external files are being blocked (EG: Noscript/Scriptsafe)
        $( "#notification" ).html("<?php echo $bpWExcept; ?>");
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
<?php
// Confirm that a configured domain name appears to be valid (Will not authenticate internationalised domain names)
function is_valid_domain_name($domain_name) { // Cr: http://bit.ly/2gnunOo
  return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name)
    && preg_match("/^.{1,253}$/", $domain_name)
    && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name));
}

// Installations such as DietPi remove the lighttpd.conf configuration provided by Pi-hole
// Provide a response header for debugging using curl -I, if not already configured to do so
function set_xpihole_header() {
  global $lighttpdConf;
  if (!preg_match('/^\s+setenv.add-response-header/', $lighttpdConf[61])) {
    header("X-Pi-hole: A black hole for Internet advertisements.");
    $lighttpdConf = null;
	}
}

// Retrieve remote INI
function cache_ini($url, $fileName, $time) {
  $fileExists = (is_file("$fileName") ? true : false);
  
  // Check if local classifications file was recently checked for an update
  if ($fileExists)
    if (time() - filemtime("$fileName") < $time) return;
  
  // Retrieve remote class file headers
  $urlHeader = @get_headers($url, 1);
  $urlStatus = substr($urlHeader[0], 9, 3);
  $urlEtag = (isset($urlHeader["ETag"]) ? $urlHeader["ETag"] : false);
  $urlLastmod = (isset($urlHeader["Last-Modified"]) ? strtotime($urlHeader["Last-Modified"]) : false);
  $urlHeader = null;
  
  // Parse remote URL for error handling
  $hostUrl = parse_url($url);
  $hostName = $hostUrl["scheme"]."://".$hostUrl["host"];
  
  // Error handling
  if (empty($urlStatus))
    die("[ERROR]: Unable to retrieve '$fileName'. The server '$hostName' was not found");
  if (isset($urlStatus) && !in_array($urlStatus, array("200","301","302")))
    die("[ERROR]: Unable to retrieve '$fileName'. The server '$hostName' returned the error code: $urlStatus");
  if (!$urlEtag && !$urlLastmod)
    die("[ERROR]: Unable to store '$fileName'. The server '$hostName' does not provide either Last-Modified or ETag headers");
  
  // Hash ETag or Last-Modified with $url
  $urlVersion = ($urlEtag ? hash('crc32', '$urlEtag.$url') : hash('crc32', '$urlLastmod.$url'));
  
  if ($fileExists) {
    // Read first line to check local config $urlVersion
    $localVersion = substr(fgets(fopen($fileName, 'r')), 2, -1);
    if (empty($localVersion)) die("[ERROR]: Unable to read from '$fileName'");
    if ($localVersion == $urlVersion) touch($fileName); // Update file mod time
  }
  
  // Retrieve and store remote class file
  $urlFile = file("$url");
  array_unshift($urlFile, "; $urlVersion\n"); // Place $urlVersion at top of local user config for version control
  file_put_contents("$fileName", $urlFile);
  if (!is_file("$fileName")) {
    $serverUser = ($_SERVER['USER'] ? $_SERVER['USER'] : "www-data");
    die("[ERROR]: '$fileName' was unable to be written to folder: '".getcwd()."'<br/>Please chown your default HTML folder from Terminal so that it can be written to: <b>sudo chown \"$serverUser\" ".getcwd()."</b>");
  }
  $urlFile = null;
}

// Compare local version with remote classfile
function check_update() {
  global $checkUpdates, $phbpVersion, $latestPubVersion, $bpUpdate;
  if ($checkUpdates && version_compare($latestPubVersion, $phbpVersion, '>')) {
    return " $bpUpdate";
  }
}

// Find partial case insensitive string within array
function array_stripos($needle, $array) {
   foreach ($array as $key => $value) {
      if (stripos($value, $needle) !== false) { return "$key $value"; break; }
   }
}

// Provide load times
function load_time($dec = "2") {
  return round(microtime(true)-$_SERVER["REQUEST_TIME_FLOAT"], $dec)."s";
}

// Convert bytes to human readable format
function convert($size, $notation = "1024", $dec = "0") { // Cr: http://bit.ly/2iySnTa
    $unit=array('b','KB','MB','GB','TB','PB');
    return @round($size/pow($notation,($i=floor(log($size,$notation)))), $dec).' '.$unit[$i];
}

// Recursively remove empty entries within array
function array_filter_empty($arr) { // Cr: http://bit.ly/2jf0VMi
  foreach ($arr as $k => $v) { 
    if (is_array($v)) $arr[$k] = array_filter_empty($arr[$k]);
    if (empty($arr[$k])) unset($arr[$k]);
  }
  return $arr;
}

// Get server distribution
function os_release() {
  $release = glob("/etc/*-release");
  if (!$release) return PHP_OS." ".php_uname("r")." ".php_uname("m");
  $release = preg_replace('/(.*=)|(\")/', "", file($release[0], FILE_IGNORE_NEW_LINES));
  return $release[0];
}

// Get human readable server uptime
function os_uptime() { // Cr: http://bit.ly/2k3GqRR
  if (php_uname("s") !== "Linux") return "-1";
  $dtF = new DateTime('@0');
  $dtT = new DateTime("@".round(file_get_contents("/proc/uptime")));
  $d = $dtF->diff($dtT)->format('%a'); $da = null;
  if ($d !== "0") $da = ($d == 1 ? "1 day, " : "$d days, ");
  $h = sprintf('%02d', $dtF->diff($dtT)->format('%h'));
  $i = sprintf('%02d', $dtF->diff($dtT)->format('%i'));
  $s = sprintf('%02d', $dtF->diff($dtT)->format('%s'));
  return "$da$h:$i:$s";
}

// Return task load average for debugging
function os_taskload() {
	$load = sys_getloadavg();
	return "$load[0], $load[1], $load[2]";
}

// Provide CPU usage %, active and total tasks
function os_cpustats() {
  global $cpuPerc, $cpuProcActive, $cpuProcTotal;
  if (php_uname("s") !== "Linux") return "-1";
  exec("ps -eo rss,pcpu --no-headers", $ps);
  if ($ps) $ps = preg_replace("/.*\s/", "", preg_grep("/\s0\s/", $ps, PREG_GREP_INVERT));
  $cpuPerc = (!empty($ps) ? round(array_sum($ps)) : "err");
  $cpuProcActive = (!empty($ps) ? count(preg_grep("/^0/", $ps, PREG_GREP_INVERT)) : "?");
  $cpuProcTotal = (!empty($ps) ? count($ps) : "?");
}

// Attempt to return CPU temp
function os_cputemp() {
  if (is_file("/sys/class/thermal/thermal_zone0/temp")) {
    $temp = file_get_contents("/sys/class/thermal/thermal_zone0/temp");
  } elseif (is_file("/sys/class/hwmon/hwmon0/temp1_input")) {
    $temp = file_get_contents("/sys/class/hwmon/hwmon0/temp1_input");
  } else {
    return "N/A";
  }
  return substr($temp, 0, 2)."c";
}

// Return CPU count for debugging
function os_cpu_num() { // Cr: http://bit.ly/2j7nPVb
  $numCpus = 1;
  if (is_file('/proc/cpuinfo')) {
    $cpuinfo = file_get_contents('/proc/cpuinfo');
    preg_match_all('/^processor/m', $cpuinfo, $matches);
    $numCpus = count($matches[0]);
  } elseif ('WIN' == strtoupper(substr(PHP_OS, 0, 3))) {
    return "-1";
  } else {
    $process = @popen('sysctl -a', 'rb');
    if (false !== $process) {
      $output = stream_get_contents($process);
      preg_match('/hw.ncpu: (\d+)/', $output, $matches);
      if ($matches) $numCpus = intval($matches[1][0]);
      pclose($process);
    }
  }
  return $numCpus;
}

// Provide RAM usage for debugging
function os_ramload() {
  global $ramPerc, $ramUsed, $ramTotal;
  $meminfo = preg_replace("/[^0-9]+/", "", file("/proc/meminfo"));
  if (!$meminfo) return "-1";
  $ramPerc = round(($meminfo[0]-$meminfo[1]-$meminfo[3]-$meminfo[4])*100/$meminfo[0]);
  $ramUsed = ($meminfo[0]-$meminfo[1]-$meminfo[3]-$meminfo[4])*1024;
  $ramTotal = $meminfo[0]*1024;
}

// Parse results of Pi-hole API
function ph_parseapi() {
  global $domains_being_blocked, $ads_blocked_today, $dns_queries_today;
  $api = json_decode(file_get_contents("http://pi.hole/admin/api.php"));
  if(!$api) die("[ERROR]: Unable to retrieve results from Pi-hole primary API");
  $domains_being_blocked = $api->{"domains_being_blocked"};
  $ads_blocked_today = $api->{"ads_blocked_today"};
  $dns_queries_today = $api->{"dns_queries_today"};
}
?>
