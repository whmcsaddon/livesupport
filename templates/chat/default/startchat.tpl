<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{$LANG.starttitle}</title>
{literal}<link rel="stylesheet" type="text/css" href="templates/chat/default/chat_start.css" />{/literal}
</head>

<body>
<div class="logo"><img src="images/chat/livechat_logo.jpg" alt="WHChat Live Support" title="WHChat Live Support" /></div>
<div style="clear: both;"></div>

{if $error == "invalid"}
	<div class="error">{$LANG.starterrorinvalid}</div>
{elseif $error == "user"}
	<div class="error">{$LANG.starterroruser}</div>
{/if}

<form method="post" action="start_session.php" id="sessionForm">
<input type="hidden" name="postData" value="posted" />

{if !isset($SESSION.uid)}
<div class="white">
	<div class="loginText">{$LANG.logintext}</div>
    <div class="formOutline formOutlineWhite">
    	<table>
        	<tr>
            	<td class="widthSet">
                	{$LANG.useremailtext}
                </td>
                <td>
              		<input type="text" name="user" />
                </td>
           	</tr>
        	<tr>
            	<td class="widthSet">
                	{$LANG.userpasswordtext}
                </td>
                <td>
              		<input type="password" name="password" />
                </td>
           	</tr>
        </table>
    </div>
</div>
<div class="blue">
	<div class="registerText">{$LANG.guesttext}</div>
    <div class="formOutline formOutlineBlue">
    	<table>
        	<tr>
            	<td class="widthSet">
                	{$LANG.guestnametext}
                </td>
                <td>
              		<input type="text" name="name" />
                </td>
           	</tr>
        	<tr>
            	<td class="widthSet">
                	{$LANG.guestemailtext}
                </td>
                <td>
              		<input type="text" name="email" />
                </td>
           	</tr>
        </table>
    </div>
</div>

<div style="clear: both;"></div>
{/if}
<div class="bBlue">
	<table>
    	<tr>
        	<td>
				{$LANG.questiontext}
            </td>
            <td>
            	<input type="text" name="question" />
            </td>
        </tr>
        <tr>
            <td>
            	{$LANG.departmenttext}
            </td>
            <td>
                <div class="department_selector">
					{$departBuffer}
                </div>
            	<div class="department_menu">{$departDefault}</div>
                <input type="hidden" name="department" id="department" value="{$departDefaultVal}" />
            </td>
        </tr>
    </table>
</div>
<div id="footer">
    <br /><img src="images/chat/chatnow.jpg" id="submitB" />
</div>
</form>

{literal}
<script type="text/javascript" src="includes/jscript/jquery-1.4.1.min.js"></script>
<script type="text/javascript">

$(document).ready(function () {
resizeTo(630,600);
	var departMenu = false;
	$(".department_menu").click(function () {
		if (!departMenu) {
			var dleft = $(".department_menu").position().left;
			var dtop = (parseInt($(".department_menu").position().top)+24)+"px";
			$(".department_selector").css({
				 'visibility': 'visible',
				 top: dtop,
				 left: dleft
			});
			departMenu = true;
			$(".department_selector").fadeIn(300);
		} else {
			departMenu = false;
			$(".department_selector").click();
		}
	});

	$(".department_selector").click(function () {
		$(".department_selector").fadeOut(300);
	});
	$(".departmentCat").click(function () {
		$(".department_menu").html($(this).html());
		$("#department").val($(this).attr("name"));
		
	});
	$(".departmentCat").hover(function() {
		$(this).css("background-color", "#c7e3ee");
	},
	function() {
		$(this).css("background-color", "white");
	});
	$(".department_menu").hover(function() {
		$(this).css("background-image", "images/dropdown_hover.jpg");
	},
	function() {
		$(this).css("background-image", "images/dropdown.jpg");
	});
	
	preload_image = new Image(25,25); 
    preload_image.src="images/chatnow_hover.jpg"; 
	preload_image2 = new Image(25,25); 
    preload_image2.src="images/dropdown_hover.jpg"; 

	$("#submitB").hover(function() {
		$(this).attr("src", "images/chat/chatnow_hover.jpg");
	},
	function() {
		$(this).attr("src", "images/chat/chatnow.jpg");
	});

	$("#submitB").click(function() {
		$("#sessionForm").trigger("submit");							 
	});
});

</script>{/literal}

</body>
</html>