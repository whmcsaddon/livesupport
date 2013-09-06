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
$x = 0;
$dir = "";
foreach($directoryFinder as $pathPart) {
	if ($x < count($directoryFinder)-2) {
		if ($pathPart != "") {
				$dir .= "/".$pathPart;
		}
	} else {
		$dir .= "/";
		break;	
	}
	$x++;
}
$dir = __DIR__."/../../../";
if (!isset($chat_settings)) {
	  $result2 = mysql_query("SELECT * FROM `chat_settings`");
	  while($row = mysql_fetch_array($result2)) {
		  $chat_settings[$row[0]] = $row[1];
	  }
}

if ($_POST["action"] == "save") {
	saveSetting($chat_settings["AdminDisplayName"], "AdminDisplayName");
	saveSetting($chat_settings["chatTitleMessage"], "chatTitleMessage");
	saveSetting($chat_settings["chatTitleNewMessage"], "chatTitleNewMessage");
	saveSetting($chat_settings["operatorConnectedMessage"], "operatorConnectedMessage");
	saveSetting($chat_settings["clientConnectedMessage"], "clientConnectedMessage");
	saveSetting($chat_settings["ClientDisplayName"], "ClientDisplayName");
	saveSetting($chat_settings["connectMessage"], "connectMessage");
	saveSetting($chat_settings["licensekey"], "licensekey");
	saveSetting($chat_settings["offlineDisplay"], "offlineDisplay");
	saveSetting($chat_settings["onlineDisplay"], "onlineDisplay");
	saveSetting($chat_settings["uploadEnabled"], "uploadEnabled");
	saveSetting($chat_settings["uploadPath"], "uploadPath");
	saveSetting($chat_settings["defaultDepartment"], "defaultDepartment");
	saveSetting($chat_settings["timeout"], "timeout");
	saveSetting($chat_settings["template"], "template");
	saveSetting($chat_settings["defaultLang"], "defaultLang");
	saveSetting($chat_settings["adminHTML"], "adminHTML", "<script><b><u><div><i><strong><span>");
	saveSetting($chat_settings["skipQuestions"], "skipQuestions");

	foreach ($_POST["departments"] as $department) {
		
		if ($department["live"] == "on") {
			saveDepartments($department["id"], 1);
		} else {
			saveDepartments($department["id"], 0);	
		}
	}
	
	foreach ($_POST["script"] as $script) {
		saveScripts($script["id"], $script["value"], $script["description"]);
		if ($script["delete"] == "on") {
			$query2 = "DELETE FROM `chat_scripts` WHERE `id`='".$script["id"]."'";
			$result = mysql_query($query2) or die(mysql_error());
		}
	}
	
	if ($_POST["customName"] != "" && $_POST["customDescription"] != "" && $_POST["customScript"] != "") {
		saveCustomScript($_POST["customName"], $_POST["customScript"], $_POST["customDescription"]);
	}
}

function saveSetting(&$setting, $settingName, $oValue="") {
	if (isset($setting)) {
		if ($oValue != "") {
			$data = $oValue;
		} else {
			$data = htmlspecialchars_decode($_POST[$settingName]);
		}
		$query2 = "UPDATE `chat_settings` SET `value`='".mysql_real_escape_string($data)."' WHERE `setting`='".mysql_real_escape_string($settingName)."'";
		$result = mysql_query($query2) or die(mysql_error());
	} else {
		if ($oValue != "") {
			$data = $oValue;
		} else {
			$data = htmlspecialchars_decode($_POST[$settingName]);
		}
		$query2 = "INSERT INTO `chat_settings` (`setting`, `value`) VALUES ('".mysql_real_escape_string($settingName)."', '".mysql_real_escape_string($data)."')";
		$result = mysql_query($query2) or die(mysql_error());
	}
	$setting = $data;
}

function saveCustomScript($name, $value, $description) {
	$data = htmlspecialchars_decode($value);
	$query2 = "INSERT INTO `chat_scripts` (`name`, `description`, `value`) VALUES ('".mysql_real_escape_string($name)."', '".mysql_real_escape_string($description)."', '".mysql_real_escape_string($data)."')";
	$result = mysql_query($query2) or die(mysql_error());
}

function saveScripts($id, $value, $description) {
	$data = htmlspecialchars_decode($value);
	$query2 = "UPDATE `chat_scripts` SET `value`='".mysql_real_escape_string($data)."', `description`='".mysql_real_escape_string($description)."' WHERE `id`='".mysql_real_escape_string($id)."'";
        $result = mysql_query($query2) or die(mysql_error());
}

