<?php
namespace ExternalModules;

require_once dirname(dirname(dirname(__FILE__))) . '/classes/ExternalModules.php';

$pid = @$_GET['pid'];
$moduleDirectoryPrefix = $_GET['moduleDirectoryPrefix'];
$version = $_GET['moduleDirectoryVersion'];

if(empty($pid) && !ExternalModules::hasSystemSettingsSavePermission($moduleDirectoryPrefix)){
	header('Content-type: application/json');
	echo json_encode(array(
			'status' => 'You do not have permission to save system settings!'
	));
}

$Proj = new \Project($pid);
$title = substr($Proj->getAttributesApiExportProjectInfo()["app_title"],20);

$details = ExternalModules::getSettings($moduleDirectoryPrefix, $pid);

header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename='$title"."_".$moduleDirectoryPrefix."_ModuleSettings_".date('Y-m-d').".csv'");

$outstream = fopen("php://output", 'w');

fputcsv($outstream,["key","type","value"]);

while($row = db_fetch_assoc($details)) {
	fputcsv($outstream,[$row['key'],$row["type"],$row["value"]]);
}