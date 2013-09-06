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

function ipcode($ip) {   
	$numbers = preg_split( "/\./", $ip);   
	$code=($numbers[0] * 16777216) + ($numbers[1] * 65536) + ($numbers[2] * 256) + ($numbers[3]);   

	return $code;
}


$result = mysql_query("SELECT * FROM `tbladmins` WHERE `id`='".$_SESSION["adminid"]."'");
while($row = mysql_fetch_array($result)) {
	$user = $row;
}

$viewDept = explode(",", $user["supportdepts"]);

$result = mysql_query("SELECT * FROM `tblticketdepartments`");
$x = 0;
$dept = array();
while($row = mysql_fetch_array($result)) {
	$dept[$x] = $row;
	$x++;
}

$withinFive = time() - 300;

$result = mysql_query("SELECT * FROM `chat_ban`");
$x = 0;
$ban = array();
while($row = mysql_fetch_array($result)) {
	$ban[$x] = $row["ip"];
}

if ($_GET["method"] == "current") {
	$result = mysql_query("SELECT * FROM `chat_sessions` WHERE `active`='0' AND NOT `utype`='2' ORDER BY `timestamp` DESC, `active` ASC");
} elseif ($_GET["method"] == "monitor") {
	$result = mysql_query("SELECT * FROM `site_activitylogs` WHERE `lastaccess` >= ".$withinFive." ORDER BY `uid` DESC,`id` ASC");
} else {
	$result = mysql_query("SELECT * FROM `chat_sessions` WHERE `timestamp` >= ".$withinFive." AND `active` = '1' ORDER BY `timestamp` DESC, `active` ASC");
}

