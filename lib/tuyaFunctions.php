<?php

function Tuya_Web_Stats($device_id,$gw_id='') {
	include_once(DIR_MODULES . 'tuya/tuya.class.php');
	$tuya_module = new tuya();

	if ($gw_id=='') {
		$gw_id = $device_id;
	}
	
	$dp_id=17;
	echo $device_id . '<BR>';
	echo $gw_id . '<BR>';
	echo $dp_id . '<BR>';
	$apiResult = $tuya_module->TuyaWebRequest(['action'=> 'tuya.m.location.list',
                                          'requiresSID'=> 1]);

	$result=json_decode($apiResult , true);
	$gid= $result['result'][0] ['groupId'];

	$apiResult = $tuya_module->TuyaWebRequest(['action'=> 'tuya.m.dp.stat.month.list',
                                         'gid'=>$gid,
                                         'data'=> ['devId'=> $device_id,
                                                 'gwId'=> $gw_id,
                                                 'dpId'=> $dp_id,
                                                 'type'=> 'sum'],
                                          'requiresSID'=> 1]);

	$result=json_decode($apiResult , true);
	return $result['result'];
}	
