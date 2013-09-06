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
/*ini_set('default_charset', 'UTF-8');
header('Content-Type:text/html; charset=UTF-8');
mb_internal_encoding("UTF-8");*/
//setlocale ( LC_CTYPE, 'C' );
require("../../init.php");
//mysql_query("SET NAMES 'utf8'");
$result = mysql_query("SELECT * FROM `chat_settings` WHERE `jsSetting`=1;");
for ($x = 0; $x <= mysql_num_rows($result); $x++) {
	$chat_settings[mysql_result($result, $x, 0)] = mysql_result($result, $x, 1);
}
echo $chat_settings[$_GET["setting"]];
?>