while($row = mysql_fetch_array($result)) {
	if ($_GET["method"] == "monitor") {
		$actuallyRan = true;
		
?>
	<table class="monitorTable monitorContent">
		  <tr>
			<td class="monitorName"><?php
			$run = false;
			
			if ($row["uid"] > 0) {
				$result2 = mysql_query("SELECT * FROM `tblclients` WHERE `id`=".$row["uid"]);
				
				while ($row2 = mysql_fetch_array($result2)) {
					echo "<a href='clientssummary.php?userid=".$row["uid"]."' target='_blank'><img src=\"images/icons/clientsprofile.png\" border=\"0\" /> ".$row2["firstname"]." ".$row2["lastname"]."</a>";
					$run = true;
				}
	
			}
			
			if ($run == false) {				
				echo "<i>Guest</i>";
			}
			
			?></td>
			<td class="monitorDepartment"><?= $row["ip"]; ?></td>
			<td class="monitorQuestion"><?php
			$pages = explode("|", $row["pages"]);
			echo $pages[count($pages)-1];
			?></td>
			<td class="monitorStatus"><?php
			$timestamps = explode("|", $row["timestamps"]);
			$firstTime = explode(",", $timestamps[0]);
			//$lastTime = explode(",", $timestamps[count($timestamps)-1]);
			$timeFinal = ($row["lastaccess"]-$firstTime[0]);
			$hours = floor($timeFinal/3600);
			$timeFinal = $timeFinal - ($hours * 3600);
			$minutes = floor($timeFinal/60);
			$timeFinal = $timeFinal - ($minutes * 60);        
			$seconds = $timeFinal;
			if ($minutes < 10)
				$minutes = "0".$minutes;
			if ($seconds < 10)
				$seconds = "0".$seconds;
			echo $hours.":".$minutes.":".$seconds;
			// 7 days; 24 hours; 60 mins; 60secs
			
			?></td>
			<td class="monitorBetween"></td>
			<td class="monitorActions">
				<img class="injectScriptButton <?= $row["id"]; ?>" src="images/script.jpg" title="Inject Script" alt="Inject Script" onclick="injectScript('<?= $row["session"]; ?>', '.injectScriptButton.<?= $row["id"]; ?>')" onmouseover="this.src='images/script_hover.jpg';" onmouseout="this.src='images/script.jpg';" />
			</td>
			<td class="monitorBetween"></td>
			<td class="monitorMore"><?php
			$x = ipcode($row["ip"]);
			
			$result2 = mysql_query("SELECT * FROM tblgeoip WHERE ip_from <= '$x' AND ip_to >= '$x'");
			while ($row2 = mysql_fetch_array($result2)) {
				if (floatval($row2["ip_from"]) <= floatval($x) && floatval($row2["ip_to"]) >= floatval($x)) {
					echo "<img src='images/flags/".strtolower($row2["ctry"]).".gif' title='".$row2["country"]."' alt='".$row2["country"]."' />";
					break;
				}
			
			}
			
			?></td>
		  </tr>
		</table>
<?php
	} else {
		$currentDept = explode("|", $row["departments"]);
		$ignore = explode("|", $row["ignore"]);
		//print_r($user);
		//echo in_array($user["id"], $ignore);
		$run = false;
		if ($row["uid"] > 0 || $row["name"] != "") {
			$run = true;
		}
		
		$env = unserialize($row["environment"]);
		
		if (in_array($env["REMOTE_ADDR"], $ban) == 1) {
			$run = false;	
		}
		
		if (in_array($user["id"], $ignore) != 1 && $run || $_GET["method"] == "current" && $run) {
		
			if (in_array($currentDept[count($currentDept)-1], $viewDept) == 1 || $currentDept[count($currentDept)-1] == "-1") {
				//$env = unserialize($row["environment"]);
					//echo $row["session"];
					$actuallyRan = true;
	?>
	<table class="monitorTable monitorContent">
		  <tr>
			<td class="monitorName"><?php
			$run = false;
			
			if ($row["uid"] > 0) {
				$result2 = mysql_query("SELECT * FROM `tblclients` WHERE `id`=".$row["uid"]);
				
				while ($row2 = mysql_fetch_array($result2)) {
					echo "<a href='clientssummary.php?userid=".$row["uid"]."' target='_blank'><img src=\"images/icons/clientsprofile.png\" border=\"0\" /> ".$row2["firstname"]." ".$row2["lastname"]."</a>";
					$run = true;
				}
	
			}
			
			if ($run == false) {
				$result2 = mysql_query("SELECT * FROM `tblclients` WHERE `firstname`='".$row["name"]."' OR `lastname`='".$row["name"]."' OR `companyname`='".$row["name"]."' OR `email`='".$row["email"]."' OR `ip`='".$env["REMOTE_ADDR"]."'");
				while ($row2 = mysql_fetch_array($result2)) {
					echo "<img src=\"images/info.gif\" border=\"0\" title=\"Client Results Detected\" alt=\"Client Results Detected\" /> ";
					break;
				}
				
				echo $row["name"];
			}
			
			?></td>
			<td class="monitorDepartment"><?php 
				if ($currentDept[count($currentDept)-1] == "-1") {
					echo "<i>All</i>";	
				} else {
					for ($x = 0; $x < count($dept); $x++) {
						if ($dept[$x]["id"] == $currentDept[count($currentDept)-1]) {
							echo $dept[$x]["name"];
						}
					}
				}
				
			?></td>
			<td class="monitorQuestion"><?php echo $row["question"]; ?></td>
			<td class="monitorStatus"><?php
			if ($row["active"] == 1) {
				echo "Not Answered";
			} elseif ($row["active"] == 2) {
				echo "Closed";
			} else {
				echo "Answered";	
			}
			?></td>
			<td class="monitorBetween"></td>
			<td class="monitorActions">
				<img class="actionAnswer" src="images/answer.jpg" title="Answer" alt="Answer" onclick="answerCall('<?= $row["session"]; ?>'<?php if ( $_GET["method"] == "current") { echo ", true"; } ?>);" onmouseover="this.src='images/answer_hover.jpg';" onmouseout="this.src='images/answer.jpg';" />
				<?php if ($_GET["method"] != "current") { ?>
				<img class="actionIgnore" src="images/ignore.jpg" title="Ignore" alt="Ignore" onclick="ignoreCall('<?= $row["session"]; ?>');" onmouseover="this.src='images/ignore_hover.jpg';" onmouseout="this.src='images/ignore.jpg';" />
				<?php } ?>
				<img src="images/blockip.jpg" title="Block IP" alt="Block IP" onclick="blockUser('<?= $row["session"]; ?>');" onmouseover="this.src='images/blockip_hover.jpg';" onmouseout="this.src='images/blockip.jpg';" />
			</td>
			<td class="monitorBetween"></td>
			<td class="monitorMore"><?php
			$x = ipcode($env["REMOTE_ADDR"]);
			
			$result2 = mysql_query("SELECT * FROM tblgeoip WHERE ip_from <= '$x' AND ip_to >= '$x'");
			while ($row2 = mysql_fetch_array($result2)) {
				if (floatval($row2["ip_from"]) <= floatval($x) && floatval($row2["ip_to"]) >= floatval($x)) {
					echo "<img src='images/flags/".strtolower($row2["ctry"]).".gif' title='".$row2["country"]."' alt='".$row2["country"]."' />";
					break;
				}
			
			}
			
			?></td>
		  </tr>
		</table>
	<?php
			}
		}
	}
}

if ($actuallyRan != true) {
	?><table class="monitorTable monitorContent">
	  <tr><td style="padding-left: 10px;">No support requests are available at the moment.</td></tr></table><?php
}
?>