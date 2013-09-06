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
header("Content-Type: text/javascript");

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
	/*echo "alert('";
	print_r($chat_settings);
	echo "');";*/
?>
var count = 0;
var t;

var noteTime = 0;
var noteT;

var titleDefault = "<?= $chat_settings["chatTitleMessage"]; ?>";
var titleNew = "<?= $chat_settings["chatTitleNewMessage"]; ?>";

var dbErrorFix = "<div style=\"border: 1px dashed #cc0000;font-family:Tahoma;background-color:#FBEEEB;width:100%;padding:10px;color:#cc0000;\"><strong>Critical Error</strong><br>Could not connect to the database</div>"; //"<div style=\"border: 1px dashed rgb(204, 0, 0); font-family: Tahoma; background-color: rgb(251, 238, 235); width: 100%; padding: 10px; color: rgb(204, 0, 0);\"><strong>Critical Error</strong><br>Could not connect to the database</div>";

<?php /* Javascript Resize Functions
var resizeT;
var resizeSetting;
var resizeHeightSubtraction;
*/ ?>
var leftTab = "chat";
var rightTab = "write";

var connectorTimer;
var connected = <?php
if ($utype == 2) {
echo "true";	
} else {
echo "false";	
}
?>;
var currentSession = "<?= $_GET["currentSession"]; ?>";<?php
require_once("../chat/chatSession.php");
$chat_session = new chatSession();

if ($utype == 0) {
	$chat_session->useSession($_GET["currentSession"]);
	echo "\nvar username = \"".$chat_session->getName()."\";";
} elseif ($utype == 1) {
	$chat_session->getUID();
	$userResult = mysql_query("SELECT * FROM `tblclients` WHERE `id`='".$uid."';");
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
	echo "\nvar username = \"".$uname."\";";
} elseif ($utype == 2) {	
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
	echo "\nvar username = \"".$uname."\";";
} else {
	echo "\nvar username = \"No Name\";";
}
?>


var windowFocus = true;
var windowFocusT;
var windowFocusNew = true;

var tabFocus = true;

var jQue = false;

var isIE = (navigator.appName == "Microsoft Internet Explorer");


function getCheckConnectionState() {
	if (!connected) {
    	window.location.assign("leavemessage.php");
    }
}

function isWritingMessage() {
	var istyping = document.getElementById('chatMessage').value;
	if (istyping != "") {
    	return true;
    } else {
    	return false;
    }
}

function enableWritingIcon() {
	jQuery("#writingMessage").html("<img src=\"images/chat/pencil.jpg\" />");
}

function disableWritingIcon() {
	jQuery("#writingMessage").html("");
}

function getUpdates() {
	var wMess = isWritingMessage();
	if (!jQue) {
    	jQue = true;
        jQuery("#receiver").load('includes/chat/chat.php', {count: count, session: currentSession, action: "view", wmessage: wMess}, function(responseText, textStatus, XMLHttpRequest) {
            var scrollBottom = false;
            if (jQuery("#receiver").text() != "" && jQuery("#receiver").html() != dbErrorFix) {               
                
                if( jQuery("#liveupdate")[0].scrollTop == (jQuery("#liveupdate")[0].scrollHeight - jQuery("#liveupdate")[0].offsetHeight)) {
                    scrollBottom = true;
                }
                
                jQuery("#liveupdate").append(jQuery("#receiver").html());
                if (scrollBottom) {
                    jQuery("#liveupdate").animate({scrollTop: jQuery("#liveupdate")[0].scrollHeight});
        
                }
                

                if (!windowFocus || !tabFocus) {
                    flashTitle();
                    if (playSound == 1)
                        jQuery.sound.play("includes/media/Message.mp3", {timeout: 2000});
                }
                
                count++;
            }
            jQue = false;
            t=setTimeout("getUpdates();",750);
            
        });
    }
	
	jQuery('#liveupdate').linkify();
	jQuery('#liveupdate a').attr({
		target: '_blank'
	});
}

function flashTitle() {
	if (!windowFocus && !isIE || !tabFocus && !isIE) {
    	if (!windowFocusNew) {
        	document.title = titleDefault;
            windowFocusNew = true;
        } else {
			document.title = titleNew;
            windowFocusNew = false;
        }
		windowFocusT=setTimeout("flashTitle();",1000);
	} else {
        document.title = titleDefault;
    }
    
}

<?php if ($utype == 2) { ?>
function getNoteUpdates() {
	jQuery("#noteReceiver").load('includes/chat/notes.php', {noteTime: noteTime, session: currentSession, action: "view"}, function(responseText, textStatus, XMLHttpRequest) {
		if (jQuery("#noteReceiver").html() != "" && jQuery("#noteReceiver").html() != dbErrorFix || responseText != "" && responseText != dbErrorFix) {
			var appendData = jQuery("#noteReceiver").html();
			var scrollBottom = false;
			if( jQuery("#noteViewHolder")[0].scrollTop == (jQuery("#noteViewHolder")[0].scrollHeight - jQuery("#noteViewHolder")[0].offsetHeight)) {
				scrollBottom = true;
			}
			jQuery(".noteTable").append(appendData);
			if (scrollBottom) {
				jQuery("#noteViewHolder").animate({scrollTop: jQuery("#noteViewHolder")[0].scrollHeight});
			}
			noteTime++;
		}
		noteT=setTimeout("getNoteUpdates();",60000);
	
	});
	
	jQuery('.noteTable').linkify();
	jQuery('.noteTable a').attr({
		target: '_blank'
	});
}
<?php } ?>