function saveDepartments($id, $allow) {
	$query2 = "UPDATE `tblticketdepartments` SET `allowlive`='".mysql_real_escape_string($allow)."' WHERE `id`='".mysql_real_escape_string($id)."'";
	$result = mysql_query($query2) or die(mysql_error());
}
?>

<style type="text/css">
	.chat_settings textarea {
		width: 250px;
		height: 100px;
	}

	.chat_settings input {
		width: 200px;
	}
	
	.chat_settings {
		width: 100%;
		background-color: #E1E1E1;
		border: 1px solid #999;
		padding: 5px;
		border-collapse:collapse;
	}

	.chat_settings tr:nth-child(even) {
		background: #E1E1E1;
	}
	
	.chat_settings tr:nth-child(odd) {
		background: #F8F8F8;
	}
	

	.chat_settings_depart {
		background-color: #E1E1E1;
		border: 1px solid #999;
		padding: 5px;
		border-collapse:collapse;
	}
	
	.chat_settings_depart tr:nth-child(even) {
		background: #E1E1E1;
	}
	
	.chat_settings_depart tr:nth-child(odd) {
		background: #F8F8F8;
	}
	
	.chat_settings td {
		padding: 4px;
	}
	
	.chat_settings td.checkbox {
		width: 25px;
	}
	
	.chat_settings_header {
		font-size: 14px;
		margin-top: 10px;
		margin-bottom: 2px;
		font-weight: bold;
	}
</style>
<div class="nodepartments errorbox">
	<span class="title">No Departments Found</span><br />
	It seems you have not setup any departments yet. This addon integrates directly with the WHMCS department system.
	Please <a href="configticketdepartments.php">setup your departments</a>.
</div>

<form method="post" action="<?= $modulelink; ?>&page=settings">
<input type="hidden" name="action" value="save" />
<div class="chat_settings_header">General Settings</div>
<table class="chat_settings">
	<tr style="display:none;">
		<td valign="top">
			License:
		</td>
		<td valign="top">
			<input type="text" disabled name="licensekey" value="<?= $chat_settings["licensekey"]; ?>" />
		</td>
        <td valign="top"><strong>No longer required.</strong></td>
	</tr>
    
    <tr>
		<td valign="top">
			Upload Path:
		</td>
		<td valign="top">
			<input type="text" name="uploadPath" value="<?= $chat_settings["uploadPath"]; ?>" />
		</td>
        <td valign="top">The path to upload files to.<br /><sup>It is recommended to upload to an offline directory.</sup></td>
	</tr>
    
    <tr>
		<td valign="top">
			Allow Client Upload:
		</td>
		<td valign="top">
			<select name="uploadEnabled">
            	<option value="1"<?php if ($chat_settings["uploadEnabled"] == 1) { echo " selected=\"selected\""; } ?>>Yes</option>
                <option value="0"<?php if ($chat_settings["uploadEnabled"] == 0) { echo " selected=\"selected\""; } ?>>No</option>
            </select>
		</td>
        <td valign="top">Do you want to allow your clients to upload a file?</td>
	</tr>
    
    <tr>
		<td valign="top">
			Default Language:
		</td>
		<td valign="top">
        	<select name="defaultLang">
            
        <?php
$langdir = "lang/chat/";
if (is_dir($dir.$langdir)) {
    if ($dh = opendir($dir.$langdir)) {
        while (($file = readdir($dh)) !== false) {
        	if ($file != "." && $file != "..") {
        		echo "<option value=\"".basename($file, ".php")."\"";
				if ($chat_settings["defaultLang"] == basename($file, ".php"))
					echo " selected=\"selected\"";
				echo ">".basename($file, ".php")."</option>";
			}
        }
        closedir($dh);
    }
}
		?>
        	</select>
		</td>
        <td valign="top">
			Select a default language if the users language is unavailable.
		</td>
	</tr>
    
	<tr>
		<td valign="top">
			Template:
		</td>
		<td valign="top">
        <select name="template">
            
        <?php
