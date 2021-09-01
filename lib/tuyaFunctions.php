<?php

function Tuya_Web_Stats($device_id, $dp_id=17, $gw_id='') {
	include_once(DIR_MODULES . 'tuya/tuya.class.php');
	$tuya_module = new tuya();

	if ($gw_id=='') {
		$gw_id = $device_id;
	}
	
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

function Tuya_Web_Log($device_id, $dp_id=1, $gw_id='',$limit=50, $offset=0) {
	include_once(DIR_MODULES . 'tuya/tuya.class.php');
	$tuya_module = new tuya();

	if ($gw_id=='') {
		$gw_id = $device_id;
	}
	
	$apiResult = $tuya_module->TuyaWebRequest(['action'=> 'tuya.m.location.list',
                                          'requiresSID'=> 1]);

	$result=json_decode($apiResult , true);
	$gid= $result['result'][0] ['groupId'];

	$apiResult = $tuya_module->TuyaWebRequest(['action'=> 'tuya.m.smart.operate.all.log',
                                         'gid'=>$gid,
                                         'data'=> ['devId'=> $device_id,
                                                 'dpIds'=> $dp_id,
                                                 'gwId'=>  $gw_id,
                                                 'limit'=> $limit,
                                                 'offset'=>$offset,
                                                 'startTime'=>'',
                                                 'endTime'=>'',
                                                 'sortType'=>''],
                                          'requiresSID'=> 1]);
	$result=json_decode($apiResult , true);
	return $result['result'];
}	


function Tuya_Door_Log($device_id, $dp_id=1, $gw_id='',$limit=50, $offset=0) {
	include_once(DIR_MODULES . 'tuya/tuya.class.php');
	$tuya_module = new tuya();

	if ($gw_id=='') {
		$gw_id = $device_id;
	}
	
	$apiResult = $tuya_module->TuyaWebRequest(['action'=> 'tuya.m.location.list',
                                          'requiresSID'=> 1]);

	$result=json_decode($apiResult , true);
	$gid= $result['result'][0] ['groupId'];

	$apiResult = $tuya_module->TuyaWebRequest(['action'=> 'm.smart.scale.history.get.list',
                                         'gid'=>$gid,
                                         'data'=> ['devId'=> $device_id,
                                                 'dpIds'=> "",
                                                 'offset'=> $offset,
                                                 'limit'=> $limit
                                                 ],
                                          'requiresSID'=> 1]);


//	$apiResult = $tuya_module->TuyaWebRequest(['action'=> 'm.smart.scale.history.get.list',
//                                         'gid'=>$gid,
 //                                        'data'=> ['devId'=> $device_id,
//                                                 'dpIds'=> $dp_id,
//                                                 'gwId'=>  $gw_id,
//                                                 'limit'=> $limit,
//                                                 'offset'=>$offset,
//                                                 ],
//                                          'requiresSID'=> 1]);
	$result=json_decode($apiResult , true);
	return $result['result'];
}

function TuyaDoorUser($device_id, $id) {
	include_once(DIR_MODULES . 'tuya/tuya.class.php');
	$tuya_module = new tuya();
	
	$gid  = SQLSelectOne("SELECT GID_ID FROM tudevices WHERE DEV_ID='".$device_id."';");
	$gid = $gid['GID_ID'];
	$action = "tuya.m.scale.history.door.user.list";

	$apiResult = $tuya_module->TuyaWebRequest(['action'=>$action,
										 'gid'=>$gid,
										 'data'=> [
                                                   'devId'=>$device_id,
                                                   'gwId'=> $device_id,
												 ],
										  'requiresSID'=> 1], '1.0');
	$apiResult = json_decode($apiResult, true);

	foreach($apiResult['result']['familyMember'] as $user) {
		foreach ($user['unlockIds'] as $auth_id) {
			if ($auth_id == $id) {
				return $user['userName'];
			}
		}
	}
	return 'Неизвестный';	
}	

function TuyaScene($rule_id) {
	include_once(DIR_MODULES . 'tuya/tuya.class.php');
	$tuya_module = new tuya();

	$apiResult = $tuya_module->TuyaWebRequest(['action'=> 'tuya.m.location.list',
										  'requiresSID'=> 1]);
	
	$result=json_decode($apiResult , true);
	$gid= $result['result'][0] ['groupId'];


	$action = "tuya.m.linkage.rule.trigger";

	$apiResult = $tuya_module->TuyaWebRequest(['action'=>$action,
										 'gid'=>$gid,
										 'data'=> ['ruleId'=> $rule_id,
												 ],
										  'requiresSID'=> 1]);
}

function TuyaIR($dev_id, $command) {
	include_once(DIR_MODULES . 'tuya/tuya.class.php');
	$tuya_module = new tuya();

	$dev_info = SQLSelectOne("SELECT * FROM tudevices WHERE DEV_ID='" . $dev_id . "';");
	
	if ($dev_info) {
		$gw_info = SQLSelectOne("SELECT * FROM tudevices WHERE DEV_ID='" .$dev_info['MESH_ID'] ."';");
		
		if ($gw_info) {
			$code = SQLSelectOne("SELECT * FROM tuircommand WHERE DEVICE_ID=" . $dev_info['ID'] . " AND TITLE='" . $command ."';");
			$dps_201 = SQLSelectOne("SELECT * FROM tucommands WHERE TITLE='201' AND DEVICE_ID='".$gw_info['ID']."';");
			
			if ($gw_info['DEV_IP'] !='' and $gw_info['LOCAL_KEY'] !='') {
				if ($code['CPULSE_ALT_FLAG']) {
					if ($dps_201) {
						$dps = '{"201":"{\"control\":\"send_ir\",\"head\":\"\",\"key1\":\"1'. $code['CPULSE_ALT'] . '\",\"type\":0,\"delay\":300}"}';
					} else {	
						$dps='{"1":"study_key","13":3,"3":"","7":"'. $code['CPULSE_ALT'] . '","10":300}';
					}	
				} else {
					if ($dps_201) {
						$dps = '{"201":"{\"control\":\"send_ir\",\"head\":\"'.$code['EXTS'].'\",\"key1\":\"0'. $code['COMPRESSPULSE'] . '\",\"type\":0,\"delay\":300}"}';
					} else {
						$dps='{"1":"send_ir","13":0,"3":"'.$code['EXTS'].'","4":"'. $code['COMPRESSPULSE'] . '","10":300}';
					}	
				}

				$result = $tuya_module->TuyaLocalMsg('SET', $gw_info['DEV_ID'], $gw_info['LOCAL_KEY'], $gw_info['DEV_IP'], $dps);
			} else {
				if ($code['CPULSE_ALT_FLAG']) {
					if ($dps_201) {
						$dps = '{"201":\'{"control":"send_ir","head":"","key1":"1'. $code['CPULSE_ALT'] . '","type":0,"delay":300}\'}';
					} else {	
						$dps='{"1":"study_key","13":3,"3":"","7":"'. $code['CPULSE_ALT'] . '","10":300}';
					}	
				} else {
					if ($dps_201) {
						$dps = '{"201":\'{"control":"send_ir","head":"'.$code['EXTS'].'","key1":"0'. $code['COMPRESSPULSE'] . '","type":0,"delay":300}\'}';
					} else {
						$dps='{"1":"send_ir","13":0,"3":"'.$code['EXTS'].'","4":"'. $code['COMPRESSPULSE'] . '","10":300}';
					}	
				}


				$apiResult = $tuya_module->TuyaWebRequest(['action'=> 'tuya.m.device.dp.publish',
															'gid'=>$gw_info['GID_ID'],
															'data'=> ['devId'=> $gw_info['DEV_ID'],
																	'gwId'=> $gw_info['DEV_ID'],
																	'dps'=> $dps ],
															'requiresSID'=> 1]);
				DebMes('Api Result:'.$apiResult);	
			}	
														
					
		}	
	}	
}			
