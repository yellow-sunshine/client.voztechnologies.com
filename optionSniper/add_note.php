<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL,~E_NOTICE);
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
require_once(__DIR__.'/tda.class.php');
include_once(__DIR__.'/get_settings.php');

$tweet_id = $conn->real_escape_string($_POST['tweet_id']);
$username = $conn->real_escape_string($_POST['username']);

// Here we take out tabs and large amounts of white space
$note = $conn->real_escape_string(trim(preg_replace("/\s{1,999}|\t|\r\n|\n/s", ' ', $_POST['note'])));



$rs=$conn->query("SELECT notes FROM `twitter_sniper`.`tweets` WHERE `tweet_id` = '$tweet_id' LIMIT 1");
$notes = $rs->fetch_assoc();
$notes = json_decode($notes['notes'],1);

$note_id = date('dhisu');
$notes[$note_id]['username'] = $username;
$notes[$note_id]['note'] = $note;
$notes[$note_id]['date'] = date('Y-m-d H:i:s');
$notes = json_encode($notes);
if($rs=$conn->query("UPDATE `twitter_sniper`.tweets SET notes = '$notes'  WHERE `tweet_id` = '$tweet_id' LIMIT 1")){print $notes;}
?>