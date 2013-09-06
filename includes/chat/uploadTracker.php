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
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: ".date("r")); // Date in the past
error_reporting(0);
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

$uploadProgress = phpinfo_array(1);
if ($upload_id && $uploadProgress["uploadprogress"] != "") {
	$data = uploadprogress_get_info($upload_id);
	if (!$data)
		$data['error'] = 'upload id not found';
	else {		
		$avg_kb = $data['speed_average'] / 1024;
		if ($avg_kb<100)
			$avg_kb = round($avg_kb,1);
		else if ($avg_kb<10)
			$avg_kb = round($avg_kb,2);
		else $avg_kb = round($avg_kb);
		
		// two custom server calculations added to return data object:
		$data['kb_average'] = $avg_kb;
		$data['kb_uploaded'] = round($data['bytes_uploaded'] /1024);
	}
	
	echo json_encode($data);
	exit;
} elseif ($upload_id) {
	$data = uploadprogress_get_info($upload_id);
	$data['error'] = 'upload id not found';
	json_encode($data);
	exit();
}


// display on completion of upload:
if ($_GET["UPLOAD_IDENTIFIER"] || $_POST["UPLOAD_IDENTIFIER"]) {
	require("../../init.php");
	$result = mysql_query("SELECT * FROM `chat_settings` WHERE `setting`='uploadPath';");
	$row = mysql_fetch_row($result);
	$target_path = $row[1];
	
	$target_path = $target_path . basename($_FILES['file1']['name']);
	$ext = explode(".", $_FILES['file1']['name']);
	$ext = $ext[count($ext)-1];
	if ($ext != "gif" && $ext != "jpeg" && $ext != "jpg" && $ext != "png"  && $ext != "zip") {
		exit("<div id=\"error\">Invalid File Format ".$_FILES['file1']['type']."</div>");
	}
	if(move_uploaded_file($_FILES['file1']['tmp_name'], $target_path)) {
		//$ufolder = escapeshellarg($target_path);
		//$ufile = escapeshellarg($_FILES['file1']['name']);
		//$files_to_zip[0] = $target_path.$_FILES['file1']['name'];
		//if true, good; if false, zip creation failed
		//$result = create_zip($files_to_zip,$files_to_zip[0].".zip");
		//if (!$result) {
			//shell_exec("cd ".$ufolder."; zip ".$ufile.".zip ".$ufile.";");
			
		//}	
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
		//$filesize = filesize($target_path.".zip");
		$filesize = filesize($target_path);
		
		//$ext = explode("/", $_FILES['file1']['type']);
		
		if ($ext == "jpg" || $ext == "jpeg" || $ext == "png" || $ext == "gif") {
			$icon = "picture";
		}
		
		if ($ext == "zip") {
			$icon = "zip";	
		}
		
		
		//$data = addslashes(fread(fopen($target_path.".zip", "r"), $filesize));
		$data = addslashes(fread(fopen($target_path, "r"), $filesize));
		$timestamp = time();
		$result = mysql_query("INSERT INTO `chat_upload` (`binary`, `filename`, `filesize`, `filetype`, `session`, `uploader`, `utype`, `timestamp`) VALUES ('$data', '".preg_replace("/[^a-zA-Z0-9\s]/", "", $_FILES['file1']['name'])."', '$filesize', '".$_FILES['file1']['type']."', '1', '$uid', '$utype', '$timestamp');");
		echo "<div id=\"filename\">".preg_replace("/[^a-zA-Z0-9\s]/", "", $_FILES['file1']['name'])."</div>";
		echo "<div id=\"type\">".$_FILES['file1']['type']."</div>";
		echo "<div id=\"ext\">".$ext."</div>";
		echo "<div id=\"icon\">".$icon."</div>";
		echo "<div id=\"error\">".$_FILES['file1']['error']."</div>";
		echo "<div id=\"size\">".$_FILES['file1']['size']."</div>";
		echo "<div id=\"timestamp\">".$timestamp."</div>";
		//unlink($target_path.".zip");
		unlink($target_path);
	} else {
		exit("<div id=\"error\">Upload Failed</div>");	
	}
	exit;
}


/* creates a compressed zip file */
function create_zip($files = array(),$destination = '',$overwrite = false) {
	//if the zip file already exists and overwrite is false, return false
	if(file_exists($destination) && !$overwrite) { return false; }
	//vars
	$valid_files = array();
	//if files were passed in...
	if(is_array($files)) {
		//cycle through each file
		foreach($files as $file) {
			//make sure the file exists
			if(file_exists($file)) {
				$valid_files[] = $file;
			}
		}
	}
	//if we have good files...
	if(count($valid_files)) {
		//create the archive
		$zip = new ZipArchive();
		if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
			return false;
		}
		//add the files
		foreach($valid_files as $file) {
			$zip->addFile($file,$file);
		}
		//debug
		//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
		
		//close the zip -- done!
		$zip->close();
		
		//check to make sure the file exists
		return file_exists($destination);
	}
	else
	{
		return false;
	}
}

function phpinfo_array($return=false){
 /* Andale!  Andale!  Yee-Hah! */
 ob_start();
 phpinfo(-1);
 
 $pi = preg_replace(
 array('#^.*<body>(.*)</body>.*$#ms', '#<h2>PHP License</h2>.*$#ms',
 '#<h1>Configuration</h1>#',  "#\r?\n#", "#</(h1|h2|h3|tr)>#", '# +<#',
 "#[ \t]+#", '#&nbsp;#', '#  +#', '# class=".*?"#', '%&#039;%',
  '#<tr>(?:.*?)" src="(?:.*?)=(.*?)" alt="PHP Logo" /></a>'
  .'<h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#',
  '#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#',
  '#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#',
  "# +#", '#<tr>#', '#</tr>#'),
 array('$1', '', '', '', '</$1>' . "\n", '<', ' ', ' ', ' ', '', ' ',
  '<h2>PHP Configuration</h2>'."\n".'<tr><td>PHP Version</td><td>$2</td></tr>'.
  "\n".'<tr><td>PHP Egg</td><td>$1</td></tr>',
  '<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
  '<tr><td>Zend Engine</td><td>$2</td></tr>' . "\n" .
  '<tr><td>Zend Egg</td><td>$1</td></tr>', ' ', '%S%', '%E%'),
 ob_get_clean());

 $sections = explode('<h2>', strip_tags($pi, '<h2><th><td>'));
 unset($sections[0]);

 $pi = array();
 foreach($sections as $section){
   $n = substr($section, 0, strpos($section, '</h2>'));
   preg_match_all(
   '#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#',
     $section, $askapache, PREG_SET_ORDER);
   foreach($askapache as $m)
       $pi[$n][$m[1]]=(!isset($m[3])||$m[2]==$m[3])?$m[2]:array_slice($m,2);
 }

 return ($return === false) ? print_r($pi) : $pi;
}
?>