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

$sortby_tudevices = 'tudevices.TITLE';

$out['SORTBY'] = $sortby_tudevices;

$res = SQLSelect("SELECT * FROM tudevices WHERE $qry ORDER BY $sortby_tudevices");

if ($res[0]['ID']) {

   $total = count($res);
   for ($i = 0; $i < $total; $i++) {
      $tmp = explode(' ', $res[$i]['UPDATED']);
      $res[$i]['UPDATED'] = $tmp[0] . " " . $tmp[1];

      $commands = SQLSelect("SELECT * FROM tucommands WHERE DEVICE_ID=" . $res[$i]['ID'] . " AND TITLE!='report'  ORDER BY TITLE");

      if ($commands[0]['ID']) {
         $totalc = count($commands);
         for ($ic = 0; $ic < $totalc; $ic++) {
            if ($commands[$ic]['TITLE'] == 'online') {
               $res[$i]['ONLINE'] = (int)$commands[$ic]['VALUE'];
               continue;
            }
            if ($commands[$ic]['TITLE'] == 'state') {
               $res[$i]['STATE'] = (int)$commands[$ic]['VALUE'];
               continue;
            }
            if ($commands[$ic]['ALIAS']=='') {
             $res[$i]['COMMANDS'] .= '<nobr>' . $commands[$ic]['TITLE'] . ': <i>' . $commands[$ic]['VALUE'] . '</i>';
            } else {
             $res[$i]['COMMANDS'] .= '<nobr>' . $commands[$ic]['ALIAS'] . ': <i>' . $commands[$ic]['VALUE'] . '</i>';
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
         }


          
        }
    }

   $out['RESULT'] = $res;
}
