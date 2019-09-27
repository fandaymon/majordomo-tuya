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

      $commands = SQLSelect("SELECT * FROM tucommands WHERE DEVICE_ID=" . $res[$i]['ID'] . " AND TITLE!='report'  AND TITLE!='ip' AND TITLE!='command' ORDER BY TITLE");

      if ($commands[0]['ID']) {
         $totalc = count($commands);
         for ($ic = 0; $ic < $totalc; $ic++) {
            $res[$i]['COMMANDS'] .= '<nobr>' . $commands[$ic]['TITLE'] . ': <i>' . $commands[$ic]['VALUE'] . '</i>';
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
            if ($commands[$ic]['TITLE'] == 'battery_level') {
               $res[$i]['POWER'] = $commands[$ic]['VALUE'];
               $res[$i]['POWER_WARNING'] = 'success';
               if ($res[$i]['POWER']<= 40)
                  $res[$i]['POWER_WARNING'] = 'warning';
               if ($res[$i]['POWER']<= 20)
                  $res[$i]['POWER_WARNING'] = 'danger';
            }
            $res[$i]['COMMANDS'] .= ";</nobr> ";

 if (time()-strtotime($res[$i]['UPDATED'])>3000) {
  $res[$i]['LOST']='1';}

         }
      }
   }

   $out['RESULT'] = $res;
}
