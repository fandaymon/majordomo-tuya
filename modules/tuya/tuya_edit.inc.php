<?php

   if ($this->owner->name == 'panel') {
      $out['CONTROLPANEL'] = 1;
   }

   $table_name = 'tudevices';

   $rec = SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");

   if ($rec['ID']) {
 
   }

   if ($this->mode == 'update') {

      $ok = 1;

      // step: default
      if ($this->tab == '') {
         global $title;
         $rec['TITLE'] = $title;
         if ($rec['TITLE'] == '') {
            $out['ERR_TITLE'] = 1;
            $ok = 0;
         }
         global $local_key;
         $rec['LOCAL_KEY'] = $local_key;
 
         global $mac;
         $rec['MAC'] = $mac;

         global $dev_id;
         $rec['DEV_ID'] = $dev_id;

         global $dev_ip;
         $rec['DEV_IP'] = $dev_ip;
         
         global $control;
         $rec['CONTROL'] = $control;
         
         global $status;
         $rec['STATUS'] = $status;

         global $type;
         $rec['TYPE'] = $type;

         global $send12;
         $rec['SEND12'] = $send12;

         global $flags12;
         $rec['FLAGS12'] = $flags12;

         global $tuya_ver;
         $rec['TUYA_VER'] = $tuya_ver;

         global $dsp_filled;
         if (isset($dsp_filled)) {
            $rec['DSP_FILLED'] = $dsp_filled;    
         } else {
            $rec['DSP_FILLED'] = 0;    
         }     

      }

      //UPDATING RECORD
      if ($ok) {
         //$rec['UPDATED']=date('y-m-d H:j:s',time());
         if ($rec['ID']) {
            if (strlen($rec['SEND12']) == 0) $rec['SEND12'] = 0;
            SQLUpdate($table_name, $rec);
         } else {
            $new_rec = 1;
            $rec['ID'] = SQLInsert($table_name, $rec);
         }

 
         $out['OK'] = 1;
      } else {
         $out['ERR'] = 1;
      }
   }

   // step: default


   // step: data
   if ($this->tab == 'data') {
      $new_id = 0;

      global $delete_id;

      if ($delete_id) {
         SQLExec("DELETE FROM tucommands WHERE ID='".(int)$delete_id."'");
         SQLExec("DELETE FROM tuvalues WHERE ID='".(int)$delete_id."'");

      }

      $properties = SQLSelect("SELECT tucommands.*, VALUE,UPDATED FROM tucommands INNER JOIN tuvalues ON tucommands.ID=tuvalues.ID WHERE DEVICE_ID='".$rec['ID']."' ORDER BY ID");

      $total = count($properties);

      for($i = 0; $i < $total; $i++) {
         $properties[$i]['SDEVICE_TYPE']='relay';
         if ($properties[$i]['ID'] == $new_id) continue;
         if ($this->mode == 'update') {
            /*
            global ${'title'.$properties[$i]['ID']};
            $properties[$i]['TITLE']=trim(${'title'.$properties[$i]['ID']});
            global ${'value'.$properties[$i]['ID']};
            $properties[$i]['VALUE']=trim(${'value'.$properties[$i]['ID']});
            */
            $old_linked_object=$properties[$i]['LINKED_OBJECT'];
            $old_linked_property=$properties[$i]['LINKED_PROPERTY'];
            global ${'linked_object'.$properties[$i]['ID']};
            $properties[$i]['LINKED_OBJECT']=trim(${'linked_object'.$properties[$i]['ID']});
            global ${'linked_property'.$properties[$i]['ID']};
            $properties[$i]['LINKED_PROPERTY']=trim(${'linked_property'.$properties[$i]['ID']});
            global ${'linked_method'.$properties[$i]['ID']};
            $properties[$i]['LINKED_METHOD']=trim(${'linked_method'.$properties[$i]['ID']});
            global ${'alias'.$properties[$i]['ID']};
            $properties[$i]['ALIAS']=trim(${'alias'.$properties[$i]['ID']});
            global ${'color_convert'.$properties[$i]['ID']};
            $properties[$i]['COLOR_CONVERT']=${'color_convert'.$properties[$i]['ID']}; 
            global ${'color_v2'.$properties[$i]['ID']};
            $properties[$i]['COLOR_V2']=${'color_v2'.$properties[$i]['ID']}; 

            global ${'dividedby10'.$properties[$i]['ID']};
            $properties[$i]['DIVIDEDBY10']=${'dividedby10'.$properties[$i]['ID']}; 
            global ${'dividedby2'.$properties[$i]['ID']};
            $properties[$i]['DIVIDEDBY2']=${'dividedby2'.$properties[$i]['ID']};
            global ${'dividedby100'.$properties[$i]['ID']};
            $properties[$i]['DIVIDEDBY100']=${'dividedby100'.$properties[$i]['ID']};
            global ${'value_scale'.$properties[$i]['ID']};
            $properties[$i]['VALUE_SCALE']=${'value_scale'.$properties[$i]['ID']};
            global ${'replace_list'.$properties[$i]['ID']};
            $properties[$i]['REPLACE_LIST']=${'replace_list'.$properties[$i]['ID']};
            global ${'power_meter'.$properties[$i]['ID']};
            $properties[$i]['POWER_METER']=${'power_meter'.$properties[$i]['ID']};
            global ${'decode'.$properties[$i]['ID']};
            $properties[$i]['DECODE']=${'decode'.$properties[$i]['ID']};
            global ${'split'.$properties[$i]['ID']};
            $properties[$i]['SPLIT']=${'split'.$properties[$i]['ID']};  
                                             
            
            if (strlen($properties[$i]['VALUE_SCALE']) == 0) $properties[$i]['VALUE_SCALE'] = 0;
            if (strlen($properties[$i]['DIVIDEDBY10']) == 0) $properties[$i]['DIVIDEDBY10'] = 0;
            if (strlen($properties[$i]['DIVIDEDBY2']) == 0) $properties[$i]['DIVIDEDBY2'] = 0;
            if (strlen($properties[$i]['POWER_METER']) == 0) $properties[$i]['POWER_METER'] = 0;
            if (strlen($properties[$i]['DECODE']) == 0) $properties[$i]['DECODE'] = 0;
            if (strlen($properties[$i]['SPLIT']) == 0) $properties[$i]['SPLIT'] = 0;
       
            
	    unset($properties[$i]['VALUE']);
	    unset($properties[$i]['UPDATED']);

            SQLUpdate('tucommands', $properties[$i]);
            if ($old_linked_object && $old_linked_object!=$properties[$i]['LINKED_OBJECT'] && $old_linked_property && $old_linked_property!=$properties[$i]['LINKED_PROPERTY']) {
             removeLinkedProperty($old_linked_object, $old_linked_property, $this->name);
            }
         }

         if ($properties[$i]['LINKED_OBJECT'] && $properties[$i]['LINKED_PROPERTY']) {
            addLinkedProperty($properties[$i]['LINKED_OBJECT'], $properties[$i]['LINKED_PROPERTY'], $this->name);
         }
      }


      $out['PROPERTIES'] = $properties;
   }

   if (is_array($rec)) {
      foreach($rec as $k => $v) {
         if (!is_array($v)) {
            $rec[$k] = htmlspecialchars($v);
         }
      }
   }

   outHash($rec, $out);
