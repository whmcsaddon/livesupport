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
// required for IE, otherwise Content-disposition is ignored
if(ini_get('zlib.output_compression'))
  ini_set('zlib.output_compression', 'Off');


		require("init.php");
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
		$filesize = filesize($target_path.".zip");
		$data = addslashes(fread(fopen($target_path.".zip", "r"), $filesize));
		$result = mysql_query("SELECT * FROM `chat_upload` WHERE `filename`='".html_entity_decode($_GET["filename"])."' AND `timestamp`='".$_GET["timestamp"]."';");


header("Pragma: public"); // required
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false); // required for certain browsers 
header("Content-Type: ".@MYSQL_RESULT($result,0,"filetype"));
header("Content-Transfer-Encoding: binary");

$getFileType = explode("/", @MYSQL_RESULT($result,0,"filetype"));

header("Content-Disposition: attachment; filename=\"".html_entity_decode($_GET["filename"]).".".$getFileType[1]."\";" );
		echo @MYSQL_RESULT($result,0,"binary");

exit();

?>