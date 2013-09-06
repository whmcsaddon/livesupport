<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8"> 
<title>{$LANG.starttitle}</title>

<link rel="stylesheet" href="templates/chat/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="templates/chat/bootstrap/css/bootstrap-theme.css">

<link rel="stylesheet" href="templates/chat/bootstrap/css/chat_start.css">
</head>

{if $error == "invalid"}
	<div class="alert alert-danger error">{$LANG.starterrorinvalid}</div>
{elseif $error == "user"}
	<div class="alert alert-danger error">{$LANG.starterroruser}</div>
{/if}

<form method="post" action="start_session.php" id="sessionForm">
<input type="hidden" name="postData" value="posted" />

{if !isset($SESSION.uid)}

<div class="row">
  <div class="col-md-3 col-sm-5 col-xs-6">
    <div class="thumbnail">
      <div class="caption">
        <h3>{$LANG.logintext}</h3>
        <p>
        	<div class="form-group">
			    <label for="userEmail">{$LANG.useremailtext}</label>
			    <input type="text" name="user" class="form-control" id="userEmail" placeholder="{$LANG.useremailtext}" />
			</div>
			<div class="form-group">
			    <label for="userPass">{$LANG.userpasswordtext}</label>
			    <input type="text" name="password" class="form-control" id="userPass" placeholder="{$LANG.userpasswordtext}" />
			</div>
        </p>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-5 col-xs-6">
    <div class="thumbnail">
      <div class="caption">
        <h3>{$LANG.guesttext}</h3>
        <p>
        	<div class="form-group">
			    <label for="guestName">{$LANG.guestnametext}</label>
			    <input type="text" name="name" class="form-control" id="guestName" placeholder="{$LANG.guestnametext}" />
			</div>
			<div class="form-group">
			    <label for="guestEmail">{$LANG.guestemailtext}</label>
			    <input type="text" name="email" class="form-control" id="guestEmail" placeholder="{$LANG.guestemailtext}" />
			</div>
        </p>
      </div>
    </div>
  </div>
</div>
{/if}
<div class="row" style="margin-top: 15px;">
  <div class="col-md-6">
    <div class="thumbnail">
      <div class="caption">
        <p>
        	<div class="form-group">
			    <label for="question">{$LANG.questiontext}</label>
			    <input type="text" name="question" class="form-control" id="question" placeholder="{$LANG.questiontext}" />
			</div>
			<div class="form-group">
			    <label for="department">{$LANG.departmenttext}</label>
			    <select name="department" id="department" class="form-control">
			    {$departBufferSelect}
			    </select>
			</div>
        </p>
        <p align="right"><a href="#" class="btn btn-primary" id="submitB">Chat Now</a></p>
      </div>
    </div>
  </div>
</div>
</form>

{literal}
<script src="includes/jscript/jquery.js" type="text/javascript"></script>
<script src="templates/chat/bootstrap/js/bootstrap.min.js"></script>

<script type="text/javascript">

$(document).ready(function () {
	resizeTo(630,600);

	$("#submitB").click(function() {
		$("#sessionForm").trigger("submit");							 
	});
});

</script>{/literal}

</body>
</html>