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
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: ".date("r")); // Date in the past
// Find WHMCS Directory
//    Set $pathPart to the folder to exclude from.
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
session_start();

if ($_SESSION["adminid"] != "") {
	$uid = $_SESSION["adminid"];
	$utype = 2;
} elseif ($_SESSION["uid"] != "") {
	$uid = $_SESSION["uid"];
	$utype = 1;
} else {
	$uid = -1;
	$utype = 0;
}

if (!isset($chat_settings)) {
	  $result2 = mysql_query("SELECT * FROM `chat_settings`");
	  while($row = mysql_fetch_array($result2)) {
		  $chat_settings[$row[0]] = $row[1];
	  }
}
// View Messages
if ($_POST["action"] == "view" && $utype == 2) {
	$result = mysql_query("SELECT * FROM `chat_notes` WHERE `session`='".mysql_real_escape_string($_POST["session"])."' AND `timestamp`>=".mysql_real_escape_string($_POST["noteTime"])." ORDER BY `timestamp` ASC;");
	
	$run = false;
	while($row = mysql_fetch_array($result)) {
		$run = true;
		$htmlDecode = html_entity_decode($row["note"]);
		// Identify user's name
		$userResult = mysql_query("SELECT * FROM `tbladmins` WHERE `id`='".$row["admin"]."';");
		while($uRow = mysql_fetch_array($userResult)) {
			if ($chat_settings["AdminDisplayName"] == "l") {
				$uname = $uRow["lastname"];
			} elseif ($chat_settings["AdminDisplayName"] == "f") {
				$uname = $uRow["firstname"];
			} elseif ($chat_settings["AdminDisplayName"] == "fl") {
				$uname = $uRow["firstname"]." ".$uRow["lastname"];
			} elseif ($chat_settings["AdminDisplayName"] == "lf") {
				$uname = $uRow["lastname"]." ".$uRow["firstname"];
			} elseif ($chat_settings["AdminDisplayName"] == "u") {
				$uname = $uRow["username"];
			}
		}
		
		echo "<tr><td><div class=\"innote ".$row["order"]."\">";
		echo "<span class=\"note aname\">".strip_tags(preg_replace('/<script\b[^>]*>(.*?)<\/script>/i', "", $uname)).":</span> ";
		echo "<span class=\"note asays\">".nl2br(strip_tags($htmlDecode))."</span></div></td></tr>";
		
		$lastCount = $row["timestamp"];
	}
	
	if ($_POST["noteTime"] != $lastCount && $run) {
		echo "<script type=\"text/javascript\">noteTime = ".$lastCount.";</script>";
	}
	exit();
}

// Post Message
if ($_POST["action"] == "post" && $utype == 2) {
	$data = $_POST["data"];
	
	$result = mysql_query("INSERT INTO chat_notes (`session`, `timestamp`, `admin`, `note`)
VALUES ('".mysql_real_escape_string($_POST["session"])."', '".time()."', '".$uid."', '".mysql_real_escape_string($data)."');");
	
	require("chatSession.php");
	$chat_session = new chatSession();
	$chat_session->useSession($_POST["session"]);
	if ($chat_session->getActive() == 2) {
		$userResult = mysql_query("SELECT * FROM `tbladmins` WHERE `id`='".$uid."'");
		while($uRow = mysql_fetch_array($userResult)) {
			$uname = $uRow["firstname"]." ".$uRow["lastname"];
		}
		$result = mysql_query("INSERT INTO `tblticketnotes` (`ticketid`, `admin`, `date`, `message`) VALUES ('".$chat_session->getTID()."', '".mysql_real_escape_string($uname)."', '".date("Y-m-d G:i:s")."', '".mysql_real_escape_string($data)."');");
	}
	exit();
}

echo "<script type=\"text/javascript\">antiNoteHacker=true;</script>";
?>