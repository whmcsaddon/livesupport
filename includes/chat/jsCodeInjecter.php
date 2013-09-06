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

require("../../init.php");
session_start();
if ($_SESSION["uid"] != "") {
	$uid = $_SESSION["uid"];
	$utype = 1;
} else {
	$uid = 0;
	$utype = 0;
}

/*if (isset($_SESSION["site_session"]) || isset($_COOKIE["site_session"])) {
	$result = mysql_query("SELECT * FROM `site_activitylogs` WHERE `session`='".$_SESSION["site_session"]."'");
	while($row = mysql_fetch_array($result)) {
		if ($uid != $row["uid"] || $_SERVER['REMOTE_ADDR'] != $row["ip"]) {
			$startNew = true;	
		}
	}
}*/
$aTime = time() - 3000;
$result = mysql_query("SELECT * FROM `site_activitylogs` WHERE `ip`='".$_SERVER['REMOTE_ADDR']."' AND `lastaccess`>='".$aTime."' ORDER BY `id` ASC");

//echo $_SESSION["monitor_session"];
if (mysql_num_rows($result) == 1) {

	while($row = mysql_fetch_array($result)) {
		$pageFinder = explode("|", $row["pages"]);
		$timeFinder = explode("|", $row["timestamps"]);
		if ($pageFinder[count($pageFinder)-1] == $_GET["url"]) {
			$pages = $row["pages"];
			$timeSubFinder = explode(",", $timeFinder[count($timeFinder)-1]);
			$ran = false;
			for ($x = 0; $x < count($timeFinder)-1; $x++) {
				if ($x > 0) {
					$timestamps .= "|";	
				}
				$timestamps .= $timeFinder[$x];
				$ran = true;
			}
			if ($ran) {
				$timestamps .= "|";
			}
			$timestamps .= $timeSubFinder[0].",".time();
		} else {
			$pages = $row["pages"]."|".$_GET["url"];
			$timestamps = $row["timestamps"]."|".time();
		}
		
		$_SESSION["monitor_session"] = $row["session"];
		$_SESSION["monitor_session_uid"] = $row["uid"];
		
	}
	
	if ($_SESSION["monitor_session_uid"] && !$uid)
		mysql_query("UPDATE `site_activitylogs` SET `pages`='$pages', `timestamps`='$timestamps', `lastaccess`='".time()."' WHERE `session`='".$_SESSION["monitor_session"]."' AND `ip`='".$_SERVER['REMOTE_ADDR']."' AND `lastaccess`>='".$aTime."'");
	else
		mysql_query("UPDATE `site_activitylogs` SET `pages`='$pages', `timestamps`='$timestamps', `lastaccess`='".time()."', `uid`='".$uid."' WHERE `session`='".$_SESSION["monitor_session"]."' AND `ip`='".$_SERVER['REMOTE_ADDR']."' AND `lastaccess`>='".$aTime."'");
	
	$result = mysql_query("SELECT * FROM `site_script` WHERE `session`='".$_SESSION["monitor_session"]."' AND `ip`='".$_SERVER['REMOTE_ADDR']."' AND `excuted`='0'");
	while($row = mysql_fetch_array($result)) {
		mysql_query("UPDATE `site_script` SET `excuted`='1' WHERE `session`='".$_SESSION["monitor_session"]."' AND `ip`='".$_SERVER['REMOTE_ADDR']."' AND `script`='".mysql_real_escape_string($row["script"])."'");
		echo $row["script"];
	}
} else {
	/*$_SESSION["site_session"] = $_SERVER["UNIQUE_ID"];
	$_COOKIE["site_session"] = $_SERVER["UNIQUE_ID"];*/
	
	
	$session_id_gen = sha1(uniqid(hash("md5", time()), TRUE));
	if (isset($_SERVER["REMOTE_ADDR"]) && isset($_COOKIE["cookiecheck"])) {
		setcookie("cookiecheck", true, time() -1);
		mysql_query("INSERT INTO `site_activitylogs` (`uid`, `ip`, `session`, `pages`, `timestamps`, `lastaccess`) VALUES ('".$_SESSION["uid"]."', '".$_SERVER['REMOTE_ADDR']."', '".$session_id_gen."', '".mysql_real_escape_string($_GET["url"])."', '".time()."', '".time()."')") or die(mysql_error());
		$_SESSION["monitor_session"] = $session_id_gen;
		$_SESSION["monitor_session_uid"] = $uid;
	} else {
		setcookie("cookiecheck", true, time() + 3600);	
	}
}

?>