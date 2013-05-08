<?php

	/*
	Plugin Name: 2-WaySMS.com Messenger
	Plugin URI: http://www.2-waysms.com
	Description: Web-SMS sending and online text messages receiving.
	Version: 1.0
	Author: Tomas Tamm
	Author URI: http://www.2-waysms.com
	License: GPL2


	Copyright 2013  Tomas Tamm  (E-mail: tomas.tamm@yahoo.com)
 
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.
 
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
 
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	*/

$accfldr = "../wp-content/plugins/2-waysms-messenger";
$option = $_REQUEST["option"];

if (file_exists("$accfldr/accfile.php")) {
	include "$accfldr/accfile.php";
} else { }

add_action('admin_menu', 'twowaysms');

function twowaysms() {
global $option, $accfldr;
	add_menu_page('2-WaySMS.com', '2-WaySMS.com', '', '2waysms', 'sending', "$accfldr/ic_2.png", '10');
	add_submenu_page('2waysms', 'Send SMS', 'Send SMS', '1', '2waysms_send','sending');
	add_submenu_page('2waysms', 'Inbox', 'Receive SMS', '1', '2waysms_receive','receivesms');
	add_submenu_page('2waysms', 'Account Settings', 'Settings', '1', '2waysms_settings','settings');
}    

function sending() {
global $option, $accfldr, $from, $token;

	$text = $_REQUEST["text"];
	$to = $_REQUEST["to"];
	$senderid = $_REQUEST["senderid"];

	switch ($option) {

	case sendsms:
		echo "<h1>Bulk WEB-SMS Sending</h1>";
		if ($text == "") { echo "<p>Error!<br>Text not entered<br><a href=\"javascript:history.back(-1)\">Go Back</a></p>"; die; } else { }
		if ($to == "") { echo "<p>Error!<br>Number not entered<br><a href=\"javascript:history.back(-1)\">Go Back</a></p>"; die; } else { }
		if ($senderid == "") { echo "<p>Error!<br>From not entered<br><a href=\"javascript:history.back(-1)\">Go Back</a></p>"; die; } else { }
		$to_arr = explode(";", $to);
		foreach ($to_arr as $to_x){
			$url = "http://www.2-waysms.com/my/api/sms.php";
			$postfields = array ("from" => "$from",
			"token" => "$token",
			"text" => "$text",
			"to" => "$to_x",
			"senderid" => "$senderid");
			if (!$curld = curl_init()) {
				echo "Could not initialize cURL session.";
				exit;
			}
			curl_setopt($curld, CURLOPT_POST, true);
			curl_setopt($curld, CURLOPT_POSTFIELDS, $postfields);
			curl_setopt($curld, CURLOPT_URL, $url);
			curl_setopt($curld, CURLOPT_RETURNTRANSFER, true);
			$output = curl_exec($curld);
			curl_close ($curld);
		}
		echo "<p><a href=\"javascript:history.back(-1)\">Send New Message</a></p>";
	break;

	default:
		echo "<h1>Bulk WEB-SMS Sending</h1>"
		."<form method=post action=\"";
		echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); 
		echo "&option=sendsms\">"
		."<p><b>From</b><br><input type=\"text\" name=\"senderid\"></p>"
		."<p><b>To / Numbers</b><br><textarea rows=\"4\" cols=\"25\" name=\"to\"></textarea>"
		."<blockquote><small>"
		."- Separate numbers with ';' <i>Example: 4444444;555555555;6666666;7777777</i><br>"
		."- Accepted phone numbers format: <i>International format (with country code); Without +, 00, 0 or -; Numbers only!</i>"
		."</small></blockquote></p>"
		."<p><b>Message</b><br><textarea rows=\"4\" cols=\"25\" name=\"text\"></textarea></p>"
		."<p><input type=submit class=button name=submit value=Send></p>"
		."</form>";			
	}
}
    
