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
//@error_reporting(0);
@ini_set("register_globals", "off");
/*
This file is the main controller for operators.
1. Allows operators to view clients requesting chat
2. Shows chat statuses
3. Transfer calls
*/


# Get Variables from storage (retrieve from wherever it's stored - DB, file, etc...)
if (!isset($chat_settings)) {
	  $result2 = mysql_query("SELECT * FROM `chat_settings`");
	  while($row = mysql_fetch_array($result2)) {
		  $chat_settings[$row[0]] = $row[1];
	  }
}


?>

<style type="text/css">
#monitor {
	background-color: white;
}
#method {
	margin: 10px 0 -5px 0;
	-webkit-border-radius: 13px;
	-moz-border-radius: 13px;
	border-radius: 13px;
	border: 1px solid #D3D3D3;
	font-size: 14px;
	font-weight: bold;
	padding:5px 5px 10px 5px;
	width:205px;
}
.monitorTable {
	width: 945px;
	height: 38px;
}
.monitorHeader {
	-webkit-border-radius: 13px;
	-moz-border-radius: 13px;
	border-radius: 13px;
	-webkit-box-shadow: 0px 0px 5px #D3D3D3;
	-moz-box-shadow: 0px 0px 5px #D3D3D3;
	box-shadow: 0px 0px 5px #D3D3D3;
	background-image: -moz-linear-gradient(top, #e9e9e9, #f1f1f1);
	background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0.00, #e9e9e9), color-stop(1.0, #f1f1f1));
	border: 1px solid #D3D3D3;
	background-color: #D3D3D3;
	padding: 10px;
	color: #888888;
}
.monitorContent {
	-webkit-border-radius: 4px;
	-moz-border-radius: 4px;
	border-radius: 4px;
	border: 1px solid #D3D3D3;
	background-color: #FFFFFF;
	margin-top: 3px;
	margin-left: 10px;
	width: 925px;
}
th {
	font-size: 12px;
	font-family: arial;
	color: #5a5959;
}
.monitorHName {
	padding-left: 22px;
	text-align: left;
	width: 145px;
}
.monitorHDepartment {
	text-align: left;
	width: 154px;
}
.monitorHQuestion {
	text-align: left;
	width: 296px;
}
.monitorHStatus {
	text-align: center;
	width: 74px;
	font-size: 10px;
	font-weight: 100;
}
.monitorHActions {
	text-align: center;
	width: 190px;
	font-size: 10px;
	font-weight: 100;
}
.monitorHMore {
	text-align: center;
	width: 74px;
	font-size: 10px;
	font-weight: 100;
}
.monitorHBetween {
	color: #cacaca;
	font-size: 12px;
}

td {
	font-size: 12px;
	font-family: arial;
	color: #5a5959;
}
.monitorName {
	padding-left: 22px;
	text-align: left;
	width: 145px;
}
.monitorDepartment {
	text-align: left;
	width: 154px;
}
.monitorQuestion {
	text-align: left;
	width: 296px;
}
.monitorStatus {
	text-align: center;
	width: 74px;
	font-size: 10px;
	font-weight: 100;
}
.monitorActions {
	text-align: center;
	width: 190px;
	font-size: 10px;
	font-weight: 100;
}
.monitorMore {
	text-align: center;
	width: 74px;
	font-size: 14px;
	font-weight: bold;
}
.actionAnswer {
	color: #060;
	font-size: 12px;
}
.actionIgnore {	
	color: #900;
}
.additionalInfo {
	background-color:#f1f1f1;
	border:solid 1px #dedede;
	margin-top: 5px;
}
textarea.adminNotes {
	width: 250px;
	height: 75px;
}
#liveupdate {}

#receiver, #activereceiver {
	visibility: hidden;
	width: 0px;
	height: 0px;
	overflow: hidden;
}



.loadImage {
	margin-left: 10px;
	margin-top: 10px;
	float: right;
}

#blackFader {
	background-color:black;
	width: 1px;
	height: 1px;
	position: fixed;
	top: -500px;
	left: -500px;	
}

#chatScripts {
	background-color:#E1E1E1;
	font-size:12px;
	padding:5px;
	width:450px;
	border:1px solid #CCC;
	position: absolute;
	top: -500px;
	left: -500px;
}