$templatedir = "templates/chat/";
if (is_dir($dir.$templatedir)) {
    if ($dh = opendir($dir.$templatedir)) {
        while (($file = readdir($dh)) !== false) {
        	if ($file != "." && $file != "..") {
        		echo "<option value=\"".$file."\"";
				if ($chat_settings["template"] == $file)
					echo " selected=\"selected\"";
				echo ">".$file."</option>";
			}
        }
        closedir($dh);
    }
}
		?>
        	</select>
			<!--<input type="text" name="template" value="<?= $chat_settings["template"]; ?>" />-->
		</td>
        <td valign="top">
			Select your chat template.
		</td>
	</tr>
    
	<tr>
		<td valign="top">
			Timeout:
		</td>
		<td valign="top">
			<input type="text" name="timeout" value="<?= $chat_settings["timeout"]; ?>" />
		</td>
        <td valign="top">
			Automatically timeout the client if the conversation has not been answered.<br /><sup>1000 = 1 Second</sup>
		</td>
	</tr>
    
	<tr>
		<td valign="top">
			Chat Window Title:
		</td>
		<td valign="top">
			<input type="text" name="chatTitleMessage" value="<?= $chat_settings["chatTitleMessage"]; ?>" />
		</td>
        <td valign="top">
			This is the title of the main chat console.
		</td>
	</tr>
	
	<tr>
		<td valign="top">
			Chat Window Title New Message:
		</td>
		<td valign="top">
			<input type="text" name="chatTitleNewMessage" value="<?= $chat_settings["chatTitleNewMessage"]; ?>" />
		</td>
        <td valign="top">
			This is the title of the main chat console when a new message is received.
		</td>
	</tr>
    
    <tr>
		<td valign="top">
			Admin Display Name Format:
		</td>
		<td valign="top">
			<select name="AdminDisplayName">
				<option value="fl"<?php if ($chat_settings["AdminDisplayName"] == "fl") { echo " selected=\"selected\""; } ?>>First Name Last Name</option>
				<option value="lf"<?php if ($chat_settings["AdminDisplayName"] == "lf") { echo " selected=\"selected\""; } ?>>Last Name First Name</option>
				<option value="f"<?php if ($chat_settings["AdminDisplayName"] == "f") { echo " selected=\"selected\""; } ?>>First Name</option>
				<option value="l"<?php if ($chat_settings["AdminDisplayName"] == "l") { echo " selected=\"selected\""; } ?>>Last Name</option>
			</select>
		</td>
        <td valign="top">
			This is how your operators name is displayed in the chat console.
		</td>
	</tr>
	
	<tr>
		<td valign="top">
			Client Display Name Format:
		</td>
		<td valign="top">
			<select name="ClientDisplayName">
				<option value="fl"<?php if ($chat_settings["ClientDisplayName"] == "fl") { echo " selected=\"selected\""; } ?>>First Name Last Name</option>
				<option value="lf"<?php if ($chat_settings["ClientDisplayName"] == "lf") { echo " selected=\"selected\""; } ?>>Last Name First Name</option>
				<option value="f"<?php if ($chat_settings["ClientDisplayName"] == "f") { echo " selected=\"selected\""; } ?>>First Name</option>
				<option value="l"<?php if ($chat_settings["ClientDisplayName"] == "l") { echo " selected=\"selected\""; } ?>>Last Name</option>
			</select>
		</td>
        <td valign="top">
			This is how your clients name is displayed in the chat console.
		</td>
	</tr>
	
	<tr>
		<td valign="top">
			Client Connected Message:
		</td>
		<td valign="top">
			<textarea name="clientConnectedMessage"><?= $chat_settings["clientConnectedMessage"]; ?></textarea>
		</td>
        <td valign="top">
			This is the message the operator sees when the client joins the conversation.<br />
            <sup>%FIRSTNAME% : Firstname from database<br />%LASTNAME% : Lastname from database<br />%ENTEREDNAME% : Name from the information filled out initially</sup>
		</td>
	</tr>
	
	<tr>
		<td valign="top">
			Operator Connected Message:
		</td>
		<td valign="top">
			<textarea name="operatorConnectedMessage"><?= $chat_settings["operatorConnectedMessage"]; ?></textarea>
		</td>
        <td valign="top">
			This is the message the client sees when an operator joins the conversation.
            <br />
            <sup>%FIRSTNAME% : Firstname from database<br />%LASTNAME% : Lastname from database</sup>
		</td>
	</tr>
	
	<tr>
		<td valign="top">
			Live Support Online:
		</td>
		<td valign="top">
			<textarea name="onlineDisplay"><?= $chat_settings["onlineDisplay"]; ?></textarea>
		</td>
        <td valign="top">
			If your operators are online this is the html displayed on your website to initiate chat.
		</td>
	</tr>
	
	<tr>
		<td valign="top">
			Live Support Offline:
		</td>
		<td valign="top">
			<textarea name="offlineDisplay"><?= $chat_settings["offlineDisplay"]; ?></textarea>
		</td>
        <td valign="top">
			If your operators are offline this is the html displayed on your website to initiate chat.
		</td>
	</tr>

	<tr>
		<td valign="top" align="center" colspan="3">
			<input type="submit" value="Save &amp; Update" />
		</td>
	</tr>
