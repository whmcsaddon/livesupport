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

if ($utype != 2) {
	exit("Forbidden Access");	
}
require_once("chatSession.php");

$chat_session = new chatSession();
$chat_session->useSession($_POST["session"]);
$chat_session->setDepartment($_POST["department"]);
$chat_session->setActive(1);
	
$result = mysql_query("SELECT * FROM `chat_conversations` WHERE `session`='".mysql_real_escape_string($_POST["session"])."' AND `order`>=".mysql_real_escape_string($_POST["count"])." ORDER BY `order`, `timestamp` ASC;");

$run = false;
while($row = mysql_fetch_array($result)) {
	$run = true;
	$lastCount = $row["order"];
}

if ($run) {
	$order = $lastCount + 1;
} else {
	$order = $_POST["count"];
}
	if ($_POST["datatype"] == 4) {
		$data = "You are now being transferred to ".$_POST["departmentName"].".<script type=\"text/javascript\">connected=false; connectorTimer = setTimeout(\"getCheckConnectionState();\",".$chat_settings["timeout"].");</script>";
	} elseif ($_POST["datatype"] == 5) {
		$data = "You are no longer being transferred to ".$_POST["departmentName"].".<script type=\"text/javascript\">connected=true;</script>";	
	}
	$result = mysql_query("INSERT INTO chat_conversations (`session`, `data`, `timestamp`, `order`, `datatype`)
VALUES ('".mysql_real_escape_string($_POST["session"])."', '".mysql_real_escape_string($data)."', '".$_SERVER['REQUEST_TIME']."', '".mysql_real_escape_string($order)."', '".mysql_real_escape_string($_POST["datatype"])."');");
	mysql_close();
?>