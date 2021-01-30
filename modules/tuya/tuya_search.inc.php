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

if (!$qry) $qry = '1';

$sortby_tudevices = 'tudevices.DEV_ID';

$out['SORTBY'] = $sortby_tudevices;

if ($qry=='') {
   $qry="INSTR(DEV_ID,'_')=0";
} else {
   $qry .= " AND INSTR(DEV_ID,'_')=0";
}

$res = SQLSelect("SELECT * FROM tudevices WHERE $qry ORDER BY $sortby_tudevices");
$last_i = 0;

if ($res[0]['ID']) {

   $total = count($res);
   for ($i = 0; $i < $total; $i++) {
      $tmp = explode(' ', $res[$i]['UPDATED']);
      $res[$i]['UPDATED'] = $tmp[0] . " " . $tmp[1];
      
      $commands = SQLSelect("SELECT * FROM tucommands WHERE DEVICE_ID=" . $res[$i]['ID'] . " and TITLE!='state' AND TITLE!='report'  ORDER BY TITLE");

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
                
             if ($commands[$ic]['ALIAS']=='power' OR $commands[$ic]['ALIAS']=='switch_1') {
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

   $out['RESULT'] = $res;
}
