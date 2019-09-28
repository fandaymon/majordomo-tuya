<?php
/**
* Tuya
* @package project
* @author <fandaymon@gmail.com>
* @copyright 2019 (c)
* @version 2019.09.22
*/


Define('TUYA_LOCAL_PORT', 9898);
Define('TUYA_WEB','https://px1.tuyaeu.com');


class tuya extends module
{
   /**
    * tuya
    *
    * Module class constructor
    *
    * @access private
    */
   function tuya()
   {
      $this->name = 'tuya';
      $this->title = 'Tuya smarthome';
      $this->module_category = '<#LANG_SECTION_DEVICES#>';
      $this->checkInstalled();
   }

   /**
    * saveParams
    *
    * Saving module parameters
    *
    * @access public
    */
   function saveParams($data = 0)
   {
      $p = array();
      if (isset($this->id)) {
         $p["id"] = $this->id;
      }
      if (isset($this->view_mode)) {
         $p["view_mode"] = $this->view_mode;
      }
      if (isset($this->edit_mode)) {
         $p["edit_mode"] = $this->edit_mode;
      }
      if (isset($this->data_source)) {
         $p["data_source"] = $this->data_source;
      }
      if (isset($this->tab)) {
         $p["tab"] = $this->tab;
      }
      return parent::saveParams($p);
   }

   /**
    * getParams
    *
    * Getting module parameters from query string
    *
    * @access public
    */
   function getParams()
   {
      global $id;
      global $mode;
      global $view_mode;
      global $edit_mode;
      global $data_source;
      global $tab;

      if (isset($id)) {
         $this->id = $id;
      }
      if (isset($mode)) {
         $this->mode = $mode;
      }
      if (isset($view_mode)) {
         $this->view_mode = $view_mode;
      }
      if (isset($edit_mode)) {
         $this->edit_mode = $edit_mode;
      }
      if (isset($data_source)) {
         $this->data_source = $data_source;
      }
      if (isset($tab)) {
         $this->tab = $tab;
      }
   }

   /**
    * Run
    *
    * Description
    *
    * @access public
    */
   function run()
   {
      global $session;

      $out = array();

      if ($this->action == 'admin') {
         $this->admin($out);
      } else {
         $this->usual($out);
      }

      if (isset($this->owner->action)) {
         $out['PARENT_ACTION'] = $this->owner->action;
      }

      if (isset($this->owner->name)) {
         $out['PARENT_NAME'] = $this->owner->name;
      }

      $out['VIEW_MODE'] = $this->view_mode;
      $out['EDIT_MODE'] = $this->edit_mode;
      $out['MODE'] = $this->mode;
      $out['ACTION'] = $this->action;
      $out['DATA_SOURCE'] = $this->data_source;
      $out['TAB'] = $this->tab;
      $this->data = $out;
      $p = new parser(DIR_TEMPLATES . $this->name . "/" . $this->name . ".html", $this->data, $this);
      $this->result = $p->result;
   }


   function admin(&$out)
   {
      $this->getConfig();

      if ((time() - (int)gg('cycle_tuyaRun')) < 15) {
         $out['CYCLERUN'] = 1;
      } else {
         $out['CYCLERUN'] = 0;
      }

      $out['TUYA_USERNAME'] = $this->config['TUYA_USERNAME'];
      $out['TUYA_PASSWD'] = $this->config['TUYA_PASSWD'];
      $out['TUYA_INTERVAL'] = $this->config['TUYA_INTERVAL'];


      if ($this->view_mode=='update_settings') {

         global $tuya_username;
         $this->config['TUYA_USERNAME'] = $tuya_username;

         global $tuya_passwd;
         $this->config['TUYA_PASSWD'] = $tuya_passwd;

         global $tuya_interval;
         $this->config['TUYA_INTERVAL'] = $tuya_interval;

        
         $token=json_decode($this->getToken($tuya_username,$tuya_passwd));

         $this->config['TUYA_ACCESS_TOKEN']=$token->access_token;
         $this->config['TUYA_REFRESH_TOKEN']=$token->refresh_token;
         $this->config['TUYA_TIME']=time()+$token->expires_in;
         $this->Tuya_Discovery_Devices($token->access_token);      

         $this->saveConfig();


         setGlobal('cycle_tuyaControl', 'restart');

         $this->redirect('?');
      }

      if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
         $out['SET_DATASOURCE'] = 1;
      }

      if ($this->data_source == 'tudevices' || $this->data_source == '') {
         if ($this->view_mode == '' || $this->view_mode == 'search_tudevices') {
            $this->search_tudevices($out);
         }
         if ($this->view_mode == 'edit_tudevices') {
            $this->edit_tudevices($out, $this->id);
         }
         if ($this->view_mode == 'delete_tudevices') {
            $this->delete_tudevices($this->id);
            $this->redirect("?data_source=tudevices");
         }
      }

