<?php
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
$directoryFinder = explode("/", $_SERVER["SCRIPT_FILENAME"]);
$dir = "";
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

require("../../init.php");

if (!isset($chat_settings)) {
	  $result2 = mysql_query("SELECT * FROM `chat_settings`");
	  while($row = mysql_fetch_array($result2)) {
		  $chat_settings[$row[0]] = $row[1];
	  }
}

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

?>jQuery(document).ready(function () {
jQuery.ajaxSetup({cache: false});

jQuery(".livechat").html("<?php
$timestamp = time() - 600;
$result = mysql_query("SELECT * FROM `tbladminlog` WHERE `logouttime`='0000-00-00 00:00:00' AND `online`='1'");
$x = 0;
while($row = mysql_fetch_array($result)) {
	if (strtotime($row["lastvisit"]) >= $timestamp) {
		$usernames[$x] = $row["adminusername"];	
		$x++;
	}
}

for ($x = 0; $x < count($usernames); $x++) {
	if ($x != 0) {
		$query2 .= " OR";	
	}
	$query2 .= " `username`='".$usernames[$x]."'";
}

if ($query2 != "") {
	$query2 = " WHERE".$query2;	
}

$x = 0;
//echo "SELECT * FROM `tbladmins`".$query2;
$departments = array();
if ($query2 != "") {
	$result = mysql_query("SELECT * FROM `tbladmins`".$query2);
	while($row = mysql_fetch_array($result)) {
		$d = explode(",", $row["supportdepts"]);
		for ($y = 0; $y < count($d); $y++) {
			if ($d[$y] != "" && in_array($d[$y], $departments) != 1) {
				$departments[$x] = $d[$y];
				$x++;
			}
		}
	}
	
	//print_r($departments);
	$departDefaultSet = false;
	$result = mysql_query("SELECT * FROM `tblticketdepartments` WHERE allowlive=1");
	while($row = mysql_fetch_array($result)) {
		if (in_array($row["id"], $departments) == 1) {
			$departDefaultSet = true;
		}
	}
}
  
if (!$departDefaultSet) {
	echo addslashes($chat_settings["offlineDisplay"]);
} else {
	echo addslashes($chat_settings["onlineDisplay"]);
}



?>");

jQuery("body").append("<div id='receiver' style='width: 0px; height: 0px; visibility:hidden; overflow:hidden;'></div>");


jQuery(".livechat").click(function () {
open_win();
});

codeInject();

});

function codeInject() {
	jQuery("#receiver").load('<?= $url; ?>/includes/chat/jsCodeInjecter.php?url='+escape(window.location), function(responseText, textStatus, XMLHttpRequest) {
		t=setTimeout("codeInject();",10000);
	});
}

function open_win() {
	window.open("<?= $url; ?>/start_session.php","_blank","toolbar=no, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=yes, copyhistory=no, width=625, height=475");
}