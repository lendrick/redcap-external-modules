<?php
namespace ExternalModules;

require_once dirname(dirname(dirname(__FILE__))) . '/classes/ExternalModules.php';

$pid = @$_GET['pid'];
$moduleDirectoryPrefix = $_GET['prefix'];
$version = $_GET['moduleDirectoryVersion'];

if(empty($pid) && !ExternalModules::hasSystemSettingsSavePermission($moduleDirectoryPrefix)){
	header('Content-type: application/json');
	echo json_encode(array(
		'status' => 'You do not have permission to save system settings!'
	));
}

$config = ExternalModules::getConfig($moduleDirectoryPrefix, $version, $pid);

$importFile = reset($_FILES);

if($importFile) {
	$tmp_name  = $importFile['tmp_name'];
	$f = fopen($tmp_name,"r");
	$headers = fgetcsv($f);
	$settings = [];
	while($row = fgetcsv($f)) {
		$settings[$row[0]] = [
			"type" => $row[1],
			"value" => $row[2]
		];
	}

	$errors = [];
	foreach($settings as $key => $details) {
		if($details["type"] == "json-array") {
			$value = json_decode($details["value"],true);
			if(!$value) {
				$errors[$key] = 'Error decoding json-array';
			}
		}
		else if($details["type"] == "boolean") {
			if(strtolower($details["value"]) == "true") {
				$value = true;
			}
			else if(strtolower($details["value"]) == "false") {
				$value = false;
			}
			else {
				$value = $details["value"];
			}
		}
		else {
			$value = $details["value"];
		}

		ExternalModules::setProjectSetting($moduleDirectoryPrefix,$pid,$key,$value);
	}
}