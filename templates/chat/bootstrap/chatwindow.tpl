<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8"> 
<title>{$chat_settings.chatTitleMessage}</title>

<link rel="stylesheet" href="templates/chat/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="templates/chat/bootstrap/css/bootstrap-theme.css">

<link rel="stylesheet" type="text/css" href="templates/chat/bootstrap/css/chat.css" />
</head>

<body>


<header class="navbar navbar-fixed-top" role="banner">
  <div class="container">
  	
    <nav class="" role="navigation">
      <ul class="nav navbar-tab navbar-nav" id="tabmenu">
        <li class="active">
          <a href="#chat">{$LANG.chattab}</a>
        </li>
        {if $utype == 2}
        <li>
          <a href="#client">{$LANG.clienttab}</a>
        </li>
        <li>
          <a href="#notes">{$LANG.notetab}</a>
        </li>
        <li>
          <a href="#scripts">{$LANG.scripttab}</a>
        </li>
        {/if}
      </ul>
    </nav>
    
    <div class="btn btn-default pull-right" id="soundSetter"><span class="glyphicon glyphicon-volume-up"></span></div>
    {if $utype == 2}<a data-toggle="modal" href="#transfer-client-modal" class="btn btn-default pull-right" id="transfer"><span class="glyphicon glyphicon-transfer"></span></a>{/if}
    <div id="writingMessage" class="pull-right"></div>
  </div>
</header>

<div class="tab-content header-margin-space">
  <div class="tab-pane active" id="chat">
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
    </div>
	<div class="navbar-fixed-bottom" id="chatInput">                
		<textarea id="chatMessage" class="writemessage" cols="2" rows="2"></textarea>
		<div class="btn btn-primary" id="sendMessage">Send</div> <div data-toggle="modal" href="#upload-modal" class="btn btn-default"><span class="glyphicon glyphicon-upload"></span></div>
    </div>
  </div>
  {if $utype == 2}
  <div class="tab-pane" id="notes">
  	<div class="mainbox">
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
    </div>
    <div class="navbar-fixed-bottom" id="chatNotes">                
		<textarea id="noteElementTextarea" class="writemessage" cols="2" rows="2"></textarea>
		<div class="btn btn-primary" id="noteSubmit">Post</div>
    </div>
  </div>
  <div class="tab-pane" id="client" style="height: 504px;">
	<div class="mainbox" style="height: 504px;">
		<div id="chatClient">
			<div id="clientViewHolder">
				{$bufferClientInfo}
			</div>
		</div>
	</div>
  </div>
  <div class="tab-pane" id="scripts">
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
  </div>
  {/if}
</div>

<div id="soundplayer"></div>

{if $utype == 2 || $chat_settings.uploadEnabled == "1"}
<div class="modal fade" id="upload-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Upload File</h4>
      </div>
      <div class="modal-body">
        <div id="chatUpload">
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
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
{/if}

{if $utype == 2}
<div class="modal fade" id="transfer-client-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Transfer</h4>
      </div>
      <div class="modal-body">
        <div id="chatTransfer">
	    	Select a department: 
	    	<select id="transfer">
				{$departmentBuffer}
	        </select>
	        <input type="button" class="btn btn-primary btn-sm" value="Transfer" id="transferButton" />
	        <br /><br />
	        {$LANG.transfer}<br />
	        <div style="text-align:right;"><input type="button" class="btn btn-default btn-sm" value="Cancel Transfer" id="transferCancelButton" /></div>
	        
	        
	    </div>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
{/if}
{literal}
<script src="includes/jscript/jquery.js" type="text/javascript"></script>
<script src="templates/chat/bootstrap/js/bootstrap.min.js"></script>
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
    {literal}
	
	$(".upload-progress").hide();

	$("#file1").change(function() {	
		$('#upload_form').submit();
	});
	{/literal}
{/if}{literal}
	$(".write").click(function () {
		lowerTabSwitch();
		$(this).addClass("active");
		rightTab = "write";
		$("#chatInput").fadeIn("slow");
        if (leftTab != "client" && leftTab != "chat")
        	$(".chat").click();
	});

	{/literal}
    
{if $utype == 2}{literal}
    getNoteUpdates();
{/literal}{/if}
{literal}
	$("#soundSetter").click(function () {
    	if (playSound == 0) {
        	$("#soundSetter span").addClass("glyphicon-volume-up");
        	$("#soundSetter span").removeClass("glyphicon-volume-off");
            playSound = 1;
        } else if (playSound == 1) {
        	$("#soundSetter span").removeClass("glyphicon-volume-up");
        	$("#soundSetter span").addClass("glyphicon-volume-off");
            playSound = 0;
        }
		
    });	
}


$('#tabmenu a').click(function (e) {
  e.preventDefault()
  $(this).tab('show')
})
</script>
{/literal}

</body>
</html>