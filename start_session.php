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
require_once("init.php");

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
	$uid = 0;
	$utype = 0;
}


function createSession($var2) {
	require_once("includes/chat/chatSession.php");
	$sess = new chatSession();
	$sess->createSession($var2);	
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
        		$langFiles[$x] .= basename($file, ".php");
				$x++;
        }
        closedir($dh);
    }
}

$langFile = get_client_language($langFiles, $chat_settings["defaultLang"]);

include("lang/chat/".$langFile.".php");

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
	
	$departBuffer = "";
	$departBufferSelect = "";
	$departDefaultSet = false;
	$result = mysql_query("SELECT * FROM `tblticketdepartments` WHERE allowlive=1");
	while($row = mysql_fetch_array($result)) {
		if (in_array($row["id"], $departments) == 1) {
			if (!$departDefaultSet) {
				$departDefaultSet = true;
				$departDefaultVal = $row["id"];
				$departDefault = $row["name"]." <span class=\"departmentOnline\">".$_LANG["online"]."</span>";
				$departBufferSelect = "<option value=\"".$row["id"]."\" selected>".$row["name"]."</option>";
			} else {
				$departBufferSelect = "<option value=\"".$row["id"]."\">".$row["name"]."</option>";	
			}
			$departBuffer .= "<div class=\"departmentCat\" name=\"".$row["id"]."\">".$row["name"]." <span class=\"departmentOnline\">".$_LANG["online"]."</span></div>";
		}
	}
}

if ($departBuffer == "") {
	unset($_SESSION["chat_name"]);
	unset($_SESSION["chat_email"]);
	unset($_SESSION["chat_question"]);
	header("Location: leavemessage.php");
	exit();	
}

if (!isset($chat_settings["skipQuestions"])) {
	$chat_settings["skipQuestions"] = "off";
}
	
if ($_POST["postData"] == "posted" || $uid != 0 && $chat_settings["skipQuestions"] == "off") {

	$myhash = sha1(uniqid(hash("md5", time()), TRUE));
	$_SESSION["chat_session"] = $myhash;
	$_SESSION["chat_name"]=$_POST["name"];
	$_SESSION["chat_email"]=$_POST["email"];
	$_SESSION["chat_question"]=$_POST["question"];
	
	$var["session"] = $_SESSION["chat_session"];
	$var["uid"] = $_SESSION["uid"];
	$var["name"] = $_POST["name"];
	$var["email"] = $_POST["email"];
	$var["question"] = $_POST["question"];
	$var["departments"] = $_POST["department"];
	if (empty($var["departments"]))
		$var["departments"] = $chat_settings["defaultDepartment"];
	$var["active"] = 1;
	$var["utype"] = $utype;
	
	if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $_POST["email"])) {
		  $emailValid = true;
	} else {
		  $emailValid = false;
		  $error = "user";
	}

	
	if ($uid == 0 && !empty($_POST["user"]) && !empty($_POST["password"])) {
		createSession($var);
		header("Location: dologin.php?goto=chatwindow.php&username=".$_POST["user"]."&password=".$_POST["password"]);
		exit("Redirecting");
	} elseif ($uid == 0 && $_POST["name"] != "" && $_POST["email"] != "" && $emailValid) {
		createSession($var);
		header("Location: chatwindow.php?chat_session=".$myhash);
		exit("Redirecting");
	} elseif ($uid != 0) {
		createSession($var);
		header("Location: chatwindow.php?chat_session=".$myhash);
		exit("Redirecting");	
	}
}

if (!$departDefaultSet) {
	$departDefault = $_LANG["nodepartment"];
}

if (!isset($error)) {
	$error = $_GET["error"];	
}

unset($_SESSION["chat_session"]);


// Template File Loader
include("includes/smarty/Smarty.class.php");
$smarty = new Smarty;

if (isset($templates_compiledir)) {
	$smarty->compile_dir = $templates_compiledir;
}

$smarty->assign("departBuffer", $departBuffer);
$smarty->assign("departDefault", $departDefault);
$smarty->assign("departDefaultVal", $departDefaultVal);
$smarty->assign("departBufferSelect", $departBufferSelect);
$smarty->assign("error", $error);
$smarty->assign("LANG", $_LANG);
$smarty->assign("SESSION", $_SESSION);

$smarty->display("chat/".$chat_settings["template"]."/startchat.tpl");

?>