<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{$LANG.leavetitle}</title>
<link rel="stylesheet" type="text/css" href="templates/chat/default/chat_start.css" />
</head>

<body>
{if $displayMessage == 0 && isset($displayMessage)}
	<div class="error">{$LANG.leavesendfailed}</div>
{elseif $displayMessage == 1}
	<div class="success">{$LANG.leavesendsuccess}</div>
{/if}

<div style="font-size: 14px; font-weight: bold; margin-bottom: 5px;">{$LANG.leaveheader}</div>
<form method="post" action="leavemessage.php" id="sessionForm">
<input type="hidden" name="action" value="post" />

<table class="leavemessage">
	<tr>
    	<td>{$LANG.leavenamefield}</td>
        
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
    <tr class="noborder">
        <td colspan="2"><textarea name="message">{$smarty.post.message}</textarea></td>
    </tr>
</table>
<div style="text-align: center; padding: 4px; margin-top: 5px;"><input type="submit" value="Send" /></div>
</form>

</body>
</html>