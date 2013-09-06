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

session_start();

if (!isset($chat_settings)) {
	  $result2 = mysql_query("SELECT * FROM `chat_settings`");
	  while($row = mysql_fetch_array($result2)) {
		  $chat_settings[$row[0]] = $row[1];
	  }
}
		
require("chatSession.php");
$chat_session = new chatSession();
$chat_session->useSession($_POST["session"]);
$departments = $chat_session->getDepartments();
$env = $chat_session->getEnvironment();
$chat_session->setActive("2");

if (!empty($_SESSION["uid"])) {
	$uid = $_SESSION["uid"];
	$utype = 1;
} else {
	$uid = 0;
	$utype = 0;
}
	
$result = mysql_query("SELECT * FROM `chat_conversations` WHERE `session`='".mysql_real_escape_string($_POST["session"])."' ORDER BY `order`, `timestamp` ASC;");

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

	$data = $_POST["user"]." has now left the conversation.";
	
	$result = mysql_query("INSERT INTO chat_conversations (`session`, `user`, `uid`, `ulevel`, `data`, `timestamp`, `order`, `datatype`)
VALUES ('".mysql_real_escape_string($_POST["session"])."', '".mysql_real_escape_string($_POST["user"])."', '".$uid."', '".$utype."', '".mysql_real_escape_string($data)."', '".$_SERVER['REQUEST_TIME']."', '".mysql_real_escape_string($order)."', '".mysql_real_escape_string($_POST["datatype"])."');");
	

// Run Create Ticket Function:
	// Create the chat message data.
	$chatBuffer = "";
	$chatConvoActive = false;
	$result = mysql_query("SELECT * FROM `chat_conversations` WHERE `session`='".mysql_real_escape_string($_POST["session"])."' ORDER BY `order`, `timestamp` ASC;");
	$numberRows = mysql_num_rows($result);
		
	while($row = mysql_fetch_array($result)) {
		$htmlDecode = htmlspecialchars($row["data"]);
		  // Download
		if ($row["datatype"] == 2) {
		  $lastCount = $row["order"];
		  //$chatBuffer .= "<div style='background-color:#CCC;padding:5px;border:1px #999 solid;color:#333;font-family:\"Courier New\", Courier, monospace;font-size:12px;margin:5px;margin-left:50px;width:350px;'>".htmlspecialchars_decode($row["data"])."</div>";
		  continue;
		// Connected User
		} elseif ($row["datatype"] == 3) {
		  $lastCount = $row["order"];
		  if ($uid != $row["uid"])
			  $chatBuffer .= "<div style='color:#090;'>".htmlspecialchars_decode($row["data"])."</div>";
		  continue;
		// Transfer
		} elseif ($row["datatype"] == 4) {
		  $lastCount = $row["order"];
		  $chatBuffer .= "<div style='color:#090;'>".htmlspecialchars_decode($row["data"])."</div>";
		  continue;
		// Cancel Transfer
		} elseif ($row["datatype"] == 5) {
		  $lastCount = $row["order"];
		  $chatBuffer .= "<div style='color:#090;'>".htmlspecialchars_decode($row["data"])."</div>";
		  continue;
		}
		// Identify user's name
		switch ($row["ulevel"]) {
			case 2:
				$userResult = mysql_query("SELECT * FROM `tbladmins` WHERE `id`='".$row["uid"]."';");
				while($uRow = mysql_fetch_array($userResult)) {
					switch ($chat_settings["AdminDisplayName"]) {
						case "l":
							$uname = $uRow["lastname"];
							break;
						case "f":
							$uname = $uRow["firstname"];
							break;
						case "fl":
							$uname = $uRow["firstname"]." ".$uRow["lastname"];
							break;
						case "lf":
							$uname = $uRow["lastname"]." ".$uRow["firstname"];
							break;
						case "u":
							$uname = $uRow["username"];
							break;
						default:
							$uname = $uRow["firstname"];
							break;
					}
				}
				break;
			case 1:
				$userResult = mysql_query("SELECT * FROM `tblclients` WHERE `id`='".$row["uid"]."';");
				while($uRow = mysql_fetch_array($userResult)) {
					switch ($chat_settings["ClientDisplayName"]) {
						case "l":
							$uname = $uRow["lastname"];
							break;
						case "f":
							$uname = $uRow["firstname"];
							break;
						case "fl":
							$uname = $uRow["firstname"]." ".$uRow["lastname"];
							break;
						case "lf":
							$uname = $uRow["lastname"]." ".$uRow["firstname"];
							break;
						default:
							$uname = $uRow["firstname"];
							break;
					}
				}
				break;
			default:
				$uname = $row[2];
				break;
		}
		
		
		if ($row["ulevel"] != 2) {
			$chatBuffer .= "<div class=\"inchat client ".$row["order"]."\">";
			if (strip_tags(preg_replace('/<script\b[^>]*>(.*?)<\/script>/i', "", $htmlDecode)) != "") {
				$chatBuffer .= "<span style=\"font-weight:bold;\">".$uname.":</span> ";
			}
			$chatBuffer .= "<span class=\"inchat client usays\">".strip_tags($htmlDecode)."</span></div>";
			$chatConvoActive = true;
		} else {
			$chatBuffer .= "<div class=\"inchat operator ".$row["order"]."\">";
			if (strip_tags(preg_replace('/<script\b[^>]*>(.*?)<\/script>/i', "", $htmlDecode)) != "") {
				$chatBuffer .= "<span style=\"font-weight:bold;color:#009;\">".$uname.":</span> ";
			}
			
			if ($row["datatype"] == 1) {
				$search = array('@<script[^>]*?>.*?</script>@si','@<style[^>]*?>.*?</style>@siU','/\s{2,}/');
				
				$htmlDecode = preg_replace($search, "\n", html_entity_decode($htmlDecode));
			}
			
			$chatBuffer .= "<span style=\"color:#009;\">".strip_tags($htmlDecode, $chat_settings["adminHTML"])."</span></div>";	
		   $chatConvoActive = true;
		   
		} 

	}
	
if ($chatConvoActive) {
	//echo $chatBuffer;
	$tid = rand(10000, 9999999);
	$result = mysql_query("SELECT `tid` FROM `tbltickets` WHERE `tid`='".$tid."'");
	$numberRows = mysql_num_rows($result);
	while ($numberRows > 0) {
		$tid = rand(10000, 9999999);
		$result = mysql_query("SELECT `tid` FROM `tbltickets` WHERE `tid`='".$tid."'");
		$numberRows = mysql_num_rows($result);
	}
	
	if (empty($departments[count($departments)-1]))
		$departments[count($departments)-1] = $chat_settings["defaultDepartment"];
		
	$result = mysql_query("INSERT INTO `tbltickets` (`tid`, `did`, `userid`, `name`, `email`, `c`, `date`, `title`, `message`, `status`, `urgency`, `lastreply`, `clientunread`, `adminunread`)
VALUES ('".$tid."', '".$departments[count($departments)-1]."', '".$uid."', '".mysql_real_escape_string($chat_session->getName())."', '".mysql_real_escape_string($chat_session->getEmail)."', '".$_SERVER["UNIQUE_ID"]."', '".date("Y-m-d G:i:s")."', 'Live Chat Transcript', '".mysql_real_escape_string($chatBuffer)."', 'Closed', 'Low', '".date("Y-m-d G:i:s")."','0', '0');");

	$result3 = mysql_query("SELECT `id` FROM `tbltickets` WHERE `tid`='".$tid."'");
	while($row3 = mysql_fetch_array($result3)) {
		$ticketid = $row3["id"];
		//print_r($row3);
	}
	//echo $ticketid;
		
	
	$chat_session->setActive("2");
	$chat_session->setTID($ticketid);
		
	// Inputted Information
	$inputBuffer = "Name: ".$chat_session->getName()."<br />Email: ".$chat_session->getEmail()."<br />Question: ".$chat_session->getQuestion()."<br />Session: ".$chat_session->getSession()."<br />Remote IP: ".$env["REMOTE_ADDR"]."<br />HTTP User Agent: ".$env["HTTP_USER_AGENT"];
	$result2 = mysql_query("INSERT INTO `tblticketnotes` (`ticketid`, `admin`, `date`, `message`) VALUES ('".$ticketid."', 'Live Chat System', '".date("Y-m-d G:i:s", $chat_session->getTimestamp())."', '".mysql_real_escape_string($inputBuffer)."');");

	// Post Notes to ticket notes:
	$result = mysql_query("SELECT * FROM `chat_notes` WHERE `session`='".mysql_real_escape_string($_POST["session"])."' ORDER BY `timestamp` ASC;");
	
	while($row = mysql_fetch_array($result)) {
		$noteBuffer = "";
		$htmlDecode = html_entity_decode($row["note"]);
		// Identify user's name
		$userResult = mysql_query("SELECT * FROM `tbladmins` WHERE `id`='".$row["admin"]."';");
		while($uRow = mysql_fetch_array($userResult)) {
			$uname = $uRow["firstname"]." ".$uRow["lastname"];
		}
		
		$noteBuffer .= "<div class=\"innote ".$row["order"]."\">";
		//$noteBuffer .= "<span class=\"note aname\">".strip_tags(preg_replace('/<script\b[^>]*>(.*?)<\/script>/i', "", $uname)).":</span> ";
		$noteBuffer .= "<span class=\"note asays\">".nl2br(strip_tags($htmlDecode))."</span></div>";
		
		
		$result2 = mysql_query("INSERT INTO `tblticketnotes` (`ticketid`, `admin`, `date`, `message`) VALUES ('".$ticketid."', '".mysql_real_escape_string($uname)."', '".date("Y-m-d G:i:s", $row["timestamp"])."', '".mysql_real_escape_string($noteBuffer)."');");

	}
}

//echo "End Session";
$_SESSION["chat_last_session"] = $_SESSION["chat_session"];
unset($_SESSION["chat_session"]);
?>