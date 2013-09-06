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

# Get Variables from storage (retrieve from wherever it's stored - DB, file, etc...)
if (!isset($chat_settings)) {
	  $result2 = mysql_query("SELECT * FROM `chat_settings`");
	  while($row = mysql_fetch_array($result2)) {
		  $chat_settings[$row[0]] = $row[1];
	  }
}


session_start();

//print_r($_SESSION);
if (!isset($_SESSION["adminid"])) {
	exit("You do not have permission to view this page.");	
}

require("chatSession.php");
$chat_session = new chatSession();
$chat_session->useSession($_GET["session"]);
$env = $chat_session->getEnvironment();

//echo $env["REMOTE_ADDR"];
	
if ($_GET["action"] == "ignore") {
	$chat_session->setIgnored($_SESSION["adminid"]);
}

if ($_GET["action"] == "block") {
	
	
	$result = mysql_query("INSERT INTO `chat_ban` (`ip`, `date`)
VALUES ('".mysql_real_escape_string($env["REMOTE_ADDR"])."', '".time()."')");
}

if ($_GET["action"] == "script") {
	
	$script = htmlspecialchars_decode($_POST["script"]);
	$result = mysql_query("INSERT INTO `site_script` (`ip`, `session`, `script`, `excuted`)
VALUES ('".mysql_real_escape_string($_SERVER["REMOTE_ADDR"])."', '".mysql_real_escape_string($_GET["session"])."', '".mysql_real_escape_string($script)."', '0')");
}

?>