<?php

global $session;

if ($this->owner->name == 'panel') {
   $out['CONTROLPANEL'] = 1;
}

$qry = '1';

global $save_qry;

if ($save_qry) {
   $qry = $session->data['tudevices_qry'];
} else {
   $session->data['tudevices_qry'] = $qry;
}

global $tab;

if ($tab == 'scene') {
   $res = SQLSelect("SELECT * FROM tudevices WHERE TYPE='scene' ORDER BY DEV_ID");
} elseif ($tab == 'ir') {   
   $res = SQLSelect("SELECT * FROM tudevices WHERE IR_FLAG=1;");
   
   if ($res) {
      foreach ($res as &$pult) {
         $codes = SQLSelect("SELECT tuircommand.*, '".$pult['DEV_ID']."' as DEV_ID FROM tuircommand WHERE TITLE !='' AND DEVICE_ID=" . $pult['ID'] );

         if ($codes) {
            $pult['CODES'] = $codes;
         } else {
            $apiResult = $this->TuyaWebRequest(['action'=> 'tuya.m.location.list',
                                                'requiresSID'=> 1]);

            $result=json_decode($apiResult , true);
            $gid= $result['result'][0] ['groupId'];

            $action = "tuya.m.infrared.record.get";
            
            $device_id = $pult['DEV_ID'];
            $gw_id = $pult['MESH_ID'];

            $apiResult = $this->TuyaWebRequest(['action'=>$action,
                                                'gid'=>$gid,
                                                'data'=> ['devId'=> $device_id,
                                                         'gwId'=>  $gw_id,
                                                         'subDevId'=> $gw_id,
                                                         'vender'=>'3',
                                                 ],
                                                'requiresSID'=> 1]);
            $result=json_decode($apiResult , true);

            // RF Code

            
            if ($result['result']['exts'] == '{"study":6}') {
               $action = "tuya.m.infrared.learn.get";

               $gw_id = $device_id;

               $apiResult = $this->TuyaWebRequest(['action'=>$action,
                                                   'gid'=>$gid,
                                                   'data'=> ['devId'=> $device_id,
                                                            'gwId'=>  $gw_id,
                                                            'subDevId'=> $gw_id,
                                                            'vender'=>'20',
                                                    ],
                                                   'requiresSID'=> 1]);

               $result=json_decode($apiResult , true);
   
               if ($result['result']) {
                  foreach ($result['result'] as $code) {

                     $pulse = ($code['compressPulse']);
                  
                     $pulse = str_replace('"', '\"', $pulse); 
                     $pulse = substr($pulse, 0, strlen($pulse)-1); 
                     $pulse .= ',\"ver\":\"2\"}';

                     $new_code['DEVICE_ID'] = $pult['ID'];
                     $new_code['TITLE'] = $code['keyName'];
                     $new_code['CPULSE_ALT'] = $pulse;
                     $new_code['EXTS'] = '';
                     $new_code['CPULSE_ALT_FLAG'] =  0;
                     $new_code['RF_FLAG'] =  1;
                     

                     SQLInsert('tuircommand', $new_code);
                     $new_code['DEV_ID'] = $pult['DEV_ID'];
                     array_push($codes, $new_code); 
                     unset($new_code['DEV_ID']);                     

                     
                  }
               
               }    
               
               $pult['CODES'] = $codes;    


            } else {

               $remote_id = $result['result']['remoteId'];
               $dev_type_id = $result['result']['devTypeId']; 

               $apiResult =  $this->TuyaWebRequest(['action'=> 'tuya.m.infrared.keydata.get',
                                                            'gid'=>$gid,
                                                            'data'=> ['devId'=> $device_id,
                                                            'devTypeId'=> $dev_type_id,
                                                            'gwId'=>  $gw_id,
                                                            'remoteId'=> $remote_id,
                                                            'vender'=>'3',
                                                            ],
                                                         'requiresSID'=> 1], '2.0');
               $result=json_decode($apiResult , true);
               
               $codes = array();
               
               if ($result['result']) {

                  foreach ($result['result']['compressPulseList'] as $code) {
                     $new_code['DEVICE_ID'] = $pult['ID'];
                     $new_code['TITLE'] = $code['keyName'];
                     $new_code['COMPRESSPULSE'] = $code['compressPulse'];
                     $exts = $code['exts'];
                     $exts = str_replace("\\","",$exts);
                     $exts = json_decode($exts , true);
                     $new_code['EXTS'] = $exts['99999'];
                     $new_code['CPULSE_ALT_FLAG'] =  0;
                     $new_code['RF_FLAG'] =  0;                     
                     
                     SQLInsert('tuircommand', $new_code);
                     $new_code['DEV_ID'] = $pult['DEV_ID'];
                     array_push($codes, $new_code); 
                     unset($new_code['DEV_ID']);
                  }
               
               }

               $action = "tuya.m.infrared.learn.get";

               $apiResult = $this->TuyaWebRequest(['action'=>$action,
                                          'gid'=>$gid,
                                          'data'=> ['devId'=> $gw_id,
                                                   'gwId'=>  $gw_id,
                                                   'subDevId'=> $device_id,
                                                   'vender'=>'3',
                                                   ],
                                          'requiresSID'=> 1]);

               $result=json_decode($apiResult , true);
               
               $codes = array();
               
               if ($result['result']) {

                  foreach ($result['result'] as $code) {
                     $new_code['DEVICE_ID'] = $pult['ID'];
                     $new_code['TITLE'] = $code['keyName'];
                     $new_code['CPULSE_ALT'] =  base64_encode(hex2bin($code['compressPulse']));
                     $new_code['CPULSE_ALT_FLAG'] =  1;
                     $new_code['RF_FLAG'] =  0;                     
                     
                     SQLInsert('tuircommand', $new_code);
                     $new_code['DEV_ID'] = $pult['DEV_ID'];
                     array_push($codes, $new_code); 
                     unset($new_code['DEV_ID']);
                  }
               
               }
               
               $pult['CODES'] = $codes;     
            
           }
         }   
      }      
   }

} else {
   if (!$qry) $qry = '';

   //$sortby_tudevices = 'tudevices.DEV_ID';
   $sortby_tudevices = 'tudevices.TYPE, tudevices.TITLE';

   $out['SORTBY'] = $sortby_tudevices;

   if ($qry != '') {
      $qry .= " AND "; 
   } 
   $qry .= "INSTR(DEV_ID,'_')=0 AND TYPE !='scene' AND IR_FLAG=0 ";

   $res = SQLSelect("SELECT * FROM tudevices WHERE $qry ORDER BY $sortby_tudevices");
   $last_i = 0;

   if ($res[0]['ID']) {

      $total = count($res);
      for ($i = 0; $i < $total; $i++) {
         $tmp = explode(' ', $res[$i]['UPDATED']);
         $res[$i]['UPDATED'] = $tmp[0] . " " . $tmp[1];
         
         $commands = SQLSelect("SELECT tucommands.*, tuvalues.VALUE, tuvalues.UPDATED FROM tucommands INNER JOIN tuvalues ON tucommands.ID=tuvalues.ID WHERE DEVICE_ID=" . $res[$i]['ID'] . " and TITLE!='state' AND TITLE!='report'  ORDER BY TITLE");

         if ($commands[0]['ID']) {
            $totalc = count($commands);
            $sub_dev = array();
            for ($ic = 0; $ic < $totalc; $ic++) {
               if ($commands[$ic]['TITLE'] == 'online') {
                  $res[$i]['ONLINE'] = (int)$commands[$ic]['VALUE'];
                  continue;
               }
               if ($commands[$ic]['ALIAS']=='') {
                $res[$i]['COMMANDS'] .= '<nobr>' . $commands[$ic]['TITLE'] . ': <i>' . substr($commands[$ic]['VALUE'],0,50) . ' ' . $commands[$ic]['VALUE_UNIT'] . '</i>';
               } else {
                $res[$i]['COMMANDS'] .= '<nobr>' . $commands[$ic]['ALIAS'] . ': <i>' . substr($commands[$ic]['VALUE'],0,50) .  ' ' . $commands[$ic]['VALUE_UNIT'] . '</i>';
                if ($commands[$ic]['ALIAS']=='led_switch' OR $commands[$ic]['ALIAS']=='switch_led') {
                  $res[$i]['LAMP'] = (int)$commands[$ic]['VALUE'];
                  $res[$i]['IS_LAMP'] = 1;
                }
                   
                if ($commands[$ic]['ALIAS']=='power' OR $commands[$ic]['ALIAS']=='switch_1' OR $commands[$ic]['ALIAS']=='switch_on' OR $commands[$ic]['ALIAS']=='Power' OR $commands[$ic]['ALIAS']=='switch') {
                  $res[$i]['STATE'] = (int)$commands[$ic]['VALUE'];
                  $res[$i]['IS_STATE'] = 1;
                }
                
                if (substr($commands[$ic]['ALIAS'],0,7)=='switch_') {
                   $switch_id = substr($commands[$ic]['ALIAS'],strpos($commands[$ic]['ALIAS'],'_')+1);
                   $sub_name = SQLSelectOne("SELECT TITLE FROM tudevices  WHERE DEV_ID = '" . $res[$i]['DEV_ID'] . "_". $switch_id . "' ORDER BY DEV_ID");
                   if ($sub_name) {
                     $switch_name = $sub_name['TITLE']; 
                   } else {    
                     $switch_name = $commands[$ic]['ALIAS'];
                   }    
                   array_push($sub_dev, ['ID' => $switch_id, 'SWITCH_NAME' => $switch_name, 'SWITCH_STATE' => (int)$commands[$ic]['VALUE'] ]);
                } 
                
                if ($commands[$ic]['ALIAS']=='ir_code' ) {
                  $res[$i]['IS_STATE'] = 0;
                  $res[$i]['IS_LAMP'] = 0;
                }   
      

               }
               if ($commands[$ic]['LINKED_OBJECT'] != '') {
                  $device=SQLSelectOne("SELECT TITLE FROM devices WHERE LINKED_OBJECT='".DBSafe($commands[$ic]['LINKED_OBJECT'])."'");
                  if ($device['TITLE']) {
                     $res[$i]['COMMANDS'] .= ' (' . $device['TITLE'].')';
                  } else {
                     $res[$i]['COMMANDS'] .= ' (' . $commands[$ic]['LINKED_OBJECT'];
                     if ($commands[$ic]['LINKED_PROPERTY'] != '') {
                        $res[$i]['COMMANDS'] .= '.' . $commands[$ic]['LINKED_PROPERTY'];
                     } elseif ($commands[$ic]['LINKED_METHOD'] != '') {
                        $res[$i]['COMMANDS'] .= '.' . $commands[$ic]['LINKED_METHOD'];
                     }
                     $res[$i]['COMMANDS'] .= ')';

                  }
               }
               $res[$i]['COMMANDS'] .= ";</nobr> ";
               
               if (count($sub_dev)>1) {
                  $res[$i]['SUBDEV'] = $sub_dev;
                  $res[$i]['IS_MULTI'] = 1;
                  $res[$i]['IS_STATE'] = 0;
               }   
            }
          }
             
           
       }
   }
   
}
$out['RESULT'] = $res;
