<?php

/*
 * This is part of Api Call FreePBX module. Make call using Rest API
 * Copyright (C) 2021  Stefano Fancello gentoo.stefano@gmail.com
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

include '/etc/freepbx_db.conf';
$sql = 'SELECT `val` FROM `kvstore_FreePBX_modules_Apicall` WHERE `key` = "token"';
$sth = $db->prepare($sql);
$sth->execute();
$token = $sth->fetchAll()[0][0];

if (empty($_SERVER['HTTP_TOKEN']) || $_SERVER['HTTP_TOKEN'] !== $token) {
	http_response_code(401);
	exit(1);
}

include '/etc/freepbx.conf';
session_start();
define('FREEPBX_IS_AUTH', 1);
// Include all installed modules class
$post = json_decode(file_get_contents('php://input'), true);
$tocall = $post['PhoneNumber'];
$maxretries = !empty($post['maxretries']) ? $post['maxretires'] : 0;
$retrytime = !empty($post['retrytime']) ? $post['retrytime'] : 60;
$waittime = !empty($post['waittime']) ? $post['waittime'] : 30;
$callerid = !empty($post['callerid']) ? $post['callerid'] : '999';

if (empty($tocall)) {
	error_log('ApiCall: missing tocall');
	http_response_code(400);
	exit(1);
}

$input_parameters = array(
        'ContactId' => null,
        'CampaignId' => null,
        'PhoneNumber' => null,
	'Language' => null,
        'MessageUrl' => null,
        'MessageText' => null,
        'UserInputMethod' => false, // false | 'voice' | 'digits'
	'EndOfSpeakSilenceLength' => null,
        'MessageExitDigit' => '', // ''|123456789*# stop playing message and exit if one of digits is pressed
        'NuberOfExpectedDigits' => 0,
        'EndDigit' => null,
        'UserAnswerSTT' => null,
        'CallStatusWebhookUrl' => null,
        'CallStatusWebhookHeader' => '',
        'GoToDestination' => null, // If setted, go to this destination instead of process next POST
	'NextMessageWebhookUrl' => null,
	'NextMessageWebhookHeader' => '',
	'Timeout' => null,
);

$content = "Channel: Local/{$tocall}@from-internal
MaxRetries: {$maxretries}
RetryTime: {$retrytime}
WaitTime: {$waittime}
Callerid: {$callerid}
Context: aibot
Priority: 1
Extension: s
";

foreach ( $input_parameters as $p => $default_value) {
	$value = isset($post[$p]) ? $post[$p] : $default_value;
	$content .= "Setvar: $p=".base64_encode($value)."\n";
}
// Set hangup handler to send call result
$content .= "Setvar: CHANNEL(hangup_handler_push)=aibot,end,1(args)\n";

$tmp_name = tempnam("/tmp", 'aibot_call');
$f = fopen($tmp_name,"w");
fwrite($f,$content);
fclose($f);
$filename = "aibot_call_".microtime(true).".call";
$res = rename($tmp_name,"/var/spool/asterisk/outgoing/$filename");

