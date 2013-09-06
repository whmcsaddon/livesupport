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
$posted = -1;
require_once("init.php");


if (!isset($chat_settings)) {
	  $result2 = mysql_query("SELECT * FROM `chat_settings`");
	  while($row = mysql_fetch_array($result2)) {
		  $chat_settings[$row[0]] = $row[1];
	  }
}
session_start();
if ($_SESSION["uid"] != "") {
	$uid = $_SESSION["uid"];
	$utype = 1;
} else {
	$uid = 0;
	$utype = 0;
}

if ($utype == 1) {
	$result = mysql_query("SELECT * FROM `tblclients` WHERE `id`='".$uid."'");
	while($row = mysql_fetch_array($result)) {
		$user = $row;
	}	
}

if ($_POST["action"] == "post") {
	$posted = 0;
	if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $_POST["email"])) {
		  $emailValid = true;
	} else {
		  $emailValid = false;
		  $posted = 0;
	}
	
	if ($_POST["message"] != "" && $_POST["name"] != "" && $emailValid && $_POST["subject"] != "") {
		require("includes/chat/chatSession.php");
		$chat_session = new chatSession();
		$chat_session->useSession($_SESSION["chat_last_session"]);
		$departments = $chat_session->getDepartments();
	
		$tid = rand(10000, 9999999);
		$result = mysql_query("SELECT `tid` FROM `tbltickets` WHERE `tid`='".$tid."'");
		$numberRows = mysql_num_rows($result);
		while ($numberRows > 0) {
			$tid = rand(10000, 9999999);
			$result = mysql_query("SELECT `tid` FROM `tbltickets` WHERE `tid`='".$tid."'");
			$numberRows = mysql_num_rows($result);
		}
		
		if ($departments[count($departments)-1] != "" && $departments[count($departments)-1] != -1) {
			$useDepart = $departments[count($departments)-1];
		} else {
			$useDepart = $chat_settings["defaultDepartment"];
		}
		
		$result2 = mysql_query("SELECT * FROM `tblconfiguration`");
		  while($row = mysql_fetch_array($result2)) {
			  $settings[$row[0]] = $row[1];
		  }

		
		if (in_array($_SERVER["SERVER_ADDR"], explode("\n", $settings["APIAllowedIPs"])) == 0) {
			if ($settings["APIAllowedIPs"] == "") {
				$result = mysql_query("UPDATE `tblconfiguration` SET `value`='".$_SERVER["SERVER_ADDR"]."' WHERE `setting`='APIAllowedIPs'");		
			} else {
				$result = mysql_query("UPDATE `tblconfiguration` SET `value`='".$settings["APIAllowedIPs"]."\n".$_SERVER["SERVER_ADDR"]."' WHERE `setting`='APIAllowedIPs'");		
			}
		}
		
		$result2 = mysql_query("SELECT * FROM `tbladmins` ORDER BY `id` LIMIT 1");
		while($row = mysql_fetch_array($result2)) {
				$adminUser = $row;
		}

		$url = $settings["SystemURL"]."/includes/api.php"; # URL to WHMCS API file
		
		$postfields["username"] = $adminUser["username"];
		$postfields["password"] = $adminUser["password"];
		$postfields["action"] = "openticket"; 
		$postfields["name"] = $_POST["name"];
		$postfields["email"] = $_POST["email"];
		$postfields["clientid"] = $uid;
		$postfields["deptid"] = $useDepart;
		$postfields["subject"] = $_POST["subject"];
		$postfields["message"] = $_POST["message"];
		$postfields["priority"] = "Medium";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 100);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

		$data = curl_exec($ch);
		curl_close($ch);
		
		$data = explode(";",$data);
		foreach ($data AS $temp) {
		  $temp = explode("=",$temp);
		  $results[$temp[0]] = $temp[1];
		}
		//print_r($results);
		if ($results["result"]=="success") {
			$posted = 1;
		} else {
			$posted = 0;
		}
		
	} else {
		$posted = 0;	
	}
}

function get_client_language($availableLanguages, $default="en"){
	
	if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
			
		$langs=explode(",",$_SERVER["HTTP_ACCEPT_LANGUAGE"]);

		//start going through each one
		foreach ($langs as $value){
	
			$choice=substr($value,0,2);
			if(in_array($choice, $availableLanguages)){
				return $choice;
				
			}
			
		}
	} 
	return $default;
}


$dir = "lang/chat";
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
		$x = 0;
        while (($file = readdir($dh)) !== false) {
        	if ($file != "." && $file != "..")
        		$langFiles[$x] = basename($file, ".php");
				$x++;
        }
        closedir($dh);
    }
}

$langFile = get_client_language($langFiles, $chat_settings["defaultLang"]);

include("lang/chat/".$langFile.".php");

// Template File Loader
include("includes/smarty/Smarty.class.php");
$smarty = new Smarty;

if (isset($templates_compiledir)) {
	$smarty->compile_dir = $templates_compiledir;
}

if ($_POST["action"] == "post") {
	$smarty->assign("displayMessage", $posted);
}
$smarty->assign("uid", $uid);
$smarty->assign("user", $user);
$smarty->assign("LANG", $_LANG);
$smarty->assign("SESSION", $_SESSION);

$smarty->display("chat/".$chat_settings["template"]."/leavemessage.tpl");
?>