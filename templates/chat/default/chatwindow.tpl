<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" /> 
<title>{$chat_settings.chatTitleMessage}</title>

<link rel="stylesheet" type="text/css" href="templates/chat/default/chat.css" />
<!--[if lt IE 7]>	
<link rel="stylesheet" type="text/css" href="templates/chat/default/chat.ie6.css" />
<![endif]-->
</head>

<body>

<div class="tabUpper tabUpperSelected chat">{$LANG.chattab}</div>
{if $utype == 2}
<div class="tabUpper client">{$LANG.clienttab}</div>
<div class="tabUpper unotes">{$LANG.notetab}</div>
{/if}

<div id="writingMessage" style="float: right;"></div>

<div class="clear">&nbsp;</div>

<div class="mainboxLeft">&nbsp;</div>
<div class="mainbox">
    <div id="chatWindow">
        <div id="chatInnerWindow">
            <div id="receiver"></div>
            <div id="liveupdate">
			{if $utype != 2}
			 {$LANG.connectMessage}
             {/if}
			</div>
        </div>
    </div>
    {if $utype == 2}
    <div id="chatUNotes">
      <table>
      	<tr>
        <td width="50%" valign="top">
            <div id="noteViewHolder">
                <table width="100%" class="noteTable">
                    <tr>
                        <th>
                            {$LANG.noteheader}
                        </th>
                    </tr>
                </table>
            </div>
            <div id="noteReceiver"></div>
        </td>
        </tr>
      </table>
    </div>
    
    <div id="chatClient">
    	<div id="clientViewHolder">
			{$bufferClientInfo}
		</div>
    </div>
    {/if}
</div>
<div class="mainboxRight">&nbsp;</div>

<div class="clearMainbox">
    <div class="mainboxBottomLeft">&nbsp;</div>
    <div class="mainboxBottom">&nbsp;</div>
    <div class="mainboxBottomRight">&nbsp;</div>
</div>

<div class="clear">&nbsp;</div><br />

<div class="actionboxLeft">&nbsp;</div>
<div class="actionbox">
	<div id="chatInput">                
        <table>
            <tr>
                <td width="100%" valign="top">
                   <textarea id="chatMessage" class="writemessage" cols="2" rows="2"></textarea>
                </td>
                <td valign="top" align="right" width="132px">
                    <img src="images/chat/send.jpg" id="sendMessage" alt="Send Message" />
                </td>
            </tr>
        </table>
    </div>
{if $utype == 2 || $chat_settings.uploadEnabled == "1"}
    <div id="chatUpload">
    	<br />
    	<table id="chatUploadTable">
        <tr>
        <td id="chatUploadTableField">
        <form id="upload_form" action="includes/chat/uploadTracker.php" method="post" enctype="multipart/form-data">
        {if $upload_id}
        <input name="UPLOAD_IDENTIFIER" type="hidden" value="{$upload_id}" />
        {/if}
        {$LANG.uploadfile}
        <div class="upload-progress">
            <div class="meterHolder"><div class="meter 1"></div></div>
            <div class="readout">
                <span class="kb_uploaded">0</span> kb uploaded - <span class="kb_average">0</span> kb/sec <br/><span class="est_sec">0</span> seconds remain
            </div>
        </div>
        <input type="file" id="file1" name="file1" /><br /><br />
        <span style="font-size: 10px;">{$LANG.uploadtos}</span>
        </form>
        <!--</td>-->
        <!--<td id="chatUploadTableComplete">-->
        <iframe src="#" name="download_location" id="download_location" style="width: 1px; height: 1px; visibility: hidden;"></iframe><!--<div class="upload-completed"></div>-->
        </td>
        </tr>
        </table>   
    </div>
{/if}

{if $utype == 2}
	<div id="chatScripts">
		<table width="100%" class="scriptTable">
			<tr>
				<th>{$LANG.scriptname}</th>
                <th>{$LANG.scriptdescription}</th>
                <th>{$LANG.scriptvalue}</th>
                <th></th>
			</tr>
            {$scriptBuffer}
 		</table>
    </div>
    
    <div id="chatNotes">
       <table>
            <tr>
                <td width="100%" valign="top">
                   <textarea cols="2" rows="2" autocomplete="off" id="noteElementTextarea" class="writemessage"></textarea>
                </td>
                <td valign="top" align="right" width="132px">
                    <img src="images/chat/postnote.jpg" id="noteSubmit" alt="Post Note" />
                </td>
            </tr>
        </table>
    </div>
    
    <div id="chatTransfer"><br />
    	Select a department:<br />
    	<select id="transfer">
			{$departmentBuffer}
        </select>
        <input type="button" value="Transfer" id="transferButton" />
        <br />
        <span style="font-size: 10px;">{$LANG.transfer}</span><br />
        <br />
        <input type="button" value="Cancel Transfer" id="transferCancelButton" />
    </div>
{/if}
    <div id="soundplayer"></div>
</div>
<div class="actionboxRight">&nbsp;</div>

<div class="clear">&nbsp;</div>

<div id="soundSetter">{$LANG.soundtoggle}</div>
{if $utype == 2}
<div class="tabLower transfer">{$LANG.transfertab}</div>
<div class="tabLower notes">{$LANG.notetoggletab}</div>
<div class="tabLower scripts">{$LANG.scripttab}</div>
{/if}
{if $utype == 2 || $chat_settings.uploadEnabled == "1"}
<div class="tabLower upload">{$LANG.uploadtab}</div>
{/if}
<div class="tabLower tabLowerSelected write">{$LANG.writetab}</div>

