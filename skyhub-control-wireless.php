<?php
// This is the server-side script.

// Set the content type.
header('Content-Type: text/plain');

// Send the data back.
//echo "This is the returned text.";

$xmlstr = <<<XML
<?xml version='1.0' standalone='yes'?>
<movies>
 <movie>
  <title>PHP: Behind the Parser</title>
  <characters>
   <character>
    <name>Ms. Coder</name>
    <actor>Onlivia Actora</actor>
   </character>
   <character>
    <name>Mr. Coder</name>
    <actor>El Act&#211;r</actor>
   </character>
  </characters>
  <plot>
   So, this language. It's like, a programming language. Or is it a
   scripting language? All is revealed in this thrilling horror spoof
   of a documentary.
  </plot>
  <great-lines>
   <line>PHP solves all my web problems</line>
  </great-lines>
  <rating type="thumbs">7</rating>
  <rating type="stars">5</rating>
 </movie>
</movies>
XML;

define("URL_WIRELESS_SETTINGS","http://192.168.1.204/sky_wireless_settings.html");
define("INVALID_AUTHORISATION_LENGTH",200);
define("URL_WIRELESS_SETTINGS_POST","http://192.168.1.204/sky_wireless_settings.cgi");
define("SLEEP_LENGTH",10);
define("POST_DATA_TEMPLATE","wlSsid=wifiname&wlChannel=0&wlTxMode=0&wifi_enabled=0&wifi_hide=0&secType=5&auth=1&wlKeyBit=1&wlKeyText=&wlKeys=&wlWpaPsk=&wlEncrtype=1&wlRadiusIPAddr=0.0.0.0&wlRadiusPort=1812&wlRadiusKey=&wlSyncSettings=0&wlBand=2&sessionKey=&wlAuthMode=psk2&wlAuth=0&wlEnbl=&wl_wsc_mode=disabled&wlAPIsolation=0&wlWep=disabled&wlWpa=aes&wlPreauth=1&wlNBwCap=2&wlHide=0&wlSecMode=5&wlKey1=&wlRadiusServerIP=0.0.0.0&wlSyncNvram=1");

define("USERNAME","sky");  //enter username here
define("PASSWORD","sky"); //enter password here
define("CURL_USERPWD",USERNAME . ":" . PASSWORD);

define("SESSION_ID_SEARCH_1A","var sessionKey='");
define("SESSION_ID_SEARCH_1B","'");
define("SESSION_ID_SEARCH_2","&sessionKey=");
define("WIRELESS_NBL_SRCH","&wlEnbl=");
define("SESSION_ID_SEARCH_LEN_1A", 16);
define("SESSION_ID_SEARCH_LEN_2", 12);
define("WIRELESS_NBL_SRCH_LEN", 8);

 
$sessionID = Get_SessionID();
echo curl_download_post_wireless(URL_WIRELESS_SETTINGS_POST, $sessionID, false);
sleep(SLEEP_LENGTH);
$sessionID = Get_SessionID();
echo curl_download_post_wireless(URL_WIRELESS_SETTINGS_POST, $sessionID,true);

function Get_SessionID(){
	
	$page = Get_Wireless_Page();

	$pos1 = strpos($page,SESSION_ID_SEARCH_1A)+SESSION_ID_SEARCH_LEN_1A;
	$pos2 = strpos($page,SESSION_ID_SEARCH_1B,$pos1);
	$sessionID = substr($page,$pos1,$pos2-$pos1);
	echo "----------\nstart:$pos1 , end:$pos2\n\n$sessionID\n-----------------\n";
	
	return $sessionID;
}

function Get_Wireless_Page(){	
	$page = curl_download_wireless_page(URL_WIRELESS_SETTINGS);
	echo $page;
	//sometimes need to get the page second time
	if(strlen($page) < INVALID_AUTHORISATION_LENGTH){
		$page = curl_download_wireless_page(URL_WIRELESS_SETTINGS);
	}
	
	return $page;
}

