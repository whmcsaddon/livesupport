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

$directoryFinder = explode("/", $_SERVER["SCRIPT_FILENAME"]);
$dir = "";
foreach($directoryFinder as $pathPart) {
	if ($pathPart != "") {
		if ($pathPart != "chatwindow.php") {
			$dir .= "/".$pathPart;
		} else {
			$dir .= "/";
			break;
		}
	}
}
$dir = str_replace($_SERVER["DOCUMENT_ROOT"], "", $dir);

require("init.php");

if (empty($customadminpath)) $customadminpath="admin";


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

require_once("includes/chat/chatSession.php");

if ($utype != 2) {
	if (!isset($_SESSION["chat_session"])) {
		header("Location: start_session.php?error=invalid");
	}
	
	$chat_sess = new chatSession();
	$chat_sess->useSession($_SESSION["chat_session"]);
	
	if ($chat_sess->getUID() != $_SESSION["uid"] && isset($_SESSION["uid"])) {
		$chat_sess->setUID($_SESSION["uid"]);
	}
	
	if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $chat_sess->getEmail())) {
		  $emailValid = true;
	} else {
		  $emailValid = false;
	}
	
	if (!isset($_SESSION["uid"]) && $chat_sess->getName() == "") {
		header("Location: start_session.php?error=user&user=".$chat_sess->getName());
	} elseif (!isset($_SESSION["uid"]) && !$emailValid) {
		header("Location: start_session.php?error=user&email=".$chat_sess->getEmail());
	}
} else {
	$chat_sess = new chatSession();
	$secret = $_GET["secret"];
	if ($_GET["session"] == "") {
		$secret = "true";
		$chat_sess->useSession($_SESSION["chat_session"]);
	} else {
		$chat_sess->useSession($_GET["session"]);
	}
	$chat_sess->setActive(0);
}


$result = mysql_query("SELECT * FROM `tblconfiguration`");
	while($row = mysql_fetch_array($result)) {
		if ($row[0] == "SystemSSLURL") {
			if ($row[1] != "") {
				$url = $row[1];	
			}
		}
		if ($row[0] == "SystemURL") {
			if ($url == "") {
				$url = $row[1];	
			}
		}
	}
$url = substr($url, 0, -1);