.scriptTable  {
	border:1px solid #CCCCCC;
	border-collapse:collapse;
}
.scriptTable td {
	padding:5px;
	width:100px;
}
.scriptTable td textarea {
	width:100%;
}
.scriptTable td input {
	margin-left:25px;
}
.newReq, #curReq, #monitor {
	border-bottom: 1px solid #f1f1f1;
	padding-bottom: 10px;
	margin-bottom: 5px;
}
#onlineButton, #soundButton {
	display: inline-block;
	margin-right: 5px;	
	-webkit-border-radius: 7px;
	-moz-border-radius: 7px;
	border-radius: 7px;
	border: 1px solid #6A5ACD;
	background-color: #6495ED;
	padding: 9px;
	font-family: Verdana, Geneva, sans-serif;
	font-weight: bold;
	font-size: 12px;
	color: #FFFFFF;
	text-align: center;
	cursor: pointer;
}
</style>




<img src="images/loading.gif" class="loadImage" />

You're Current Settings: <div id='onlineButton'>Detecting Online State</div>
<div id='soundButton'>Detecting Sound Setting</div>

<div class="newReq">
	<div id="method">
		New Requests
	</div>
	<table class="monitorTable monitorHeader">
	  <tr>
		<th class="monitorHName">Name</th>
		<th class="monitorHDepartment">Department</th>
		<th class="monitorHQuestion">Question</th>
		<th class="monitorHStatus">Status</th>
		<th class="monitorHBetween">|</th>
		<th class="monitorHActions">Actions</th>
		<th class="monitorHBetween">|</th>
		<th class="monitorHMore">GeoIP</th>
	  </tr>
	</table>
	<div id="liveupdateNew"></div>
</div>

<div id="curReq">
	<div id="method">
		Current Requests
	</div>
	<table class="monitorTable monitorHeader">
	  <tr>
		<th class="monitorHName">Name</th>
		<th class="monitorHDepartment">Department</th>
		<th class="monitorHQuestion">Question</th>
		<th class="monitorHStatus">Status</th>
		<th class="monitorHBetween">|</th>
		<th class="monitorHActions">Actions</th>
		<th class="monitorHBetween">|</th>
		<th class="monitorHMore">GeoIP</th>
	  </tr>
	</table>
	<div id="liveupdateCur"></div>
</div>

<div id="monitor">
	<div id="method">
		Live Monitor
	</div>
	<table class="monitorTable monitorHeader">
    	<tr>
        	<th class="monitorHName">User</th>
            <th class="monitorHDepartment">IP Address</th>
            <th class="monitorHQuestion">Current Page</th>
            <th class="monitorHStatus">Total Time</th>
            <th class="monitorHBetween">|</th>
            <th class="monitorHActions">Actions</th>
            <th class="monitorHBetween">|</th>
            <th class="monitorHMore">GeoIP</th>
       </tr>
   </table>
   <div id="liveupdateMon"></div>
</div>
	<div id="blackFader" onclick="cancelInjectScript()"></div>
	<div id="chatScripts">
		<table width="100%" class="scriptTable">
			<tr>
				<th>Name</th>
                <th>Description</th>
                <th>Script Value</th>
                <th></th>
			</tr><?php
$result = mysql_query("SELECT * FROM `chat_scripts`;");

$countScript = 0;
while($row = mysql_fetch_array($result)) {
	echo "<tr><td>". $row["name"] ."</td><td>". $row["description"] ."</td><td width=\"40%\"><textarea autocomplete=\"off\" class=\"scriptElementTextarea s".$countScript."\">". $row["value"] ."</textarea></td><td width=\"110px\"><input type=\"submit\" onclick=\"sendInjectScript('.scriptElementTextarea.s".$countScript."');\" value=\"Send\" /></td></tr>";
	$countScript++;
}
?>
			<tr><td><i>Custom Script</i></td><td colspan="2"><textarea autocomplete="off" class="scriptElementTextarea sCustom"></textarea></td><td width="110px"><input type="submit" onclick="sendInjectScript('.scriptElementTextarea.sCustom');" value="Send" /></td></tr>
 		</table>
    </div>
    
<div id="receiver"></div>
<div id="activereceiver"></div>
    
<script type="text/javascript" src="../includes/jscript/adminchat.js.php"></script>