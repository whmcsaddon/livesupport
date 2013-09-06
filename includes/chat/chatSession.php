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
// Since this is an include only file, $dir must be set by the calling file
// File requires db connection already established to run.

	//require_once($dir."dbconnect.php");
	class chatSession {
		private $sqlTable;
		
		function useSession($session) {
			$result = mysql_query("SELECT * FROM `chat_sessions` WHERE `session`='".mysql_real_escape_string($session)."'");
			while($row = mysql_fetch_array($result)) {
				//$this->sqlTable[$row[0]] = $row[1];
				$this->sqlTable["session"] = $row["session"];
				$this->sqlTable["uid"] = $row["uid"];
				$this->sqlTable["name"] = $row["name"];
				$this->sqlTable["email"] = $row["email"];
				$this->sqlTable["question"] = $row["question"];
				$this->sqlTable["environment"] = $row["environment"];
				$this->sqlTable["departments"] = $row["departments"];
				$this->sqlTable["active"] = $row["active"];
				$this->sqlTable["timestamp"] = $row["timestamp"];
				$this->sqlTable["tid"] = $row["tid"];
				$this->sqlTable["ignore"] = $row["ignore"];
				$this->sqlTable["utype"] = $row["utype"];
				$this->sqlTable["wmessage"] = $row["wmessage"];
				//echo $this->sqlTable["name"];
				
			}
			//print_r($this->sqlTable);
		}
		
		function createSession($variables) {
			// $variables Should Set: `session`, `uid`, `name`, `email`, `question`, `departments`, `active`
			$result = mysql_query("INSERT INTO `chat_sessions` (`session`, `uid`, `name`, `email`, `question`, `departments`, `environment`, `active`, `timestamp`, `utype`) VALUES ('".mysql_real_escape_string($variables["session"])."', '".mysql_real_escape_string($variables["uid"])."', '".mysql_real_escape_string($variables["name"])."', '".mysql_real_escape_string($variables["email"])."', '".mysql_real_escape_string($variables["question"])."', '".mysql_real_escape_string($variables["departments"])."', '".serialize($_SERVER)."', ".mysql_real_escape_string($variables["active"]).", '".$_SERVER['REQUEST_TIME']."', '".mysql_real_escape_string($variables["utype"])."')") or die("error:" . mysql_error());
		}
		
		function getSession() {
			return $this->sqlTable["session"];
		}
		
		function getUID() {
			return $this->sqlTable["uid"];
		}
		
		function setUID($uid) {
			$result = mysql_query("UPDATE `chat_sessions` SET `uid`='".$uid."' WHERE `session`='".$this->sqlTable["session"]."'");
			$this->sqlTable["uid"] = $uid;
		}
		
		function getName() {
			return $this->sqlTable["name"];
		}
		
		function getEmail() {
			return $this->sqlTable["email"];
		}
		
		function getQuestion() {
			return $this->sqlTable["question"];
		}
		
		function getDepartments() {
			return explode("|", $this->sqlTable["departments"]);
		}
		
		function setDepartment($depart) {
			$depart = $this->sqlTable["departments"]."|".$depart;
			$result = mysql_query("UPDATE `chat_sessions` SET `departments`='".$depart."' WHERE `session`='".$this->sqlTable["session"]."'");
			$this->sqlTable["departments"] = $depart;
		}
		
		function transferDepartments($departmentID) {
			$result = mysql_query("UPDATE `chat_sessions` SET `departments`='".$this->sqlTable["departments"]."|".$departmentID."' WHERE `session`='".$this->sqlTable["session"]."'");
		}
		
		function getWritingMessage() {
			$tmp = array();
			$tmp = explode("|", $this->sqlTable["wmessage"]);
			$tmpArray = array();
			foreach ($tmp as $key => $who) {
				if (substr($who, 0, 1) == ";") {
					if (in_array(substr($who, 1), $tmpArray)) {
						unset($tmpArray[array_search(substr($who, 1), $tmpArray)]);	
					}
				} else {
					$tmpArray[] = $who;	
				}
			}
			
			return array_unique($tmpArray);
		}
		
		function setWritingMessage($wmessage, $action=0) {
			//$this->useSession($this->sqlTable["wmessage"]);
			if ($action == 1) {
				$proccessed = "|".$wmessage;
				$append = array();
				$append = $this->getWritingMessage();
				if (!in_array($wmessage, $append)) {
					$result = mysql_query("UPDATE `chat_sessions` SET `wmessage`=concat(`wmessage`,'".$proccessed."') WHERE `session`='".$this->sqlTable["session"]."'");
					$this->sqlTable["wmessage"] .= $proccessed;
				}
			} else {
				$proccessed = "|;".$wmessage;
				$append = array();
				$append = $this->getWritingMessage();
				if (in_array($wmessage, $append)) {
					$result = mysql_query("UPDATE `chat_sessions` SET `wmessage`=concat(`wmessage`,'".$proccessed."') WHERE `session`='".$this->sqlTable["session"]."'");
					$this->sqlTable["wmessage"] .= $proccessed;
				}
			}
			
			
		}
		
		function getEnvironment() {
			return unserialize($this->sqlTable["environment"]);
		}
		
		function getActive() {
			return $this->sqlTable["active"];
		}
		
		function setActive($active) {
			$result = mysql_query("UPDATE `chat_sessions` SET `active`='".$active."' WHERE `session`='".$this->sqlTable["session"]."'");
			$this->sqlTable["active"] = $active;
		}
		
		function getTimestamp() {
			return $this->sqlTable["timestamp"];
		}
		
		function getIgnored() {
			return explode("|", $this->sqlTable["ignore"]);
		}
		
		function setIgnored($ignore) {
			$ignore = $this->sqlTable["ignore"]."|".$ignore;
			$result = mysql_query("UPDATE `chat_sessions` SET `ignore`='".$ignore."' WHERE `session`='".$this->sqlTable["session"]."'");
			$this->sqlTable["ignore"] = $ignore;
		}
		
		function getTID() {
			return $this->sqlTable["tid"];
		}
		
		function setTID($tid) {
			$result = mysql_query("UPDATE `chat_sessions` SET `tid`='".$tid."' WHERE `session`='".$this->sqlTable["session"]."'");
			$this->sqlTable["tid"] = $tid;
		}
		
		function getUType() {
			return $this->sqlTable["utype"];
		}
		
		function setUType($utype) {
			$result = mysql_query("UPDATE `chat_sessions` SET `utype`='".$utype."' WHERE `session`='".$this->sqlTable["session"]."'");
			$this->sqlTable["utype"] = $utype;
		}
		
		function createSessionId() {
			return $unique = sha1(uniqid(hash("md5", time()), TRUE));	
		}
	}
?>