<?php /* Javascript Resize Functions
function resizeIt() {
	if (resizeSetting == "true") {
		var liveHeight = jQuery(document).height();
		liveHeight = liveHeight-resizeHeightSubtraction;
		jQuery("#liveupdate").css({'height': liveHeight});
		resizeT=setTimeout("resizeIt();",500);
	}
}
*/ ?>

<?php
if ($utype == 2 || $chat_settings["uploadEnabled"] == "1") {
?>
jQuery('#upload_form').uploadProgress({
	updateDelay:1000,
	progressURL:'includes/chat/uploadTracker.php',
	displayFields : ['kb_uploaded','kb_average','est_sec'],
	progressMeterSpeed: 250,
	progressMeter: ".meter.1",
	start: function() { jQuery('#file1').hide(); jQuery('.upload-progress').show(); },
	success: function(o) { 
		jQuery(this).get(0).reset();

        if (uploadCompletedData['error'] == 0) {
            if (uploadCompletedData['filename'].substring(0, 23) != uploadCompletedData['filename']) {
                var filename = uploadCompletedData['filename'].substring(0, 20) + "...";
            } else {
                var filename = uploadCompletedData['filename'];
            }
            jQuery.post('includes/chat/chat.php', {count: count, session: currentSession, data: "Download File:<div style=\"margin-left: 30px;\"><a href=\"downloadUpload.php?filename="+escape(uploadCompletedData['filename'])+"&timestamp="+uploadCompletedData['timestamp']+"\" target=\"download_location\"><img src=\"images/chat/"+uploadCompletedData['icon']+".gif\" border=\"0\" style=\"width: 32px; height: 30px; vertical-align: middle;\" /> "+uploadCompletedData['filename']+"."+uploadCompletedData['ext']+"</a></div>", action: "post", datatype: 2});
		}
        jQuery('#file1').show();
		jQuery('.upload-progress').hide();
  
	}
});
<?php } ?>

function unload() {
	jQuery.ajaxSetup({async : false});
    jQuery.post('includes/chat/clientUnloadChat.php', {count: count, session: currentSession, user: username, action: "post", datatype: 3});
};



jQuery(document).ready(function() {	
	jQuery("#sendMessage").click(function () {
		jQuery.post('includes/chat/chat.php', {count: count, session: currentSession, data: jQuery("#chatMessage").val(), user: username, action: "post", datatype: 0});
		jQuery("#chatMessage").val('');
        getUpdates();
	});
	
	jQuery('#chatMessage').keydown(function(event) {
		if (event.keyCode == '13' && event.ctrlKey) {
			jQuery("#chatMessage").val(jQuery("#chatMessage").val()+"\n");
		} else if (event.keyCode == '13') {
			event.preventDefault();
			jQuery("#sendMessage").click();
		}
	});

<?php if ($utype == 2) { ?>
	jQuery("#noteSubmit").click(function () {
		jQuery.post('includes/chat/notes.php', {count: count, session: currentSession, data: jQuery("#noteElementTextarea").val(), action: "post"});
		jQuery("#noteElementTextarea").val('');
        getNoteUpdates();
	});

	jQuery('#noteElementTextarea').keydown(function(event) {
		if (event.keyCode == '13' && event.ctrlKey) {
			jQuery("#noteElementTextarea").val(jQuery("#noteElementTextarea").val()+"\n");
		} else if (event.keyCode == '13') {
			event.preventDefault();
			jQuery("#noteSubmit").click();
		}
	});
    
    jQuery("#transferButton").click(function () {
        jQuery.post('includes/chat/transfer.php', {count: count, session: currentSession, department: jQuery('#transfer').val(), departmentName: jQuery('#transfer :selected').text(), action: "post", datatype: 4});
    });
    
    jQuery("#transferCancelButton").click(function () {
        jQuery.post('includes/chat/transfer.php', {count: count, session: currentSession, department: jQuery('#transfer').val(), departmentName: jQuery('#transfer :selected').text(), action: "post", datatype: 5});
    });
<?php } ?>

    
    jQuery(window).focus(function () {
    	windowFocus = true;
        document.title = titleDefault;
    });
    
    jQuery(window).blur(function () {
    	windowFocus = false;
    });
      
    
    <?php if ($utype != 2) { ?>

	jQuery(window).unload(function () {
		unload();
	});
    <?php } ?>
    <?php if ($_GET["secretjoin"] != "true" && $utype == 2) { ?>
    jQuery.post('includes/chat/chat.php', {count: count, session: currentSession, data: "%operatorConnectedMessage%", user: username, action: "post", datatype: 3});
	<?php } elseif ($utype == 0 || $utype == 1) { ?>
     jQuery.post('includes/chat/chat.php', {count: count, session: currentSession, data: "%clientConnectedMessage%", user: username, action: "post", datatype: 3});   
    <?php } ?>
    connectorTimer = setTimeout("getCheckConnectionState();",<?= $chat_settings["timeout"] ?>);
    getUpdates();

});