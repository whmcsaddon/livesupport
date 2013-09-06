<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8"> 
<title>{$LANG.leavetitle}</title>

<link rel="stylesheet" href="templates/chat/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="templates/chat/bootstrap/css/bootstrap-theme.css">

<link rel="stylesheet" type="text/css" href="templates/chat/bootstrap/chat_start.css" />
</head>

<body>

{if $displayMessage == 0 && isset($displayMessage)}
	<div class="alert alert-danger error">{$LANG.leavesendfailed}</div>
{elseif $displayMessage == 1}
	<div class="alert alert-success success">{$LANG.leavesendsuccess}</div>
{/if}

<h4 style="margin: 10px;">{$LANG.leaveheader}</h4>
<form method="post" action="leavemessage.php" id="sessionForm">
<input type="hidden" name="action" value="post" />

<table class="leavemessage table table-bordered table-striped" style="margin-left: 10px; width: 450px;">
	<tr>
    	<td style="width:50px;">{$LANG.leavenamefield}</td>
        
        <td>{if $uid == 0}
        		<input type="text" name="name"{if isset($smarty.post.name) } " value="{$smarty.post.name}" {elseif isset($SESSION.chat_name) } value="{$SESSION.chat_name}" {/if} />
            {else}
            	{$user.firstname} {$user.lastname}<input type="hidden" name="name" value="{$user.firstname} {$user.lastname}" />
        {/if}</td>
    </tr>
	<tr>
    	<td>{$LANG.leaveemailfield}</td>
        <td>{if $uid == 0}
        		<input type="text" name="email"{if isset($smarty.post.email) } " value="{$smarty.post.email}" {elseif isset($SESSION.chat_email) } value="{$SESSION.chat_email}" {/if} />
            {else}
            	{$user.email}<input type="hidden" name="email" value="{$user.email}" />
       {/if}</td>
    </tr>
    <tr>
    	<td>{$LANG.leavesubjectfield}</td>
        <td><input type="text" name="subject"{if isset($smarty.post.subject) } " value="{$smarty.post.subject}" {elseif isset($SESSION.chat_question) } value="{$SESSION.chat_question}" {/if} /></td>
    </tr>
    <tr>
        <td colspan="2"><textarea style="width: 500px; height: 200px;" name="message">{$smarty.post.message}</textarea>
        <div style="text-align: right; margin-top: 10px;"><input class="btn btn-default" type="submit" value="Send" /></div>
        </td>
    </tr>
</table>

</form>

</body>
</html>