{literal}
<script src="includes/jscript/jquery-1.4.1.min.js" type="text/javascript"></script>
<script src="includes/jscript/linkify.js" type="text/javascript"></script>
<script src="includes/jscript/jquery.sound.js" type="text/javascript"></script>
{/literal}
{if $utype == 2 || $chat_settings.uploadEnabled == "1"}
{literal}<script type="text/javascript" src="includes/jscript/jquery.uploadprogress.0.3.js"></script>{/literal}
{/if}
{literal}
<script type="text/javascript">
var playSound = 1;
$(document).ready(function() {
	var currentSession = "{/literal}{$chatSessionID}{literal}";
	$.ajaxSetup({cache: false});
});
</script>
{/literal}
{literal}<script type="text/javascript" src="includes/jscript/chatwindow.js.php?currentSession={/literal}{$chatSessionID}{literal}{/literal}{if $secret == "true"}+&secretjoin=true{/if}{literal}"></script>{/literal}
{literal}<script type="text/javascript">
jsLoaded();
function jsLoaded() {
resizeTo(547,626);
{/literal}
{if $utype == 2 || $chat_settings.uploadEnabled == "1"}
    {literal}$("#chatUpload").hide();
	
	$(".upload-progress").hide();

	$("#file1").change(function() {	
		$('#upload_form').submit();
	});
	$(".upload").click(function () {
		lowerTabSwitch();
		$(this).addClass("tabLowerSelected");
		rightTab = "upload";
		$("#chatUpload").fadeIn("slow");
	});{/literal}
{/if}{literal}
	$(".write").click(function () {
		lowerTabSwitch();
		$(this).addClass("tabLowerSelected");
		rightTab = "write";
		$("#chatInput").fadeIn("slow");
        if (leftTab != "client" && leftTab != "chat")
        	$(".chat").click();
	});

	{/literal}
    
{if $utype == 2}{literal}
	$("#chatScripts").hide();
	$("#chatTransfer").hide();
	$("#chatNotes").hide();
    $("#chatUNotes").hide();
    $("#chatClient").hide();

	$(".scripts").click(function () {
		lowerTabSwitch();
		$(this).addClass("tabLowerSelected");
		rightTab = "scripts";
		$("#chatScripts").fadeIn("slow");
        if (leftTab != "client" && leftTab != "chat")
        	$(".chat").click();
	});
	$(".notes").click(function () {
		lowerTabSwitch();
		$(this).addClass("tabLowerSelected");
		rightTab = "notes";
		$("#chatNotes").fadeIn("slow");
        if (leftTab != "client" && leftTab != "notes")
        	$(".unotes").click();
	});
	$(".transfer").click(function () {
		lowerTabSwitch();
		$(this).addClass("tabLowerSelected");
		rightTab = "transfer";
		$("#chatTransfer").fadeIn("slow");
	});
	
	$(".unotes").click(function () {
		upperTabSwitch();
		$(this).addClass("tabUpperSelected");
		leftTab = "notes";
		$("#chatUNotes").fadeIn("slow");
        tabFocus = false;
        if (rightTab != "notes")
        	$(".notes").click();
	});
	$(".client").click(function () {
		upperTabSwitch();
		$(this).addClass("tabUpperSelected");
		leftTab = "client";
        tabFocus = false;
		$("#chatClient").fadeIn("slow");
	});
    
    getNoteUpdates();
{/literal}{/if}
{literal}
	$(".chat").click(function () {
		upperTabSwitch();
		$(this).addClass("tabUpperSelected");
		leftTab = "chat";
		$("#chatWindow").fadeIn("slow");
        tabFocus = true;
        if (rightTab != "write" && rightTab != "scripts" && rightTab != "upload")
        	$(".write").click();
	});
	
	$("#soundSetter").click(function () {
    	if (playSound == 0) {
        	$("#soundSetter").html("{/literal}{$LANG.soundtoggle}{literal}");
            playSound = 1;
        } else if (playSound == 1) {
        	$("#soundSetter").html("{/literal}{$LANG.soundtoggleoff}{literal}");
            playSound = 0;
        }
		alert();
    });	
}

function lowerTabSwitch() {
	if (rightTab == "write") {
		$(".write").removeClass("tabLowerSelected");
		$("#chatInput").hide();
	}
	if (rightTab == "upload") {
		$(".upload").removeClass("tabLowerSelected");
		$("#chatUpload").hide();
	}
    {/literal}{if $utype == 2}{literal}
	if (rightTab == "scripts") {
		$(".scripts").removeClass("tabLowerSelected");
		$("#chatScripts").hide();
	}
	if (rightTab == "notes") {
		$(".notes").removeClass("tabLowerSelected");
		$("#chatNotes").hide();
	}
	if (rightTab == "transfer") {
		$(".transfer").removeClass("tabLowerSelected");
		$("#chatTransfer").hide();
	}
    {/literal}{/if}{literal}
}

function upperTabSwitch() {
	if (leftTab == "chat") {
		$(".chat").removeClass("tabUpperSelected");
		$("#chatWindow").hide();
	}
    {/literal}{if $utype == 2}{literal}
	if (leftTab == "client") {
		$(".client").removeClass("tabUpperSelected");
		$("#chatClient").hide();
	}
	if (leftTab == "notes") {
		$(".unotes").removeClass("tabUpperSelected");
		$("#chatUNotes").hide();
	}
    {/literal}{/if}{literal}
}
</script>{/literal}

</body>
</html>