function curl_download_wireless_page($Url){
	// is cURL installed yet?
    if (!function_exists('curl_init')){
        die('Sorry cURL is not installed!');
    }
 
    // OK cool - then let's create a new cURL resource handle
    $ch = curl_init();
 
    // Now set some options (most are optional)
 
    // Set URL to download
    curl_setopt($ch, CURLOPT_URL, $Url);
	
	//ignore ca cert for https
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
 
    // Set a referer
    //curl_setopt($ch, CURLOPT_REFERER, "http://www.example.org/yay.htm");
 
    // User agent
    //curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
 
    // Include header in result? (0 = yes, 1 = no)
    curl_setopt($ch, CURLOPT_HEADER, 0);	
		
	// Should cURL return or print out the data? (true = return, false = print)
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
    // Timeout in seconds
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	
    //set username / password as "[username]:[password]" 
	curl_setopt($ch,CURLOPT_USERPWD, CURL_USERPWD);
 
    // Download the given URL, and return output
    $output = curl_exec($ch);
 
    // Close the cURL resource, and free system resources
    curl_close($ch);
 
    return $output;
}
	
//post if wireless is to be enabled
function curl_download_post_wireless($Url, $sessionID, $wirelessEnable){
 
	//orginal post string
	//"wlSsid=Vincent_Van_Gogh&wlChannel=0&wlTxMode=0&wifi_enabled=0&wifi_hide=0&secType=5&auth=1&wlKeyBit=1&wlKeyText=&wlKeys=&wlWpaPsk=f3L3LHEee8e3uMK&wlEncrtype=1&wlRadiusIPAddr=0.0.0.0&wlRadiusPort=1812&wlRadiusKey=&wlSyncSettings=0&wlBand=2&sessionKey=754510529&wlAuthMode=psk2&wlAuth=0&wlEnbl=1&wl_wsc_mode=disabled&wlAPIsolation=0&wlWep=disabled&wlWpa=aes&wlPreauth=1&wlNBwCap=2&wlHide=0&wlSecMode=5&wlKey1=&wlRadiusServerIP=0.0.0.0&wlSyncNvram=1";

	$postData = POST_DATA_TEMPLATE;
	$idx = strpos($postData,SESSION_ID_SEARCH_2) + SESSION_ID_SEARCH_LEN_2;
	//echo "\nidx:$idx\n";
	$postData = substr_replace($postData,$sessionID,$idx,0);
	
	$idx = strpos($postData,WIRELESS_NBL_SRCH) + WIRELESS_NBL_SRCH_LEN;
	//echo "\nidx:$idx\n";
	$postData = substr_replace($postData,($wirelessEnable?"1":"0"),$idx,0);
	
	echo $postData;
	
	
    // is cURL installed yet?
    if (!function_exists('curl_init')){
        die('Sorry cURL is not installed!');
    }
 
    // OK cool - then let's create a new cURL resource handle
    $ch = curl_init();
 
    // Now set some options (most are optional)
 
    // Set URL to download
    curl_setopt($ch, CURLOPT_URL, $Url);
	
	//ignore ca cert for https
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,
            $postData);
 
    // Set a referer
    //curl_setopt($ch, CURLOPT_REFERER, "http://www.example.org/yay.htm");
 
    // User agent
    //curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
	
	//set useragent
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36");
 
    // Include header in result? (0 = yes, 1 = no)
    curl_setopt($ch, CURLOPT_HEADER, 0);	
		
	// Should cURL return or print out the data? (true = return, false = print)
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
    // Timeout in seconds
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	
	//set username / password as "[username]:[password]" 
	curl_setopt($ch,CURLOPT_USERPWD,CURL_USERPWD);
 
    // Download the given URL, and return output
    $output = curl_exec($ch);
 
    // Close the cURL resource, and free system resources
    curl_close($ch);
 
	//debug
	//echo $output;
 
    return $output;
}
?>