</table>

<div class="chat_settings_header">Department Configurations</div>
<table class="chat_settings_depart" style="width: 400px;">
	<tr>
		<td valign="top" align="center">
			<strong>Name</strong>
		</td>
		<td valign="top" align="center">
			<strong>Viewable</strong>
        </td>
        <td align="center">
        	<strong>Default</strong>
        </td>
	</tr>
    
<?php
$departments_visible = false;
	$result = mysql_query("SELECT * FROM `tblticketdepartments`");
	while($row = mysql_fetch_array($result)) {
		$departments_visible = true;
		
?>
	<tr>
		<td valign="top">
        	<?php echo "<input type=\"hidden\" name=\"departments[".$row["id"]."][id]\" value=\"".$row["id"]."\" />"; ?>
			<?= $row["name"]; ?>
		</td>
		<td valign="top" align="center">
			<input type="checkbox" name="departments[<?= $row["id"]; ?>][live]"<?php if ($row["allowlive"] == 1) { echo " checked=\"checked\""; }?> />
        </td>
        <td style="width: 100px;" align="center">
			<input type="radio" name="defaultDepartment" value="<?= $row["id"]; ?>" <?php if ($chat_settings["defaultDepartment"] == $row["id"]) { echo "checked"; } ?>/>
        </td>
	</tr>
    
<?php
	}

	if (!$departments_visible) {
		?>
		<tr>
    	<td colspan="3">
        <i>No departments found.</i>
        </td>
    </tr>
		<?php
	} else {
		?>
		<style>.nodepartments { display: none;	}</style>
		<tr>
    	<td colspan="3">
        <input type="checkbox" name="skipQuestions"<?php if (strtolower($chat_settings["skipQuestions"]) == "on") { echo " checked=\"checked\""; }?> /> Skip selection when user is logged in.
        </td>
    </tr>
	<tr>
		<td valign="top" align="center" colspan="3">
			<input type="submit" value="Save &amp; Update" />
		</td>
	</tr>
		<?php
	}
?>
	
</table>

<div class="chat_settings_header">Pre-Defined Scripts</div>
<table class="chat_settings">
	<tr>
		<td valign="top" align="center">
			<strong>Name</strong>
		</td>
		<td valign="top" align="center">
			<strong>Description</strong>
        </td>
        <td valign="top" align="center">
        	<strong>Script</strong>
        </td>
        <td valign="top" align="center">
        	<strong>Delete?</strong>
        </td>
	</tr>
    
<?php
$result = mysql_query("SELECT * FROM `chat_scripts`");

$countScript = 0;
while($row = mysql_fetch_array($result)) {

?>
	<tr>
		<td valign="top">
			<?= $row["name"]; ?>
            <input type="hidden" name="script[<?= $row["id"]; ?>][id]" value="<?= $row["id"]; ?>" />
		</td>
		<td valign="top">
			<textarea name="script[<?= $row["id"]; ?>][description]"><?= $row["description"]; ?></textarea>
        </td>
        <td valign="top">
        	<textarea name="script[<?= $row["id"]; ?>][value]"><?= $row["value"]; ?></textarea>
        </td>
        <td valign="top" class="checkbox">
        	<input type="checkbox" name="script[<?= $row["id"]; ?>][delete]" />
        </td>
	</tr>
<?php
}
?>

	<tr>
		<td>
			<input type="text" name="customName" />
		</td>
		<td>
			<textarea name="customDescription"></textarea>
        </td>
        <td>
        	<textarea name="customScript"></textarea>
        </td>
        <td>
        
        </td>
	</tr>

	<tr>
		<td valign="top" align="center" colspan="4">
			<input type="submit" value="Save &amp; Update" />
		</td>
	</tr>
</table>


</form>