      if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
         $out['SET_DATASOURCE'] = 1;
      }

      if ($this->data_source == 'tucommands') {
         if ($this->view_mode == '' || $this->view_mode == 'search_tucommands') {
            $this->search_tucommands($out);
         }
         if ($this->view_mode == 'edit_tucommands') {
            $this->edit_tucommands($out, $this->id);
         }
      }
   }

   /**
    * FrontEnd
    *
    * Module frontend
    *
    * @access public
    */
   function usual(&$out)
   {
      if ($this->ajax) {
         global $op;
 //        if ($op == 'process') {
 //           global $message;
 //           global $ip;
 //           global $log_debmes;
 //           global $log_gw_heartbeat;

//            $this->processMessage($message, $ip, $log_debmes, $log_gw_heartbeat);
 //        }
      }
      $this->admin($out);
   }

   /**
    * tudevices search
    *
    * @access public
    */
   function search_tudevices(&$out)
   {
      require(DIR_MODULES . $this->name . '/tuya_search.inc.php');
   }

   /**
    * tudevices edit/add
    *
    * @access public
    */
   function edit_tudevices(&$out, $id)
   {
      require(DIR_MODULES . $this->name . '/tuya_edit.inc.php');
   }

   /**
    * tudevices delete record
    *
    * @access public
    */
   function delete_tudevices($id)
   {
       $rec = SQLSelectOne("SELECT * FROM tudevices WHERE ID='$id'");

       SQLExec("DELETE FROM tucommands WHERE DEVICE_ID='" . $rec['ID'] . "'");
       SQLExec("DELETE FROM tudevices WHERE ID='" . $rec['ID'] . "'");
   }

   /**
    * tucommands search
    *
    * @access public
    */
   function search_tucommands(&$out)
   {
      require(DIR_MODULES . $this->name . '/tucommands_search.inc.php');
   }

   /**
    * tucommands edit/add
    *
    * @access public
    */
   function edit_tucommands(&$out, $id)
   {
      require(DIR_MODULES . $this->name . '/tucommands_edit.inc.php');
   }




   function getToken($username,$passwd) {
    $sURL = 'https://px1.tuyaeu.com/homeassistant/auth.do';
    $sPD = "userName=".$username."&password=".$passwd."&countryCode=eu&bizType=&from=tuya"; 
    $aHTTP = array(
	  'http' => 
	    array(
	    'method'  => 'POST', 
	    'header'  => 'Content-type: application/x-www-form-urlencoded',
	    'content' => $sPD
	  )
     );
     $context = stream_context_create($aHTTP);
     $contents = file_get_contents($sURL, false, $context);
     return $contents;
   }

   function RefreshToken(){
     $this->getConfig();

     if (time()>$this->config['TUYA_TIME']) {
      $sURL = 'https://px1.tuyaeu.com/';
      $sPD = "grant_type=refresh_token&refresh_token=".$this->config['TUYA_REFRESH_TOKEN'];


      $aHTTP = array(
	  'http' => 
	    array(
	    'method'  => 'GET', 
	    'header'  => 'Content-type: application/x-www-form-urlencoded',
	    'content' => $sPd
	  )
      );
      $context = stream_context_create($aHTTP);
      $contents = file_get_contents($sURL, false, $context);
        
      $token=json_decode($contents);

      $this->config['TUYA_ACCESS_TOKEN']=$token->access_token;
      $this->config['TUYA_REFRESH_TOKEN']=$token->refresh_token;
      $this->config['TUYA_TIME']=time()+$token->expires_in;

      $this->saveConfig();
     }
     return $this->config['TUYA_ACCESS_TOKEN'];
   }


  function TuyaLocalMsg($command,$dev_id,$local_key,$local_ip,$data='') {

   $prefix="000055aa00000000000000";
   $suffix="000000000000aa55";
   if ($command=='STATUS') {
    $hexByte="0a";
    $json='{"gwId":"'.$dev_id.'","devId":"'.$dev_id.'"}';
   } else {
    $hexByte="07";
    $dps=$data;
    $json='{"gwId":"'.$dev_id.'","devId":"'.$dev_id.'", "t": "'.time().'", "dps": ' . $dps . '}';
   }

  
    $json_payload=openssl_encrypt($json, 'AES-128-ECB', $local_key, OPENSSL_RAW_DATA);

   if ($command != 'STATUS') {
    $json_payload = hex2bin("332E33000000000000000000000000" . bin2hex($json_payload));
   }

   $postfix_payload = hex2bin(bin2hex($json_payload) . $suffix);
   $postfix_payload_hex_len = dechex(strlen($postfix_payload));

   $buffer = hex2bin($prefix . $hexByte . '000000' . $postfix_payload_hex_len ) . $postfix_payload;
   $buffer=bin2hex($buffer);
   $buffer1=strtoupper(substr($buffer,0,-16));

   $hex_crc = dechex(crc32(hex2bin($buffer1))) ;
   $buffer=substr($buffer,0,-16) .($hex_crc).substr($buffer,-8);
   $data=$this->Tuya_send_receive(hex2bin($buffer),$local_ip);
   $result = substr($data,20,-8);
   $result = openssl_decrypt($result, 'AES-128-ECB', $local_key, OPENSSL_RAW_DATA);
   return $result;
  }

  function requestLocalStatus(){
   $devices=SQLSelect("SELECT * FROM tudevices WHERE LOCAL_KEY IS NOT NULL and DEV_IP IS NOT NULL");
   foreach($devices as $device) {
    $status=$this->TuyaLocalMsg('STATUS',$device['DEV_ID'],$device['LOCAL_KEY'],$device['DEV_IP']);
    if ($status!='') { 
     debmes('Status: '.$status.' '.$device['DEV_IP']);
     $status=json_decode($status);
     $dps=$status->dps;
     foreach ($dps as $k=>$d){
      if (is_bool($d)) {
       $d=($d)?1:0;
      } 
      if ($k=='1'){
       $k='state';
      }
      $this->processCommand($device['ID'],$k,$d);
     }
     $dps=json_encode($dps);
     $data=$dps;     
     $this->processCommand($device['ID'],'report',$data);


    }
   }
  }

  function Tuya_send_receive( $payload,$local_ip) {
   $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
   $buf='';
   if (socket_connect($socket, $local_ip, 6668)) {
    socket_send($socket, $payload, strlen($payload), 0);

    $buf=socket_read (  $socket , 1024 , PHP_BINARY_READ  );


   }
   socket_close($socket);
   return $buf;
  }

  function Tuya_Discovery_Devices($token){
   $sURL = 'https://px1.tuyaeu.com/homeassistant/skill';

   $header = [
            'name'           => 'Discovery',
            'namespace'      => 'discovery',
            'payloadVersion' => 1,
        ];

    $payload['accessToken'] = $token;

    $data = [
            'header'  => $header,
            'payload' => $payload,
        ];
 

   $aHTTP = array(
   'http' => 
    array(
    'method'  => 'POST', 
    'header'  => 'Content-Type: application/json',
    'content' => json_encode($data, JSON_FORCE_OBJECT)
    )
   );
   $context = stream_context_create($aHTTP);
   $contents = file_get_contents($sURL, false, $context);
   $result=json_decode($contents);
  // debmes('Tuya Web content:'.$contents);
   foreach ($result->payload->devices as $device) {
    
    $rec=SQLSelectOne('select * from tudevices where DEV_ID="'.$device->id.'"');

    if ($rec==NULL) {
     $rec['TITLE']=$device->name;
     $rec['DEV_ICON']= $device->icon;
     $rec['DEV_ID']= $device->id;
     $rec['TYPE']=$device->dev_type;

     $rec['ID']=SQLInsert('tudevices',$rec);
    }

    $data='';
    foreach($device->data as $key => $value) {
      if (is_bool($value)) {
       $value=(($value) ? 1:0);
       $data.=$key.':'.(($value) ? 1:0).' ';
      } else {
       $data.=$key.':'.$value.' ';
      }
      $this->processCommand($rec['ID'], $key, $value);
    }

    $this->processCommand($rec['ID'], 'report', $data);
   // debmes('Tuya Web :'.$data);
     
    }
   }

   function TuyaRemoteMsg($dev_id,$value){
    $token=$this->RefreshToken();
    $sURL = 'https://px1.tuyaeu.com/homeassistant/skill';

        $header = [
            'name'           => 'turnOnOff',
            'namespace'      => 'control',
            'payloadVersion' => 1,
        ];
        $payload['value']=$value;
        $payload['accessToken'] = $token;
        $payload['devId']=$dev_id;

        $data = [
            'header'  => $header,
            'payload' => $payload,
        ];
 

   $aHTTP = array(
   'http' => 
    array(
    'method'  => 'POST', 
    'header'  => 'Content-Type: application/json',
    'content' => json_encode($data, JSON_FORCE_OBJECT)
    )
    );
    $context = stream_context_create($aHTTP);  
    $contents = file_get_contents($sURL, false, $context);
    $result=json_decode($contents);
    return $result;
   }

   function processCommand($device_id, $command, $value, $params = 0) {
		
		$cmd_rec = SQLSelectOne("SELECT * FROM tucommands WHERE DEVICE_ID=".(int)$device_id." AND TITLE LIKE '".DBSafe($command)."'");
		
		if (!$cmd_rec['ID']) {
			$cmd_rec = array();
			$cmd_rec['TITLE'] = $command;
			$cmd_rec['DEVICE_ID'] = $device_id;
			$cmd_rec['ID'] = SQLInsert('tucommands', $cmd_rec);
		}

		$old_value = $cmd_rec['VALUE'];

		$cmd_rec['VALUE'] = $value;
		$cmd_rec['UPDATED'] = date('Y-m-d H:i:s');
		SQLUpdate('tucommands', $cmd_rec);
		
		if ($old_value == $value) return;

		if ($cmd_rec['LINKED_OBJECT'] && $cmd_rec['LINKED_PROPERTY']) {
			setGlobal($cmd_rec['LINKED_OBJECT'] . '.' . $cmd_rec['LINKED_PROPERTY'], $value, array($this->name => '0'));
		}
		
		if ($cmd_rec['LINKED_OBJECT'] && $cmd_rec['LINKED_METHOD']) {
			if (!is_array($params)) {
				$params = array();
			}
			$params['VALUE'] = $value;
			callMethodSafe($cmd_rec['LINKED_OBJECT'] . '.' . $cmd_rec['LINKED_METHOD'], $params);
		}

	}


   function propertySetHandle($object, $property, $value) {

    $properties = SQLSelect("SELECT tucommands.*, tudevices.DEV_ID,tudevices.LOCAL_KEY,tudevices.DEV_IP FROM tucommands LEFT JOIN tudevices ON tudevices.ID=tucommands.DEVICE_ID WHERE tucommands.LINKED_OBJECT LIKE '".DBSafe($object)."' AND tucommands.LINKED_PROPERTY LIKE '".DBSafe($property)."'");

    $total = count($properties);
   
    if ($total) {
     $dps_name=$properties[0]['TITLE'];
     if ($properties[0]['LOCAL_KEY']==NULL or $properties[0]['DEV_IP']==NULL) {

      if ($dps_name=='state') {
       $this->TuyaRemoteMsg($properties[0]['DEV_ID'],$value);
      }
     } else {
      $mdev=strpos($properties[0]['DEV_ID'],'_');
      if ($mdev>0) {
       $dev_id=substr($properties[0]['DEV_ID'],0,$mdev);
       if ($properties[0]['TITLE']=='state') $dps_name=substr($properties[0]['DEV_ID'],$mdev+1);
      } else {
       if ($properties[0]['TITLE']=='state') $dps_name='1';
       $dev_id=$properties[0]['DEV_ID'];
      }

      $dps='{"'.$dps_name.'":'.(($value==1)?'true':'false').'}';

      $this->TuyaLocalMsg('SET',$dev_id,$properties[0]['LOCAL_KEY'],$properties[0]['DEV_IP'],$dps);
     }
     $rec=SQLSelect("select * from tucommands where ID=".$properties[0]['ID']);
     $rec[$property]=$value;
     SQLUpdate('tucommands',$rec);

   //  }
      
    }
   }

   /**
    * Install
    *
    * Module installation routine
    *
    * @access private
    */
   function install($data = '')
   {
      setGlobal('cycle_tuyaControl', 'restart');
      parent::install();
   }

   /**
    * Uninstall
    *
    * Module uninstall routine
    *
    * @access public
    */
   function uninstall()
   {
      SQLExec('DROP TABLE IF EXISTS tudevices');
      SQLExec('DROP TABLE IF EXISTS tucommands');
      parent::uninstall();
   }

   /**
    * dbInstall
    *
    * Database installation routine
    *
    * @access private
    */
   function dbInstall($data = '')
   {

      $data = <<<EOD
 tudevices: ID int(10) unsigned NOT NULL auto_increment
 tudevices: TITLE varchar(100) NOT NULL DEFAULT ''
 tudevices: TYPE varchar(100) NOT NULL DEFAULT ''
 tudevices: DEV_ICON varchar(100) NOT NULL DEFAULT ''
 tudevices: DEV_ID varchar(255) NOT NULL DEFAULT ''
 tudevices: LOCAL_KEY varchar(255) NOT NULL DEFAULT ''
 tudevices: DEV_IP varchar(255) NOT NULL DEFAULT ''
 tudevices: DEV_INTERVAL int(10) NOT NULL DEFAULT 5
 tudevices: UPDATED datetime

 tucommands: ID int(10) unsigned NOT NULL auto_increment
 tucommands: TITLE varchar(100) NOT NULL DEFAULT ''
 tucommands: VALUE varchar(255) NOT NULL DEFAULT ''
 tucommands: SDEVICE_TYPE varchar(255) NOT NULL DEFAULT ''
 tucommands: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 tucommands: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 tucommands: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 tucommands: LINKED_METHOD varchar(100) NOT NULL DEFAULT ''
 tucommands: UPDATED datetime

EOD;

      parent::dbInstall($data);
   }
// --------------------------------------------------------------------
}
/*
*
* 
*
*/
