<?
/*
    WHMCS Addon Live Support - Provides a way for you to instantly communicate
    with your customers.
    Copyright (C) 2010-2012 WHMCS Addon

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
@error_reporting(0);
@ini_set("register_globals", "off");
if ($_GET["module"] == "live_chat") {
	exit();
}
// Find WHMCS Directory
//    Set $pathPart to the folder to exclude from.
$directoryFinder = explode("/", $_SERVER["SCRIPT_FILENAME"]);
foreach($directoryFinder as $pathPart) {
	if ($pathPart != "") {
		if ($pathPart != "includes") {
			$dir .= "/".$pathPart;
		} else {
			$dir .= "/";
			break;
		}
	}
}

require($dir."/dbconnect.php");

# Get Variables from storage (retrieve from wherever it's stored - DB, file, etc...)
if (!isset($chat_settings)) {
	  $result2 = mysql_query("SELECT * FROM `chat_settings`");
	  while($row = mysql_fetch_array($result2)) {
		  $chat_settings[$row[0]] = $row[1];
	  }
}


session_start();
$result = mysql_query("SELECT * FROM `tblconfiguration`");
while($row = mysql_fetch_array($result)) {
	
	if ($row[0] == "SystemSSLURL" && isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
		if ($row[1] != "") {
			$url = $row[1];
			$ssl = true;
		}
	}
	if ($row[0] == "SystemURL") {
		if ($url == "") {
			$url = $row[1];	
			$ssl = false;
		}
	}
}

if (!$ssl && strstr($url, "http://".$_SERVER["SERVER_NAME"]) == false) {
	if (strstr($_SERVER["SERVER_NAME"], "www.") != false) {
		//$url = 	"http://".$_SERVER["SERVER_NAME"].str_replace("http://".$_SERVER["SERVER_NAME"], "", $url, 1);
		$urlReplaced = substr_replace($url,"",0,(strlen("http://".$_SERVER["SERVER_NAME"])-4));
		$url = "http://".$_SERVER["SERVER_NAME"].$urlReplaced;
	} else {
		//$url = 	"http://".$_SERVER["SERVER_NAME"].str_replace("http://www.".$_SERVER["SERVER_NAME"], "", $url);
		$urlReplaced = substr_replace($url,"",0,(strlen("http://www.".$_SERVER["SERVER_NAME"])));
		$url = "http://".$_SERVER["SERVER_NAME"].$urlReplaced;
	}
} elseif ($ssl && strstr($url, "https://".$_SERVER["SERVER_NAME"]) == false) {
	if (strstr($_SERVER["SERVER_NAME"], "www.") != false) {
		//$url = 	"https://".$_SERVER["SERVER_NAME"].str_replace("https://".$_SERVER["SERVER_NAME"], "", $url);
		$urlReplaced = substr_replace($url,"",0,(strlen("https://".$_SERVER["SERVER_NAME"])-4));
		$url = "https://".$_SERVER["SERVER_NAME"].$urlReplaced;
	} else {
		//$url = 	"https://".$_SERVER["SERVER_NAME"].str_replace("https://www.".$_SERVER["SERVER_NAME"], "", $url);
		$urlReplaced = substr_replace($url,"",0,(strlen("https://www.".$_SERVER["SERVER_NAME"])));
		$url = "https://".$_SERVER["SERVER_NAME"].$urlReplaced;
	}
}
$url = substr($url, 0, -1);

$result = mysql_query("SELECT * FROM `tbladminlog` WHERE `sessionid`='".$_COOKIE["PHPSESSID"]."' ORDER BY `id` DESC LIMIT 1");
while($row = mysql_fetch_array($result)) {
	echo "var onlineState = ".$row["online"].";\n";
}
?>var t;
var tActive;

var method = "new";

var firstload = 0;
var soundState;

jQuery(document).ready(function(){
	jQuery("body").append("<div id='activereceiver' style='width: 0px; height: 0px; visibility:hidden; overflow:hidden;'></div><div id='receiver' style='width: 0px; height: 0px; visibility:hidden; overflow:hidden;'></div><div id='liveupdate' style='background-color:white;background-image:url(\"images/online_bar.jpg\");background-position:left bottom;background-repeat:repeat-x;border-left:1px solid #999999;border-right:1px solid #999999;border-top:1px solid #999999;bottom:0;color:#000000;position:fixed;right:106px;'></div><div id='soundButton' style='background-color:white;background-image:url(\"images/online_bar.jpg\");background-repeat:repeat-x;border-top:1px solid #999999;bottom:0;color:#000000;height:18px;padding:2px;position:fixed;right:41px;border-right:1px solid #999999;'></div><div id='onlineButton' style='background-color:white;background-image:url(\"images/online_bar.jpg\");background-repeat:repeat-x;border-top:1px solid #999999;bottom:0;color:#000000;height:18px;padding:2px;position:fixed;right:0;'></div>");

	jQuery.ajaxSetup({cache: false});
	getUpdates();
	tActive=setTimeout("setActive();",60000);
    
    jQuery("#onlineButton").click(function () {
    	if (onlineState == 0) {
        	onlineState = 1;
            jQuery("#onlineButton").text("Online");
            setActive();
        } else if (onlineState == 1) {
        	onlineState = 0;
            jQuery("#onlineButton").text("Offline");
            setActive();
        }
    });
	
    if (getCookie("chat_soundSetting") != "0" && getCookie("chat_soundSetting") != "1") {
		soundState = 1;    
        setCookie('chat_soundSetting',1,365);
    } else {
    	soundState = getCookie("chat_soundSetting"); 
    }
    
    if (soundState == 0) {
            jQuery("#soundButton").text("Sound: Off");
    } else if (soundState == 1) {
            jQuery("#soundButton").text("Sound: On");
    }
    
    jQuery("#soundButton").click(function () {
    	if (soundState == 0) {
        	soundState = 1;
            jQuery("#soundButton").text("Sound: On");
        } else if (soundState == 1) {
        	soundState = 0;
            jQuery("#soundButton").text("Sound: Off");
        }
        setCookie('chat_soundSetting',soundState,365);
    });
    
    if (onlineState == 0) {
            jQuery("#onlineButton").text("Offline");
    } else if (onlineState == 1) {
            jQuery("#onlineButton").text("Online");
    }
    
    jQuery.ajax({ url:"<?= $url; ?>/includes/jscript/jquery.sound.js", dataType:"script", type:"GET"});
});

function getUpdates() {
	jQuery("#receiver").load('<?= $url; ?>/includes/chat/adminsessions.php?method='+method, function(responseText, textStatus, XMLHttpRequest) {
		if (firstload == 0 || jQuery("#receiver").html() != "" && jQuery("#receiver").text() != jQuery("#liveupdate").text()) {
			jQuery("#liveupdate").html(jQuery("#receiver").html());
            if (firstload == 1 && soundState == 1 && jQuery.trim(jQuery("#receiver").text()) != "No support requests are available at the moment.") {
            	jQuery.sound.play("<?= $url; ?>/includes/media/incoming.mp3", {timeout: 5000});
            }
            firstload = 1;
		}
		t=setTimeout("getUpdates();",1000);
	});
}

function setActive() {
	jQuery("#activereceiver").load('<?= $url; ?>/includes/chat/adminactive.php?online='+onlineState);
	tActive=setTimeout("setActive();",60000);
}

function open_win(session, secret) {
    if (secret != true) {
        window.open("<?= $url; ?>/chatwindow.php?session="+session,"_blank","status=no, toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=yes, copyhistory=no, width=531, height=522");
    } else {
        window.open("<?= $url; ?>/chatwindow.php?session="+session+"&secret=true","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=yes, copyhistory=no, width=531, height=522");
    }
}

function answerCall(session, secret) {
	if (session != "") {
    	open_win(session, secret);
    }
    getUpdates();
}

function ignoreCall(session) {
	if (session != "") {
    	jQuery("#receiver").load('<?= $url; ?>/includes/chat/adminfunction.php?action=ignore&session='+session);
    }
    getUpdates();
}

function blockUser(session) {
	if (session != "") {
    	jQuery("#receiver").load('<?= $url; ?>/includes/chat/adminfunction.php?action=block&session='+session);
    }
    getUpdates();
}


// Code from W3Schools:
function setCookie(c_name,value,expiredays)
{
var exdate=new Date();
exdate.setDate(exdate.getDate()+expiredays);
document.cookie=c_name+ "=" +escape(value)+
((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
}

function getCookie(c_name)
{
if (document.cookie.length>0)
  {
  c_start=document.cookie.indexOf(c_name + "=");
  if (c_start!=-1)
    {
    c_start=c_start + c_name.length+1;
    c_end=document.cookie.indexOf(";",c_start);
    if (c_end==-1) c_end=document.cookie.length;
    return unescape(document.cookie.substring(c_start,c_end));
    }
  }
return "";
}
// End W3Schools Reference