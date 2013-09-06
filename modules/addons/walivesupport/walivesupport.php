<?php
/**
 * WHMCS Addon Live Support
 *
 * @package    WHMCS Addon
 * @author     WHMCS Addon <whmcsaddon.com>
 */

if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

function walivesupport_config() {
    $configarray = array(
    "name" => "WHMCS Addon Live Support",
    "description" => "This is an open source addon module live support addon for WHMCS.",
    "version" => "2.0",
    "author" => "WHMCS Addon",
    "language" => "english",
    );
    return $configarray;
}

function walivesupport_activate() {

    # Create Custom DB Table
    //$query = "CREATE TABLE `mod_walivesupport` (`id` INT( 1 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`demo` TEXT NOT NULL )";
	//$result = full_query($query);
	
	if (mysql_num_rows(mysql_query("SHOW TABLES LIKE 'chat_settings'"))!=1) {
		mysql_query("CREATE TABLE IF NOT EXISTS `chat_settings` (
  `setting` text NOT NULL,
  `value` text NOT NULL,
  `jsSetting` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		mysql_query("
INSERT INTO `chat_settings` (`setting`, `value`, `jsSetting`) VALUES
('adminHTML', '', 0),
('uploadPath', '', 0),
('connectMessage', '', 1),
('timeout', '123000', 0),
('defaultDepartment', '1', 0),
('AdminDisplayName', 'f', 0),
('ClientDisplayName', 'f', 0),
('uploadEnabled', '0', 0),
('operatorConnectedMessage', 'You are now chatting with %FIRSTNAME% %LASTNAME%', 1),
('chatTitleMessage', 'Live Support', 1),
('chatTitleNewMessage', 'New Message! Live Support', 1),
('clientConnectedMessage', 'You are now chatting with %FIRSTNAME% %LASTNAME% %ENTEREDNAME%', 0),
('onlineDisplay', '<img src=\"images/chat/online.jpg\" alt=\"Live Support Online\" title=\"Live Support Online\" />', 0),
('offlineDisplay', '<img src=\"images/chat/offline.jpg\" alt=\"Live Support Offline\" title=\"Live Support Offline\" />', 0),
('startChatTitleMessage', 'Start Chatting', 0),
('leaveTitleMessage', 'Leave a message!', 0),
('template', 'default', 0),
('defaultLang', 'en', 0);");
	} 
	
	$file = file_get_contents("../modules/addons/walivesupport/install/livechat.sql") or exit ("Error 142: Unable to find sql file!");
	$query = preg_split("/;/", $file) or exit ("Error 143: Unable to split sql file!");
	foreach($query as $sql) {
		mysql_query(trim($sql));
	}
	
	if(mysql_num_rows(mysql_query("SHOW TABLES LIKE 'tblgeoip'"))!=1) {
		$file = file_get_contents("../modules/addons/walivesupport/install/livechat-geoip.sql") or exit ("Error 144: Unable to find sql file!");
		$query = preg_split("/;/", $file) or exit ("Error 145: Unable to split sql file!");
		foreach($query as $sql) {
			mysql_query(trim($sql));
		}
	}

    # Return Result
    return array('status'=>'success','description'=>'This is an demo module only. In a real module you might instruct a user how to get started with it here...');
    return array('status'=>'error','description'=>'You can use the error status return to indicate there was a problem activating the module');
    return array('status'=>'info','description'=>'You can use the info status return to display a message to the user');

}

function walivesupport_deactivate() {
	# Remove Custom DB Table
    //$query = "DROP TABLE `mod_walivesupport`";
	//$result = full_query($query);

    # Return Result
    return array('status'=>'success','description'=>'If successful, you can return a message to show the user here');
    return array('status'=>'error','description'=>'If an error occurs you can return an error message for display here');
    return array('status'=>'info','description'=>'If you want to give an info message to a user you can return it here');

}

function walivesupport_upgrade($vars) {

    $version = $vars['version'];

}

function walivesupport_output($vars) {

    $modulelink = $vars['modulelink'];

    if ($_GET["page"] == "settings") {
    	include "settings.php";
    } else {
    	include "main.php";
    }
}

function walivesupport_sidebar($vars) {

    $modulelink = $vars['modulelink'];
    $version = $vars['version'];
    $option1 = $vars['option1'];
    $option2 = $vars['option2'];
    $option3 = $vars['option3'];
    $option4 = $vars['option4'];
    $option5 = $vars['option5'];
    $LANG = $vars['_lang'];

    $sidebar = '<span class="header"><img src="images/icons/addonmodules.png" class="absmiddle" width="16" height="16" /> WA Live Support</span>
<ul class="menu">
        <li><a href="'.$modulelink.'">Console</a></li>
        <li><a href="'.$modulelink.'&page=settings">Settings</a></li>
    </ul>';
    return $sidebar;

}