if ($utype == 2) {
// Client Info
$env = $chat_sess->getEnvironment();

if ($chat_sess->getUID() != 0) {
	$bufferClientInfo .= "<div class=\"clientSummery\"><img src=\"images/details.gif\" alt=\"Client\" /> <a href=\"".$url."/".$customadminpath."/clientssummary.php?userid=".$chat_sess->getUID()."\" target=\"_blank\">Client Summary Page</a></div><br />";
}

$bufferClientInfo .= "<strong>Inputted Data</strong><br />
		
		<table class=\"clientInfo\">
			<tr>
				<td>Name</td>
				<td>".$chat_sess->getName()."</td>
			</tr>
			
			<tr>
				<td>Email</td>
				<td>".$chat_sess->getEmail()."</td>
			</tr>
			
			<tr>
				<td>Question</td>
				<td>".$chat_sess->getQuestion()."</td>
			</tr>
			
			<tr>
				<td>Session</td>
				<td>".$chat_sess->getSession()."</td>
			</tr>
			
			<tr>
				<td>Remote IP</td>
				<td>".$env["REMOTE_ADDR"]."</td>
			</tr>
			
			<tr>
				<td>HTTP User Agent</td>
				<td>".$env["HTTP_USER_AGENT"]."</td>
			</tr>
		</table>";
		
	if ($chat_sess->getUID() == 0) {
		$guessBuffer = "";
		$guessBuffer .= "<br /><strong>Intelligent Guess</strong><br /><table class=\"clientInfo\">";
		
		$run = false;
		$result = mysql_query("SELECT * FROM `tblclients` WHERE `email` LIKE '".$chat_sess->getEmail()."' OR `ip`='".$env["REMOTE_ADDR"]."'");
		while ($row = mysql_fetch_array($result)) {
			if ($clientSelected[$row["id"]] != "true") {
				$guessBuffer .= "<tr>
				<td>Client: <a href=\"".$url."/".$customadminpath."/clientssummary.php?userid=".$row["id"]."\" target=\"_blank\">".$row["firstname"]." ".$row["lastname"]."</a></td>
				</tr>";
				$clientSelected[$row["id"]] = "true";
				$run = true;
			}
		}
		
		$name = explode(" ", $chat_sess->getName());
		for ($guess = 0; $guess < count($name); $guess++) {
			$result = mysql_query("SELECT * FROM `tblclients` WHERE `firstname` LIKE '".$name[$guess]."' OR `lastname` LIKE '".$name[$guess]."' OR `companyname` LIKE '".$name[$guess]."'");
			while ($row = mysql_fetch_array($result)) {

				if ($clientSelected[$row["id"]] != "true") {
					$guessBuffer .= "<tr>
					<td>Client: <a href=\"".$url."/".$customadminpath."/clientssummary.php?userid=".$row["id"]."\" target=\"_blank\">".$row["firstname"]." ".$row["lastname"]."</a></td>
					</tr>";
					$clientSelected[$row["id"]] = "true";
					$run = true;
				}
			}
		}
		
		
		// Contacts search
		
		$result = mysql_query("SELECT * FROM `tblcontacts` WHERE `email` LIKE '".$chat_sess->getEmail()."'");
		while ($row = mysql_fetch_array($result)) {
			if ($contactSelected[$row["id"]] != "true") {
				$guessBuffer .= "<tr>
					<td>Contact: <a href=\"".$url."/".$customadminpath."/clientscontacts.php?userid=".$row["userid"]."&contactid=".$row["id"]."\" target=\"_blank\">".$row["firstname"]." ".$row["lastname"]."</a></td>
					</tr>";
				$contactSelected[$row["id"]] = "true";
				$run = true;
			}
		}

		for ($guess = 0; $guess < count($name); $guess++) {
			$result = mysql_query("SELECT * FROM `tblcontacts` WHERE `firstname` LIKE '".$name[$guess]."' OR `lastname` LIKE '".$name[$guess]."' OR `companyname` LIKE '".$name[$guess]."'");
			while ($row = mysql_fetch_array($result)) {
				if ($contactSelected[$row["id"]] != "true") {
					$guessBuffer .= "<tr>
					<td>Contact: <a href=\"".$url."/".$customadminpath."/clientscontacts.php?userid=".$row["userid"]."&contactid=".$row["id"]."\" target=\"_blank\">".$row["firstname"]." ".$row["lastname"]."</a></td>
					</tr>";
					$contactSelected[$row["id"]] = "true";
					$run = true;
				}
			}
		}
		
		$guessBuffer .= "</table>";
		
		if ($run)
			$bufferClientInfo .= $guessBuffer;
	}
	
	
	if ($chat_sess->getUID() != 0) {
		
		$result = mysql_query("SELECT * FROM `tblclients` WHERE `id`='".mysql_real_escape_string($chat_sess->getUID())."'");
	
		while($row = mysql_fetch_array($result)) {
		
		$bufferClientInfo .= "<br /><strong>Client Information</strong><br />
		
		<table class=\"clientInfo\">
			<tr>
				<td>First Name</td>
				<td>".$row["firstname"]."</td>
			</tr>
			
			<tr>
				<td>Last Name</td>
				<td>".$row["lastname"]."</td>
			</tr>
			
			<tr>
				<td>Company Name</td>
				<td>".$row["companyname"]."</td>
			</tr>
			
			<tr>
				<td>Email Address</td>
				<td>".$row["email"]."</td>
			</tr>
			
			<tr>
				<td>Address 1</td>
				<td>".$row["address1"]."</td>
			</tr>
			
			<tr>
				<td>Address 2</td>
				<td>".$row["address2"]."</td>
			</tr>
			
			<tr>
				<td>City</td>
				<td>".$row["city"]."</td>
			</tr>
			
			<tr>
				<td>State</td>
				<td>".$row["state"]."</td>
			</tr>
			
			<tr>
				<td>Postal Code</td>
				<td>".$row["postcode"]."</td>
			</tr>
			
			<tr>
				<td>Country</td>
				<td>".$row["country"]."</td>
			</tr>
			
			<tr>
				<td>Phone Number</td>
				<td>".$row["phonenumber"]."</td>
			</tr>
		</table>";
		
		$result2 = mysql_query("SELECT * FROM `tblcontacts` WHERE `userid`='".mysql_real_escape_string($chat_sess->getUID())."'");
		
		$bufferClientInfo .= "<br /><strong>Contacts/Sub-Accounts</strong><br />
			<table class=\"clientInfo\">";
			
			while($row2 = mysql_fetch_array($result2)) {
			
				$bufferClientInfo .= "<tr>
					<td><a href=\"".$url."/".$customadminpath."/clientscontacts.php?userid=".$row2["userid"]."&contactid=".$row2["id"]."\" target=\"_blank\">".$row2["firstname"]." ".$row2["lastname"]."</a></td>
					<td>".$row2["email"]."</td>
				</tr>";
			}
			
			$bufferClientInfo .= "</table>";
			
			$bufferClientInfo .= "<br /><strong>Other Information</strong><br />
		
		<table class=\"clientInfo\">
			<tr>
				<td>Status</td>
				<td>".$row["status"]."</td>
			</tr>
			
			<tr>
				<td>Client Group</td>
				<td>";
			
			$result2 = mysql_query("SELECT * FROM `tblclientgroups` WHERE `id`='".$row["groupid"]."'");
			
			while($row2 = mysql_fetch_array($result2)) {
				$groupname = $row2["groupname"];	
			}
			if (isset($groupname)) {
				$bufferClientInfo .= $groupname;	
			} else {
				$bufferClientInfo .= "none";	
			}
			$bufferClientInfo .= "</td>
			</tr>
			
			<tr>
				<td>Tax Exempt</td>
				<td>";
			if ($row["taxexempt"] == "on") {
				$bufferClientInfo .= "Yes";	
			} else {
				$bufferClientInfo .= "No";	
			}
			$bufferClientInfo .= "</td>
			</tr>
			
			<tr>
				<td>Signup Date</td>
				<td>".$row["datecreated"]."</td>
			</tr>
			
			<tr>
				<td>Last Login</td>
				<td>Date: ".$row["lastlogin"]."<br />
					IP Address: ".$row["ip"]."<br />
					Host: ".$row["host"]."</td>
			</tr>
		</table>";
		
		
		
		$result2 = mysql_query("SELECT * FROM `tblcurrencies` WHERE `id`='".$row["currency"]."'");
			
			while($row2 = mysql_fetch_array($result2)) {
				$currencySetting = $row2;
			}
		$bufferClientInfo .= "<br /><strong>Invoices/Billing</strong><br />
		<table class=\"clientInfo\">
			<tr>
				<td>Paid Invoices</td>
				<td>";
			$result2 = mysql_query("SELECT * FROM `tblinvoices` WHERE `status`='Paid' AND `userid`='".$uid."'");
			
			$bufferClientInfo .= mysql_num_rows($result2);
			while($row2 = mysql_fetch_array($result2)) {
				$price += $row2["total"];
			}
			$bufferClientInfo .= " (".$currencySetting["prefix"].number_format($price, 2, ".", "").$currencySetting["suffix"].")";	
			$bufferClientInfo .= "</td>
			</tr>
			
			<tr>
				<td>Unpaid/Due</td>
				<td>";
			
			$price = 0;
			$result2 = mysql_query("SELECT * FROM `tblinvoices` WHERE `status`='Unpaid' AND `userid`='".$uid."'");
			
			$bufferClientInfo .= mysql_num_rows($result2);
			while($row2 = mysql_fetch_array($result2)) {
				$price += $row2["total"];
			}
			$bufferClientInfo .= " (".$currencySetting["prefix"].number_format($price, 2, ".", "").$currencySetting["suffix"].")";	

			$bufferClientInfo .= "</td>
			</tr>
			
			<tr>
				<td>Cancelled</td>
				<td>";
			$price = 0;
			$result2 = mysql_query("SELECT * FROM `tblinvoices` WHERE `status`='Cancelled' AND `userid`='".$uid."'");

			
			$bufferClientInfo .= mysql_num_rows($result2);
			while($row2 = mysql_fetch_array($result2)) {
				$price += $row2["total"];
			}
			$bufferClientInfo .= " (".$currencySetting["prefix"].number_format($price, 2, ".", "").$currencySetting["suffix"].")";	
			
			$bufferClientInfo .= "</td>
			</tr>
			
			<tr>
				<td>Refunded</td>
				<td>";
				$price = 0;
			$result2 = mysql_query("SELECT * FROM `tblinvoices` WHERE `status`='Unpaid' AND `userid`='".$uid."'");
			
			$bufferClientInfo .= mysql_num_rows($result2);
			while($row2 = mysql_fetch_array($result2)) {
				$price += $row2["total"];
			}
			$bufferClientInfo .= " (".$currencySetting["prefix"].number_format($price, 2, ".", "").$currencySetting["suffix"].")";	
			$bufferClientInfo .= "</td>
			</tr>
			
			<tr>
				<td>Collections</td>
				<td>";
			$price = 0;
			$result2 = mysql_query("SELECT * FROM `tblinvoices` WHERE `status`='Collections' AND `userid`='".$uid."'");
			
			$bufferClientInfo .= mysql_num_rows($result2);
			while($row2 = mysql_fetch_array($result2)) {
				$price += $row2["total"];
			}
			$bufferClientInfo .= " (".$currencySetting["prefix"].number_format($price, 2, ".", "").$currencySetting["suffix"].")";	
			$bufferClientInfo .= "</td>
			</tr>
			
			<tr>
				<td>Credit Balance</td>
				<td>".$currencySetting["prefix"].$row["credit"].$currencySetting["suffix"]."</td>
			</tr>
		</table>";
			
		}
	}

	// Script Finder
	$result = mysql_query("SELECT * FROM `chat_scripts`;");

	$countScript = 0;
	while($row = mysql_fetch_array($result)) {
		$scriptBuffer .= "<tr><td>". $row["name"] ."</td><td>". $row["description"] ."</td><td width=\"40%\"><textarea cols=\"2\" rows=\"2\" autocomplete=\"off\" class=\"scriptElementTextarea s".$countScript."\">". $row["value"] ."</textarea></td><td width=\"110px\"><input type=\"submit\" onclick=\"$.post('includes/chat/chat.php', {count: count, session: '".$chat_sess->getSession()."', data: jQuery('.scriptElementTextarea.s".$countScript."').val(), action: 'post', datatype: '1'});\" value=\"Send\" /></td></tr>";
		$countScript++;
	}
	$scriptBuffer .= "<tr><td><i>Custom Script</i></td><td colspan=\"2\"><textarea cols=\"2\" rows=\"2\" autocomplete=\"off\" class=\"scriptElementTextarea sCustom\"></textarea></td><td width=\"110px\"><input type=\"submit\" onclick=\"$.post('includes/chat/chat.php', {count: count, session: '".$chat_sess->getSession()."', data: jQuery('.scriptElementTextarea.sCustom').val(), action: 'post', datatype: '1'});\" value=\"Send\" /></td></tr>";
	// Department Finder
	$result = mysql_query("SELECT * FROM `tblticketdepartments` WHERE allowlive=1");
	$departDefaultSet = false;
	while($row = mysql_fetch_array($result)) {
		$departmentBuffer .= "<option value=\"".$row["id"]."\">".$row["name"]."</option>";
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

$smarty->assign("chat_settings", $chat_settings);
$smarty->assign("uid", $uid);
$smarty->assign("utype", $utype);
$smarty->assign("bufferClientInfo", $bufferClientInfo);
$smarty->assign("scriptBuffer", $scriptBuffer);
$smarty->assign("departmentBuffer", $departmentBuffer);
$smarty->assign("secret", $secret);
$smarty->assign("upload_id", $upload_id);
$smarty->assign("chatSessionID", $chat_sess->getSession());
$smarty->assign("LANG", $_LANG);
$smarty->assign("SESSION", $_SESSION);

$smarty->display("chat/".$chat_settings["template"]."/chatwindow.tpl");
?>