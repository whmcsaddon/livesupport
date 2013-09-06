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
//error_reporting(0);

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

require_once("../../init.php");
session_start();


// View Messages

switch ($_POST["action"]) {
	case "view":
	
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
	
	$result = mysql_query("SELECT * FROM `chat_conversations` WHERE `session`='".mysql_real_escape_string($_POST["session"])."' AND `order`>=".mysql_real_escape_string($_POST["count"])." ORDER BY `order`, `timestamp` ASC;");
	$numberRows = mysql_num_rows($result);
	$run = false;
	if ($numberRows > 0) {
		if (!isset($chat_settings)) {
		  $result2 = mysql_query("SELECT * FROM `chat_settings`");
		  while($row = mysql_fetch_array($result2)) {
			  $chat_settings[$row[0]] = $row[1];
		  }
		}
		mysql_free_result($result2);
	  while($row = mysql_fetch_array($result)) {
		  $run = true;
		  $htmlDecode = htmlspecialchars($row["data"]);
		  // Script Excutable
		  if ($row["datatype"] == 1 && $row["ulevel"] == 2) {
			$lastCount = $row["order"];
		  	$htmlDecode = htmlspecialchars_decode($row["data"]);
			$htmlDecode .= "<script>count++;</script>";
			// Download
		  } elseif ($row["datatype"] == 2) {
			$lastCount = $row["order"];
		  	echo "<div class='inchat downloadFile ".$row["order"]."'>".htmlspecialchars_decode($row["data"])."</div>";
			continue;
		  // Connected User
		  } elseif ($row["datatype"] == 3) {
			$lastCount = $row["order"];
			if ($uid != $row["uid"] || $utype != $row["ulevel"])
		  		echo "<div class='inchat connectedUser ".$row["order"]."'>".htmlspecialchars_decode($row["data"])."</div>";
			continue;
		  // Transfer
		  } elseif ($row["datatype"] == 4) {
			$lastCount = $row["order"];
			if ($utype == 2) {
				echo "<div class='inchat transfer operator ".$row["order"]."'><strong>Transfer Request Initiated!</strong></div>";	
			} else {
		  		echo "<div class='inchat transfer".$row["order"]."'>".htmlspecialchars_decode($row["data"])."</div>";
			}
			continue;
		  // Cancel Transfer
		  } elseif ($row["datatype"] == 5) {
			$lastCount = $row["order"];
			if ($utype == 2) {
				echo "<div class='inchat transfer operator red ".$row["order"]."'><strong>Transfer has been stopped!</strong></div>";	
			} else {
		  		echo "<div class='inchat transfer".$row["order"]."'>".htmlspecialchars_decode($row["data"])."</div>";
			}
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
		  
		  //$urlPattern[0] = "[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]";
		  //$urlPattern[1] = "www.[^<>[:space:]]+[[:alnum:]/]";
		  
		  if ($row["ulevel"] != 2) {
			  echo "<div class=\"inchat client ".$row["order"]."\">";
			  if (strip_tags(preg_replace('/<script\b[^>]*>(.*?)<\/script>/i', "", $htmlDecode)) != "") {
				  echo "<span class=\"inchat client uname\">".$uname.":</span> ";
			  }
			  echo "<span class=\"inchat client usays\">".strip_tags($htmlDecode)."</span></div>";
		  } else {
			  if ($utype != 2) {
				  echo "<div class=\"inchat operator ".$row["order"]."\">";
				  if (strip_tags(preg_replace('/<script\b[^>]*>(.*?)<\/script>/i', "", $htmlDecode)) != "") {
					  echo "<span class=\"inchat operator uname\">".$uname.":</span> ";
				  }
				  echo "<span class=\"inchat operator usays\">".strip_tags($htmlDecode, $chat_settings["adminHTML"])."</span></div>";	
			  } else {
				  preg_match_all('/<script\b[^>]*>(.*?)<\/script>/i', $htmlDecode, $matches);
				  if ($matches[1][0] != "") {
					  echo "<div class=\"inchat inchat script ".$row["order"]."\">Script Executed<div class=\"executed\">";
					  for ($x = 0; $x <= count($matches[1]); $x++) {
						  if ($matches[$x] != "") {
							  echo $matches[1][$x];
						  }
					  }
					  echo "</div></div>";
				  }
				  echo "<div class=\"inchat operator ".$row["order"]."\">";
				  if (strip_tags(preg_replace('/<script\b[^>]*>(.*?)<\/script>/i', "", $htmlDecode)) != "") {
					  echo "<span class=\"inchat operator uname\">".$uname.":</span> ";
				  }
				  echo "<span class=\"inchat operator usays\">".strip_tags(preg_replace('/<script\b[^>]*>(.*?)<\/script>/i', "", $htmlDecode), $chat_settings["adminHTML"])."</span></div>";	
			  }
  
		  }
		  //echo "<div class=\"chat ".$row["order"]."\">".$row["data"]."</div>";
		  $lastCount = $row["order"];
	  }
	}
	mysql_free_result($result);
	
	include "chatSession.php";
	$cSess = new chatSession();
	$cSess->useSession($_POST["session"]);
	if ($_POST["wmessage"] == "true") {
		$cSess->setWritingMessage($uid.":".$utype, 1);
	} else {
		$cSess->setWritingMessage($uid.":".$utype, 0);
	}
	
	$writeMessage = array();
	$writeMessage = $cSess->getWritingMessage();
	$isWritingM = false;
	//print_r($writeMessage);
	foreach ($writeMessage as $arrWM) {
		$arrWM2 = explode(":", $arrWM);
		if (!empty($arrWM) && $arrWM2[0] != $uid && $arrWM2[1] != $utype) {
			$isWritingM = true;
		}
	}
	
	if ($isWritingM) {
		if (!$_SESSION["chat_isWriting_".$_POST["session"]]) {
			$_SESSION["chat_isWriting_".$_POST["session"]] = true;
			echo "<script type=\"text/javascript\">enableWritingIcon();</script>";
		}
		//echo "Enabled";
	} else {
		if ($_SESSION["chat_isWriting_".$_POST["session"]]) {
			$_SESSION["chat_isWriting_".$_POST["session"]] = false;
			echo "<script type=\"text/javascript\">disableWritingIcon();</script>";
		}
		//echo "Disabled";
	}
	
	if ($run && $_POST["count"] != $lastCount) {
		echo "<script type=\"text/javascript\">count = ".$lastCount.";</script>";
		//print_r($chat_settings);
	}
	mysql_close();
	
	break;
	case "post":
	
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

	$data = $_POST["data"];
	
	if ($data == "%operatorConnectedMessage%" && $_POST["datatype"] == 3) {
		$data = $chat_settings["operatorConnectedMessage"];
		
		$userResult = mysql_query("SELECT * FROM `tbladmins` WHERE `id`='".$_SESSION["adminid"]."';");
		while($uRow = mysql_fetch_array($userResult)) {
		 	$data = str_replace("%FIRSTNAME%", $uRow["firstname"], $data);
			$data = str_replace("%LASTNAME%", $uRow["lastname"], $data);
	  	}
		$data = htmlspecialchars_decode($data);
		$data .= "<script type=\"text/javascript\">connected=true;</script>";
	}
	
	if ($data == "%clientConnectedMessage%" && $_POST["datatype"] == 3) {
		$data = $chat_settings["clientConnectedMessage"];
		
		if ($utype == 1) {
			$userResult = mysql_query("SELECT * FROM `tblclients` WHERE `id`='".$uid."';");
			while($uRow = mysql_fetch_array($userResult)) {
				$data = str_replace("%FIRSTNAME%", $uRow["firstname"], $data);
				$data = str_replace("%LASTNAME%", $uRow["lastname"], $data);
				$data = str_replace("%ENTEREDNAME%", "", $data);
			}
		} elseif ($utype == 0) {
			$data = str_replace("%FIRSTNAME%", "", $data);
			$data = str_replace("%LASTNAME%", "", $data);
			$data = str_replace("%ENTEREDNAME%", $_POST["user"], $data);
		}
	}
	
	$data = htmlspecialchars_decode($data);
	
	$result = mysql_query("INSERT INTO chat_conversations (`session`, `user`, `uid`, `ulevel`, `data`, `timestamp`, `order`, `datatype`)
VALUES ('".mysql_real_escape_string($_POST["session"])."', '".mysql_real_escape_string($_POST["user"])."', '".$uid."', '".$utype."', '".mysql_real_escape_string($data)."', '".$_SERVER['REQUEST_TIME']."', '".mysql_real_escape_string($order)."', '".mysql_real_escape_string($_POST["datatype"])."');");

	if ($_POST["datatype"] == 0) {
		require("chatSession.php");
		$chat_session = new chatSession();
		$chat_session->useSession($_POST["session"]);
		if ($chat_session->getActive() == 2) {
			$userResult = mysql_query("SELECT * FROM `tbladmins` WHERE `id`='".$uid."'");
			while($uRow = mysql_fetch_array($userResult)) {
				$uname = $uRow["firstname"]." ".$uRow["lastname"];
			}
			$result = mysql_query("INSERT INTO `tblticketreplies` (`tid`, `date`, `message`, `admin`) VALUES ('".$chat_session->getTID()."', '".date("Y-m-d G:i:s")."', '".mysql_real_escape_string($data)."', '".mysql_real_escape_string($uname)."');");
		}
	}
	mysql_close();
	break;
}

?>