function receivesms() {
global $option, $accfldr, $from;
	$filename = "$accfldr/messages.txt";

	switch ($option) {

	case removeall:
		echo "<h1>Remove all messages from Inbox</h1>"; 	
		$f=fopen("$filename","w");
		fwrite($f,"");
		fclose($f);
		echo "<p>Done!</p>"; 
	break;
	
	default:
		echo "<h1>Inbox ($from)</h1>"; 
		echo "<a href=\"";
		echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); 
		echo "\">Refresh</a>"; 	

		if (file_exists($filename)) {
			$lines = file($filename);
			$linct = count($lines);
			if ($linct > 0){
				echo "<p><table border=\"0\" cellpadding=\"3\" cellspacing=\"1\" bgcolor=\"#cccccc\">";
				echo "<tr><td><b>#</b></td><td><b>Date</b></td><td><b>Number</b></td><td><b>Text<b></td></tr>";

				foreach($lines as $line_num => $line){
					$xl = explode(";", $line);
					$rnr++;
					echo "<tr bgcolor=\"#ffffff\"><td>$rnr</td><td>$xl[0]</td><td>$xl[1]</td><td>$xl[2]</td></tr>";
				}
				echo "</table></p>";
				echo "<p><a href=\"";
				echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); 
				echo "&option=removeall\">Remove All</a></p>"; 
			} else {
				echo "<p>Inbox is empty!</p>"; 
			}
		} else {
			echo "<p>Inbox is empty!</p>"; 
		}
	}
}

function settings() {
global $option, $accfldr, $from, $token;

	switch ($option) {

	case mkfiles:
		echo "<h1>2-WaySMS Account Settings</h1>";
		$from = $_REQUEST['from'];
		$token = $_REQUEST['token'];
		
		if ($from == "") { echo "<p>Error!<br>From not entered<br><a href=\"javascript:history.back(-1)\">Go Back</a></p>"; die; } else { }
		if ($token == "") { echo "<p>Error!<br>Token not entered<br><a href=\"javascript:history.back(-1)\">Go Back</a></p>"; die; } else { }
		
		$f=fopen("$accfldr/accfile.php","w");
		fwrite($f,"<?php\n\$from=\"$from\";\n\$token=\"$token\";\n?>");
		fclose($f);
		
		$fx=fopen("$accfldr/receive_sms.php",'w');
		fwrite($fx,"<?php\ninclude \"accfile.php\";\n\$tokenx = \$_REQUEST['token'];\nif (\$token == \"\$tokenx\") { } else { die; }\n\$from = \$_REQUEST['from'];\n\$text = \$_REQUEST['text'];\n\$filename = \"messages.txt\";\n\$created = date(\"Y-m-d H:i:s\");\nif (\$from == \"\") {\n} else {\n\$f=fopen(\"\$filename\",\"a\");\nfwrite(\$f,\"\$created;\$from;\$text\\n\");\nfclose(\$f);\n}\n?>");
		fclose($fx);
		echo "<p>Data saved!</p>";
	break;

	default:
		$receiveurl = $_SERVER["SERVER_NAME"];
		echo "<h1>2-WaySMS Account Settings</h1>"
		."<form method=post action=\"";
		echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); 
		echo "&option=mkfiles\">"
		."<p><b>Number</b><br><input type=\"text\" name=\"from\" value=\"$from\">"
		."<blockquote><small>"
		."Need a number, feel free to get it from <a href=\"http://www.2-waysms.com/my\" target=\"_blank\">2-WaySMS.com</a>."
		."</small></blockquote></p>"
		."<p><b>Token</b><br><input type=\"text\" name=\"token\" value=\"$token\">"
		."<blockquote><small>"
		."Your token located on page of Settings of your Number under MY NUMBERS menu at <a href=\"http://www.2-waysms.com/my\" target=\"_blank\">2-WaySMS.com</a> Control Panel."
		."</small></blockquote></p>"	
		."<p><input type=submit name=submit value=\"Save\"></p>"
		."</form>";
		if (file_exists("$accfldr/receive_sms.php")) {
			echo "<h1>Receiver script</h1>"
			."<pre>http://{$receiveurl}/wp-content/plugins/2-waysms-messenger/receive_sms.php</pre>"			
			."<blockquote><small>"
			."1. Copy generated URL above.<br>"
			."2. Log in to your account under <a href=\"http://www.2-waysms.com/my\" target=\"_blank\">2-WaySMS.com</a> Control Panel.<br>"
			."3. Open a page of Settings of your Number under MY NUMBERS menu.<br>"
			."4. Select 'Forward all Inbound Messages to the following URL' and paste copied URL into the field below.<br>"
			."5. Press Save!<br>"
			."</small></blockquote></p>";
		}
	}
}
?>