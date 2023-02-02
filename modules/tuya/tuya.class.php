<?php
/**
* Tuya
* @package project
* @author <fandaymon@gmail.com>
* @copyright 2019-2021 (c)
* @version 2021.08.28
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

      if ((time() - (int)gg('cycle_tuyaRun')) < $this->config['TUYA_INTERVAL']+30) {
         $out['CYCLERUN'] = 1;
      } else {
         $out['CYCLERUN'] = 0;
      }
      
      if ((time() - (int)gg('cycle_local_tuyaRun')) < 10 * 2) {
         $out['LOCAL_CYCLERUN'] = 1;
      } else {
         $out['LOCAL_CYCLERUN'] = 0;
      }

      if ((time() - (int)gg('cycle_tuya_iotRun')) < 10 * 2) {
         $out['IOT_CYCLERUN'] = 1;
      } else {
         $out['IOT_CYCLERUN'] = 0;
      }


      $out['TUYA_USERNAME'] = $this->config['TUYA_USERNAME'];
      $out['TUYA_PASSWD'] = $this->config['TUYA_PASSWD'];
      $out['TUYA_INTERVAL'] = $this->config['TUYA_INTERVAL'];
      $out['TUYA_LOCAL_INTERVAL'] = $this->config['TUYA_LOCAL_INTERVAL'];
      $out['TUYA_BZTYPE'] = $this->config['TUYA_BZTYPE'];
      $out['TUYA_CCODE'] = $this->config['TUYA_CCODE'];
      $out['TUYA_SID'] = $this->config['TUYA_SID'];
      $out['TUYA_WEB'] = $this->config['TUYA_WEB'];
      $out['TUYA_WEB_INTERVAL'] = $this->config['TUYA_WEB_INTERVAL'];
      $out['TUYA_WEB_ENDPOINT'] = $this->config['TUYA_WEB_ENDPOINT'];
      $out['TUYA_CYCLE_DEBUG'] = $this->config['TUYA_CYCLE_DEBUG'];
      $out['TUYA_HA'] = $this->config['TUYA_HA'];
      $out['TUYA_IOT'] = $this->config['TUYA_IOT'];
      $out['TUYA_CLIENT_ID'] = $this->config['TUYA_CLIENT_ID'];
      $out['TUYA_CLIENT_SECRET'] = $this->config['TUYA_CLIENT_SECRET'];

      if ($this->view_mode=='update_settings') {

         global $tuya_username;
         $this->config['TUYA_USERNAME'] = $tuya_username;

         global $tuya_passwd;
         $this->config['TUYA_PASSWD'] = $tuya_passwd;
         
         global $tuya_ha;
         $this->config['TUYA_HA'] = $tuya_ha;

         global $tuya_interval;
         $this->config['TUYA_INTERVAL'] = $tuya_interval;
         
         global $tuya_local_interval;
         $this->config['TUYA_LOCAL_INTERVAL'] = $tuya_local_interval;

         global $tuya_sid;
         $this->config['TUYA_SID'] = $tuya_sid;

         global $tuya_bztype;
         if (!isset($tuya_bztype) or empty($tuya_bztype)) {
            $tuya_bztype = 'tuya';
         } elseif ($tuya_bztype != 'tuya' and $tuya_bztype != 'smart_life') {
            $tuya_bztype = 'tuya';
         } 
         if ($tuya_bztype != $this->config['TUYA_BZTYPE']) {
            $this->config['TUYA_SID'] = '';
         }    
         $this->config['TUYA_BZTYPE'] = $tuya_bztype;

         global $tuya_ccode;
         $this->config['TUYA_CCODE'] = $tuya_ccode;

         
         global $tuya_web;
         $this->config['TUYA_WEB'] = $tuya_web;
         
         global $tuya_web_interval;
         $this->config['TUYA_WEB_INTERVAL'] = $tuya_web_interval;

         global $tuya_web_endpoint;
         $this->config['TUYA_WEB_ENDPOINT'] = $tuya_web_endpoint;

         global $tuya_iot;
         $this->config['TUYA_IOT'] = $tuya_iot;
         global $tuya_client_id;
         $this->config['TUYA_CLIENT_ID'] = $tuya_client_id;
         global $tuya_client_secret;
         $this->config['TUYA_CLIENT_SECRET'] = $tuya_client_secret;

         
         global $tuya_cycle_debug;
         $this->config['TUYA_CYCLE_DEBUG'] = $tuya_cycle_debug;

         if ($this->config['TUYA_HA']) {
            $token=json_decode($this->getToken($tuya_username,$tuya_passwd,$tuya_bztype,$tuya_ccode));
            //debmes($token->responseStatus);
            if (isset($token->responseStatus) && $token->responseStatus === 'error') {
               $message = $token->responseMsg;
               debmes($message);
            }
            $this->config['TUYA_ACCESS_TOKEN']=$token->access_token;
            $this->config['TUYA_REFRESH_TOKEN']=$token->refresh_token;
            $this->config['TUYA_TIME']=time()+$token->expires_in;
            $this->Tuya_Discovery_Devices($token->access_token);      


         }
         $this->saveConfig();
         
         if ($this->config['TUYA_WEB']) {
            if (is_null($this->config['TUYA_WEB_ENDPOINT']) or $this->config['TUYA_WEB_ENDPOINT']=='' or $this->config['TUYA_WEB_ENDPOINT']!='https://a1.tuyaeu.com/api.json') {
               $this->config['TUYA_WEB_ENDPOINT']='https://a1.tuyaeu.com/api.json';
               $this->saveConfig();
            }
            if ($this->config['TUYA_SID']==NULL || $this->config['TUYA_SID']=='') {
               $result=$this->Tuya_Web_Login();
            }
            $this->Tuya_Web_Discovery_Devices();
         }         

         setGlobal('cycle_tuyaControl', 'restart');
         setGlobal('cycle_local_tuyaControl', 'restart');
         setGlobal('cycle_tuya_iotControl', 'restart');


         $this->redirect('?');
      }
      if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
         $out['SET_DATASOURCE'] = 1;
      }

      if ($this->data_source == 'tudevices' || $this->data_source == '') {
         if ($this->view_mode == '' || $this->view_mode == 'search_tudevices') {
            if (isset($this->tab) and $this->tab == 'scene') $this->getScenes();

            $this->search_tudevices($out);
         }
         if ($this->view_mode == 'edit_tudevices') {
            $this->edit_tudevices($out, $this->id);
         }
         if ($this->view_mode == 'delete_tudevices') {
            $this->delete_tudevices($this->id);
            $this->redirect("?data_source=tudevices&tab=". $this->tab);
         }
         if ($this->view_mode == 'refresh_tudevices') {
            $this->refresh_tudevices($this->id);
            $this->redirect("?data_source=tudevices&tab=". $this->tab);
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
            if ($op == 'scan') {
               $this->ScanDevices();
               exit;
            }
            if ($op == 'run_scene') {
               global $dev_id;
               TuyaScene($dev_id);
               exit;
            }
            if ($op == 'info_scene') {
               global $dev_id;
               $this->InfoScene($dev_id);
               exit;
            }
    
            if ($op == 'run_ir') {
               global $dev_id;
               global $id;
               
               $rec = SQLSelectOne("SELECT TITLE FROM tuircommand WHERE ID=" .$id);
               TuyaIR($dev_id, $rec['TITLE']);
               exit;
            }    

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
       
       if ($rec['IR_FLAG'] ) {
         SQLExec("DELETE FROM tuircommand WHERE DEVICE_ID='" . $rec['ID'] . "'");
       }   

       SQLExec("DELETE FROM tucommands WHERE DEVICE_ID='" . $rec['ID'] . "'");
       SQLExec("DELETE FROM tudevices WHERE ID='" . $rec['ID'] . "'");
   }

   function refresh_tudevices($id)
   {
       $rec = SQLSelectOne("SELECT * FROM tudevices WHERE ID='$id'");
       
       if ($rec['IR_FLAG'] ) {
         SQLExec("DELETE FROM tuircommand WHERE DEVICE_ID='" . $rec['ID'] . "'");
       }   

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

 
   function ScanDevices() {
      $udp_key = md5( 'yGAdlopoPVldABfn');
      $udp_key = hex2bin($udp_key);

      $devices=array();

      $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
      socket_bind($socket, "0.0.0.0", 6667);
      socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 2, "usec" => 0));


      $socket1 = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
      socket_bind($socket1, "0.0.0.0", 6666);
      socket_set_option($socket1, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 1, "usec" => 0));


      
      echo '<H4>В локальной сети найдены следующие устройства:</H2>';
      echo '<table>';
      
      $start_time = time();

      for ($i = 1; $i <= 20; $i++) {

         $from = '';
         $port = 0;
         socket_recvfrom($socket, $buf, 2048, 0, $from, $port);

         $data = substr($buf,20,-8);
         $result = openssl_decrypt(($data), 'AES-128-ECB', $udp_key,OPENSSL_RAW_DATA);
         $result = json_decode($result, true);

         if (in_array($result['gwId'], $devices) == false) {
            echo '<tr>';
            array_push($devices, $result['gwId']);
            
            if ($result['version'] == '3.3') {
               $version = 0;
            } else {
               $version = 1;
            }   
            $rec = SQLSelectOne("SELECT * FROM tudevices WHERE DEV_ID='" . $result['gwId'] . "'"); 
            if (IsSet($rec['ID']) and ($rec['DEV_IP'] != $result['ip'] or $rec['VER_3_1'] != $version )) {
               $rec['DEV_IP'] = $result['ip'];
               $rec['VER_3_1'] = $version;
               SQLUpdate('tudevices', $rec);
             }
            echo '<td><b>'.$rec['TITLE'].'</b></td>'; 
            echo '<td>'.$result['gwId'].'</td>';
            echo '<td> ('.$result['version'].') </td>';
            echo '<td>'.$result['ip'].'</td>';
            echo '</tr>';  
         }
         if (socket_recvfrom($socket1, $buf, 2048, 0, $from, $port)) {

            $data = substr($buf,20,-8);
            $result = json_decode($data, true);

            if (in_array($result['gwId'], $devices) == false) {
               echo '<tr>';
               array_push($devices, $result['gwId']);
               
               if ($result['version'] == '3.3') {
                  $version = 0;
               } else {
                  $version = 1;
               }   
               $rec = SQLSelectOne("SELECT * FROM tudevices WHERE DEV_ID='" . $result['gwId'] . "'"); 
               if (IsSet($rec['ID']) and ($rec['DEV_IP'] != $result['ip'] or $rec['VER_3_1'] != $version )) {
                  $rec['DEV_IP'] = $result['ip'];
                  $rec['VER_3_1'] = $version;
                  SQLUpdate('tudevices', $rec);
                }
               echo '<td><b>'.$rec['TITLE'].'</b></td>'; 
               echo '<td>'.$result['gwId'].'</td>';
               echo '<td> ('.$result['version'].') </td>';
               echo '<td>'.$result['ip'].'</td>';
               echo '</tr>';  
            }
            
         }   
         
         if ((time() - $start_time) >10) break; 
         
      }
      echo '</table>';
      socket_close($socket);
      socket_close($socket1);

   }
   
   function getScenes() {
      $this->getConfig();
      if ($this->config['TUYA_WEB']) {
         $apiResult = $this->TuyaWebRequest(['action'=> 'tuya.m.location.list',
                                          'requiresSID'=> 1]);

         $result=json_decode($apiResult , true);
         $gid= $result['result'][0] ['groupId'];


         $action = "tuya.m.linkage.rule.query";

         $apiResult = $this->TuyaWebRequest(['action'=>$action,
                                                   'gid'=>$gid,
                                                   'requiresSID'=> 1]);
         $result=json_decode($apiResult , true);
         
         if ($result['result']) {
            foreach($result['result'] as $scene) {
               $rec = SQLSelectOne("SELECT * FROM tudevices WHERe DEV_ID='" . $scene['id'] . "' AND TYPE='scene';");
               if ($rec) {
                  if ($rec['TITLE'] != $scene['name']) {
                     $rec['TITLE'] = $scene['name'];
                     SQLUpdate('tudevices', $rec);
                  }   
               } else {
                  $rec = array();
                  $rec['DEV_ID'] = $scene['id'];
                  $rec['TITLE'] = $scene['name'];
                  $rec['TYPE'] = 'scene';
                  SQLInsert('tudevices', $rec);
               }
            }   
         }   
      }   
   }

   function InfoScene($dev_id) {
      debmes('Info Scene running');
      $this->getConfig();
      if ($this->config['TUYA_WEB']) {
         $apiResult = $this->TuyaWebRequest(['action'=> 'tuya.m.location.list',
                                          'requiresSID'=> 1]);

         $result=json_decode($apiResult , true);
         $gid= $result['result'][0] ['groupId'];


         $action = "tuya.m.linkage.rule.query";

         $apiResult = $this->TuyaWebRequest(['action'=>$action,
                                                   'gid'=>$gid,
                                                   'requiresSID'=> 1]);
         $result=json_decode($apiResult , true);
         
         if ($result['result']) {
            foreach($result['result'] as $scene) {
               debmes('Scene: ' . $scene['id']);
               if ($scene['id'] == $dev_id) {
                  foreach($scene['actions'] as $action) {
                     echo var_dump($action);
                     echo '<BR>------------------<BR>';
                  }
               }
            }
            exit;   
         }   
      }   
   }

   function getToken($username,$passwd,$bztype,$ccode) {
    $sURL = 'https://px1.tuyaeu.com/homeassistant/auth.do';
    $sPD = "userName=".$username."&password=".$passwd."&countryCode=".$ccode."&bizType=".$bztype."&from=tuya"; 
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
	    'content' => $sPD
	  )
      );
      $context = stream_context_create($aHTTP);
      $contents = file_get_contents($sURL, false, $context);
        
      $token=json_decode($contents);
      
      if ($token->access_token) {
         $this->config['TUYA_ACCESS_TOKEN'] = $token->access_token;
         $this->config['TUYA_REFRESH_TOKEN'] = $token->refresh_token;
         $this->config['TUYA_TIME'] = time() + $token->expires_in;

         $this->saveConfig();
       } else {
         $tuya_username = $this->config['TUYA_USERNAME'];
         $tuya_passwd = $this->config['TUYA_PASSWD'];
         $tuya_interval = $this->config['TUYA_INTERVAL'];
         $tuya_bztype = $this->config['TUYA_BZTYPE'] ;
         $tuya_ccode = $this->config['TUYA_CCODE'];
	      
         $token=json_decode($this->getToken($tuya_username,$tuya_passwd,$tuya_bztype,$tuya_ccode));
         //debmes($token->responseStatus);
         if (isset($token->responseStatus) && $token->responseStatus === 'error') {
            $message = $token->responseMsg;
            debmes("Can't get tooken: ". $message);
         }
         $this->config['TUYA_ACCESS_TOKEN']=$token->access_token;
         $this->config['TUYA_REFRESH_TOKEN']=$token->refresh_token;
         $this->config['TUYA_TIME']=time()+$token->expires_in;
         $this->Tuya_Discovery_Devices($token->access_token);      

         $this->saveConfig();
       }
      }

     return $this->config['TUYA_ACCESS_TOKEN'];
   }

  function TuyaLocalEncrypt($command, $json, $local_key,$ver_3_1=false) {
   $prefix="000055aa00000000000000";
   $suffix="000000000000aa55";
   if ($ver_3_1) {
      $json_payload=$json;
   } else {   
      $json_payload=openssl_encrypt($json, 'AES-128-ECB', $local_key, OPENSSL_RAW_DATA);
   }
   if ($command != "0a" and $command != "12" and $ver_3_1 == false) {
    $json_payload = hex2bin("332E33000000000000000000000000" . bin2hex($json_payload));
   }


   $postfix_payload = hex2bin(bin2hex($json_payload) . $suffix);
   $postfix_payload_hex_len = dechex(strlen($postfix_payload));

   if (strlen($postfix_payload_hex_len)>2) {
    $buffer = hex2bin($prefix . $command . '00000' . $postfix_payload_hex_len ) . $postfix_payload;

   } else { 
    $buffer = hex2bin($prefix . $command . '000000' . $postfix_payload_hex_len ) . $postfix_payload;
   }

   $buffer=bin2hex($buffer);
   $buffer1=strtoupper(substr($buffer,0,-16));

   $hex_crc = dechex(crc32(hex2bin($buffer1)));
   $hex_crc=str_pad($hex_crc,8,"0",STR_PAD_LEFT);
   $buffer=substr($buffer,0,-16) .($hex_crc).substr($buffer,-8);
   return hex2bin($buffer);
  }
  
       
  function TuyaLocalMsg($command,$dev_id,$local_key,$local_ip,$data='',$cid='',$ver_3_1=false) {

   $prefix="000055aa00000000000000";
   $suffix="000000000000aa55";
   
   if ($ver_3_1) {
      $gw_name='uid';
   } else {
      $gw_name='gwId';
   }      
   if ($command=='STATUS') {
    $hexByte="0a";
    $json='{"'.$gw_name.'":"'.$dev_id.'","devId":"'.$dev_id.'"}';

   } else {
    $hexByte="07";
    if ($cid=='') {
      $dps=$data;
      $json='{"'.$gw_name.'":"'.$dev_id.'","devId":"'.$dev_id.'", "t": "'.time().'", "dps": ' . $dps . '}';
    } else {
      $json='{"dps":'.$data.',"cid":"'.$cid.'","t":'.time().'}';
    }     
   }

   if ($ver_3_1) {
      if ($command != 'STATUS') {
       $json_payload = base64_encode(openssl_encrypt($json, 'AES-128-ECB', $local_key, OPENSSL_RAW_DATA));
       
       $preMd5String = 'data=' . $json_payload . '||lpv=' .   hex2bin("332E31") . '||' . $local_key;

       $hexdigest = md5($preMd5String );
       $json_payload = hex2bin("332E31") . substr($hexdigest,8,16) . $json_payload;
      } else {
       $json_payload = $json;
      }   
   } else {   
    $json_payload=openssl_encrypt($json, 'AES-128-ECB', $local_key, OPENSSL_RAW_DATA);
    if ($command != 'STATUS') {
     $json_payload = hex2bin("332E33000000000000000000000000" . bin2hex($json_payload));
    }

   }
   
   

   $postfix_payload = hex2bin(bin2hex($json_payload) . $suffix);
   $postfix_payload_hex_len = dechex(strlen($postfix_payload));


   if (strlen($postfix_payload_hex_len)>2) {
      $buffer = hex2bin($prefix . $hexByte . '00000' . $postfix_payload_hex_len ) . $postfix_payload;

   } else { 
      $buffer = hex2bin($prefix . $hexByte . '000000' . $postfix_payload_hex_len ) . $postfix_payload;
   }
   $buffer=bin2hex($buffer);
   $buffer1=strtoupper(substr($buffer,0,-16));

   $hex_crc = dechex(crc32(hex2bin($buffer1)));
   $hex_crc=str_pad($hex_crc,8,"0",STR_PAD_LEFT);
   $buffer=substr($buffer,0,-16) .($hex_crc).substr($buffer,-8);
   $data=$this->Tuya_send_receive(hex2bin($buffer),$local_ip);
   $result = substr($data,20,-8);
   
   if (mb_substr($result,0,1) == '{' ) {
    return $result;
   } else if ($ver_3_1) {
      $result = openssl_decrypt(base64_decode($result), 'AES-128-ECB', $local_key, OPENSSL_RAW_DATA);
   } else {       
      $result = openssl_decrypt($result, 'AES-128-ECB', $local_key, OPENSSL_RAW_DATA);
   } 
   return $result;
  }

  function requestLocalStatus(){
   $devices=SQLSelect("SELECT * FROM tudevices WHERE LOCAL_KEY!='' and DEV_IP!='' ORDER BY DEV_ID");
   foreach($devices as $device) {
    $mdev=strpos($device['DEV_ID'],'_');
    if ($mdev>0 and substr($device['DEV_ID'],$mdev+1)==1) {
       $dev_id=substr($device['DEV_ID'],0,$mdev);
       $status='';
       $status=$this->TuyaLocalMsg('STATUS',$dev_id,$device['LOCAL_KEY'],$device['DEV_IP'],'','',$device['VER_3_1']);
       
       if ($status!='') { 
       // debmes('Status: '.$status.' '.$device['DEV_IP']);
        $status=json_decode($status);
        $dps=$status->dps;
        foreach ($dps as $k=>$d){
         if (is_bool($d)) {
          $d=($d)?1:0;
         } 
         if ($k=='1'){
          $k='state';
          $this->processCommand($device['ID'],$k,$d);
         } elseif ($k<8) {
          $dev_k=SQLSelectOne('SELECT ID FROM tudevices WHERE DEV_ID="' . $dev_id .'_' .$k.'"');
          $k='state';
          $this->processCommand($dev_k['ID'],$k,$d);
         } else {
          $this->processCommand($device['ID'],$k,$d);

         }
         
       }
       $dps=json_encode($dps);
       $data=$dps;     
       $this->processCommand($device['ID'],'report',$data);
      }


  
    } else {
     $status=$this->TuyaLocalMsg('STATUS',$device['DEV_ID'],$device['LOCAL_KEY'],$device['DEV_IP'],'','',$device['VER_3_1']);
     if ($status!='') { 
      //debmes('Status: '.$status.' '.$device['DEV_IP']);
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
  }

  function Tuya_send_receive( $payload,$local_ip) {
   $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
   socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 1, "usec" => 0));
   //socket_set_option($socket, SOL_SOCKET, TCP_NODELAY, 1);

   $buf='';
   
   if (socket_connect($socket, $local_ip, 6668)) {
    for ($i=0;$i<3;$i++) {
     $send=socket_send($socket, $payload, strlen($payload), 0);
     if ($send!=strlen($payload)) {
       //debmes( date('y-m-d h:i:s') . ' sended '.$send .' from ' .strlen($payload) . 'ip' . $local_ip);
     }
     $reciv=socket_recv ( $socket , $buf , 2048 ,0);
     //debmes( date('y-m-d h:i:s') . ' recived '.strlen($buf));
     if ($buf!='') break;
     sleep(1);
    }

   } else {  
    $err = socket_last_error($socket); 
    echo date('y-m-d h:i:s') .' ' .socket_strerror($err) . ' '. $local_ip ."\n";
   }
 
   socket_close($socket);
   return $buf;
  }

  function Tuya_Discovery_Devices($token){
   return;  
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
   if ($result->header->code != 'SUCCESS') {
    debmes('Tuya HA Web Error:'.$result->header->msg);
    return;
   } 
   foreach ($result->payload->devices as $device) {
    
      $rec=SQLSelectOne('select * from tudevices where DEV_ID="'.$device->id.'"');

      if ($rec==NULL) {
         $rec['TITLE']=$device->name;
         $rec['DEV_ICON']= $device->icon;
         $rec['DEV_ID']= $device->id;
         $rec['TYPE']=$device->dev_type;
         $rec['SEND12'] = 0;
         $rec['VER_3_1'] = 0;   
         $rec['VER_3_1'] = 0;   
         $rec['IR_FLAG'] = 0;

         $rec['ID']=SQLInsert('tudevices',$rec);
      }

      $data='';
      if ($rec['STATUS']==5) {
         foreach($device->data as $key => $value) {
            if (is_bool($value)) {
               $value=(($value) ? 1:0);
               $data.=$key.':'.(($value) ? 1:0).' ';
            } else if ($value=='true') {
               $value=1;
               $data.=$key.':'.$value.' ';
            } else if ($value=='false') {

               $value=0;
               $data.=$key.':'.$value.' ';
            } else {
               $data.=$key.':'.$value.' ';
            }
            $this->processCommand($rec['ID'], $key, $value);
         }
      }
    
   }
  }
   
    function TuyaWebRequest($options, $v='1.0') {
     $this->getConfig();  
     $sid=$this->config['TUYA_SID'];
     $endpoint = $this->config['TUYA_WEB_ENDPOINT'];
     $d = time();
     if ($this->config['TUYA_BZTYPE'] == 'tuya') {
      $key = '3fjrekuxank9eaej3gcx';
      $secret ='aq7xvqcyqcnegvew793pqjmhv77rneqc';
      $secret2='vay9g59g9g99qf3rtqptmc3emhkanwkx';
      $certSign='93:21:9F:C2:73:E2:20:0F:4A:DE:E5:F7:19:1D:C6:56:BA:2A:2D:7B:2F:F5:D2:4C:D5:5C:4B:61:55:00:1E:40';
     } else {
      $key = 'ekmnwp9f5pnh3trdtpgy';
      $secret ='r3me7ghmxjevrvnpemwmhw3fxtacphyg';
      $secret2='jfg5rs5kkmrj5mxahugvucrsvw43t48x';
      $certSign='0F:C3:61:99:9C:C0:C3:5B:A8:AC:A5:7D:AA:55:93:A2:0C:F5:57:27:70:2E:A8:5A:D7:B3:22:89:49:F8:88:FE';
     }             
     $keyHmac = $certSign . '_' . $secret2 . '_' . $secret;
     if ($options['deviceID']) {
       $deviceID=$options['deviceID'];
     } else {  
       $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz0123456789abcdefghijklmnopqrstuvwxyz0123456789abcdefghijklmnopqrstuvwxyz';
       $deviceID = substr(str_shuffle($permitted_chars), 0, 44);
     }    


     $pairs = ['a' => $options['action'],
                 'deviceId'=> $deviceID,
                 'os'=> 'Linux',
                 'lang' => 'en',
                 'v' => $v,
                 'clientId' => $key,
                 'time' => $d];

     if ($options['data']) {
       $pairs['postData'] = json_encode($options['data']);
     }
     
     if ($options['gid']) {
       $pairs['gid'] = $options['gid'];
     }


     $pairs['et'] = '0.0.1';
     $pairs['ttid'] = $this->config['TUYA_BZTYPE'];
     $pairs['appVersion'] = '3.8.5';


     if ($options['requiresSID']==1) {
      $pairs['sid'] = $sid;
     }

     // Generate signature for request
     $valuesToSign = ['a', 'v', 'lat', 'lon', 'lang', 'deviceId', 'imei',
                        'imsi', 'appVersion', 'ttid', 'isH5', 'h5Token', 'os',
                        'clientId', 'postData', 'time', 'requestId', 'n4h5', 'sid',
                        'sp', 'et'];

     $sorted_pairs = $pairs;
     ksort($sorted_pairs );
     $strToSign = '';

     // Create string to sign
     foreach ($sorted_pairs as $key => $value) {
      if (!in_array($key,$valuesToSign) || empty($sorted_pairs [$key])) {
       continue;
     } else if ($key === 'postData') {
        if ($strToSign) {
          $strToSign .= '||';
        }
        $strToSign .= $key;
        $strToSign .= '=';
        $strToSign .= $this->Tuya_mobileHash($pairs[$key]);
     } else {
      if ($strToSign) {
        $strToSign .= '||';
      }
      $strToSign .= $key;
      $strToSign .= '=';
      $strToSign .= $pairs[$key];
     }
    }


    $pairs['sign']=hash_hmac('sha256',$strToSign,$keyHmac);
    $result='';

    $result =getURL($endpoint . '?'.  http_build_query($pairs),0);

    return $result;

   }
   

   function Tuya_mobileHash($hash) {
    $preHash = md5($hash);

    return substr($preHash,8, 8) .substr($preHash,0, 8) . substr($preHash,24, 8) . substr($preHash,16, 8);
   }
   
   function Tuya_Web_Login() {
     $this->getConfig();
     $region='EU';  
     $email=$this->config['TUYA_USERNAME'];
     $apiResult = $this->TuyaWebRequest(['action'=> 'tuya.m.user.email.token.create',
                                          'data'=>['countryCode'=>$region,
                                          'email'=>$email],
                                          'requiresSID'=> 0]);

     $result=json_decode($apiResult , true);
     if (!$result['success']) {
         debmes('Ошибка получения PublicKey:' . $result['errorCode']);
         return;
     }    
     $n= $result["result"]["publicKey"];
     $e = $result["result"]["exponent"];
     $token = $result["result"]["token"];

     $data=md5($this->config['TUYA_PASSWD']);
     if (extension_loaded('bcmath')) {
      $data_dec=$this->bytes_to_int($data);  
      $encryptedPass = bcpowmod($data_dec, $e, $n);
      $encryptedPass = str_pad($this->bcdechex($encryptedPass),256,'0',STR_PAD_LEFT);
     } else {   
      $a=exec('python3 '. __DIR__ .'/pow_python.py ' .$n . ' ' . $e . ' ' .$data);
      if ($a=='') {
         debmes('Питон не отработал');
         return;
      }   
      $encryptedPass=substr($a,2,strlen($a)-3);
     }
     $apiResult = $this->TuyaWebRequest(['action'=> 'tuya.m.user.email.password.login',
                                          'data'=> ['countryCode'=> $region,
                                                 'email'=>$email,
                                                 'passwd'=> $encryptedPass,
                                                 'ifencrypt'=> 1,
                                                 'options'=> ['group'=> 1],
                                                 'token'=> $token],
                                          'requiresSID'=> 0],'2.0');
     $result=json_decode($apiResult , true); 
     if (!$result['success']) {
         debmes('Не смог получить СИД. Ошибка:' . $result['errorCode']);
      } else {  
         $this->config['TUYA_SID'] = $result['result']['sid'];
         $this->config['TUYA_UID'] = $result['result']['uid'];
         $this->config['TUYA_ECODE'] = $result['result']['ecode'];
         $this->config['TUYA_PUBKEY']=$n;
         $this->config['TUYA_WEB_ENDPOINT'] = $result['result'] ['domain']['mobileApiUrl'] . '/api.json';
         $this->saveConfig();
      }
     return $result;
   }   
   
   function Tuya_Web_Scheme($gid) {
    $apiResult = $this->TuyaWebRequest(['action'=> 'tuya.m.device.ref.info.my.list',
                                          'gid'=>$gid,
                                          'requiresSID'=> 1]);  
    $result=json_decode($apiResult , true);

    $sc=array();

    foreach ($result['result'] as $scheme) {
         foreach (json_decode($scheme['schemaInfo']['schema'], true) as $dp) {
            $sc[$scheme['id']][$dp['id']]['mode']=$dp['mode'];
            $sc[$scheme['id']][$dp['id']]['code']=$dp['code'];
            $sc[$scheme['id']][$dp['id']]['min']=$dp['property']['min'];
            $sc[$scheme['id']][$dp['id']]['max']=$dp['property']['max'];
            $sc[$scheme['id']][$dp['id']]['scale']=$dp['property']['scale'];
            $sc[$scheme['id']][$dp['id']]['unit']=$dp['property']['unit'];
            $sc[$scheme['id']][$dp['id']]['type']=$dp['property']['type'];
            
            if (isset($dp['property']['range'])) {
               foreach ($dp['property']['range'] as $key => $value) {
                  $sc[$scheme['id']][$dp['id']]['range'][$key]=$value;
               }
            }
         }
    }
    
    return $sc;
  
   }
   

   function bchexdec($hex) {
        if(strlen($hex) == 1) {
            return hexdec($hex);
        } else {
            $remain = substr($hex, 0, -1);
            $last = substr($hex, -1);
            return bcadd(bcmul(16, $this->bchexdec($remain)), hexdec($last));
        }
    }


   function bytes_to_int($bytes) {
      $result = '0';
      for ($i = 0; $i < strlen($bytes); $i++) {
        $b=strval(ord($bytes[$i]));
        $result = bcadd(bcmul($result ,256) ,$b);
      }
      return $result;
   }
   
   
   function bcdechex($dec) {
        $last = bcmod($dec, 16);
        $remain = bcdiv(bcsub($dec, $last), 16);

        if($remain == 0) {
            return dechex($last);
        } else {
            return $this->bcdechex($remain).dechex($last);
        }
    }
   
   function Tuya_Web_Status() {
      $apiResult = $this->TuyaWebRequest(['action'=> 'tuya.m.location.list',
                                          'requiresSID'=> 1]);
      $result=json_decode($apiResult , true);

      foreach ( $result['result'] as $home) {
		$gid= $home['groupId'];
		
		$apiResult = $this->TuyaWebRequest(['action'=> 'tuya.m.my.group.device.list',
                                          'gid'=>$gid,
                                          'requiresSID'=> 1]);

		$result=json_decode($apiResult , true);
		foreach ( $result['result'] as $device) {

         
            $rec=SQLSelectOne('select * from tudevices where DEV_ID="'.$device['devId'].'"');

            if (isset($device['moduleMap']['infrared'])) {
               $ir_flag = 1;
            } else {
               $ir_flag = 0;
            }   

            if ($rec==NULL) {
   
               $rec['IR_FLAG'] = $ir_flag;
               $rec['TITLE']=$device['name'] ;
               $rec['DEV_ICON']= $device['iconUrl'];
               $rec['DEV_ID']= $device['devId'];
               $rec['TYPE']=$device['category'];
               $rec['LOCAL_KEY']=$device['localKey'];
               $rec['PRODUCT_ID']=$device['productId'];
               $rec['GID_ID']=$gid;
               $rec['MESH_ID']=$device['meshId'];
               $rec['MAC'] = $device['mac'];
               $rec['SEND12'] = 0;
               $rec['VER_3_1'] = 0;
               $rec['STATUS'] = 0;
               $rec['CONTROL'] = 0;      

               $rec['ID']=SQLInsert('tudevices',$rec);
            } else {
               if (is_null($rec['MAC'])) $rec['MAC'] =''; 
               if (is_null($rec['LOCAL_KEY'])) $rec['LOCAL_KEY'] =''; 
               if (is_null($rec['MESH_ID'])) $rec['MESH_ID'] ='';
               if (is_null($rec['IR_FLAG'])) $rec['IR_FLAG'] = 0;
               
               if ($rec['IR_FLAG'] != $ir_flag or $rec['MAC'] != $device['mac'] or $rec['LOCAL_KEY']!=$device['localKey'] or $rec['PRODUCT_ID']!=$device['productId'] or $rec['GID_ID']!=$gid or $rec['MESH_ID']!=$device['meshId']) {
                 $rec['LOCAL_KEY']=$device['localKey'];
                 $rec['PRODUCT_ID']=$device['productId'];
                 $rec['GID_ID']=$gid;
                 $rec['MESH_ID']=$device['meshId'];
                 $rec['MAC'] = $device['mac'];
                 $rec['IR_FLAG'] = $ir_flag;
                 
                 $rec['ID']=SQLUpdate('tudevices',$rec);
               }

            }

            $data='';
            if (substr($device['categoryCode'],0,3)=='wf_') {
               if ($rec['STATUS']==0) {
                  if ($device['moduleMap']['wifi']['isOnline'] ) {
                     $this->processCommand($rec['ID'], 'online', 1);
                  } else {
                     $this->processCommand($rec['ID'], 'online', 0);
                  }
               }
            } else if (substr($device['categoryCode'],0,4)=='zig_') {
               if ($device['moduleMap']['zigbee']['isOnline'] ) {
                  $this->processCommand($rec['ID'], 'online', 1);
               } else {
                  $this->processCommand($rec['ID'], 'online', 0);
               }
            } else if (substr($device['categoryCode'],0,4)=='sub_') {
               if ($device['moduleMap']['subpieces']['isOnline'] ) {
                  $this->processCommand($rec['ID'], 'online', 1);
               } else {
                  $this->processCommand($rec['ID'], 'online', 0);
               }
            }           
			
            if ($rec['STATUS']==0) {
               foreach($device['dps'] as $key => $value) {

                  if (is_bool($value)) {
                     $value=(($value) ? 1:0);
                     $data.=$key.':'.(($value) ? 1:0).' ';
                  } else if ($value=='true') {
                     $value=1;
                     $data.=$key.':'.$value.' ';
                  } else if ($value=='false') {

                     $value=0;
                     $data.=$key.':'.$value.' ';
                  } else {
                     $data.=$key.':'.$value.' ';
                  }
                  $this->processCommand($rec['ID'], $key, $value);
               }
       
            }

        }
	  }
   } 
   
   function Tuya_Web_Discovery_Devices() {
      $apiResult = $this->TuyaWebRequest(['action'=> 'tuya.m.location.list',
                                          'requiresSID'=> 1]);
      $result=json_decode($apiResult , true);

      foreach ( $result['result'] as $home) {
		$gid= $home['groupId'];
		$sc=$this ->Tuya_Web_Scheme($gid);
		$apiResult = $this->TuyaWebRequest(['action'=> 'tuya.m.my.group.device.list',
                                          'gid'=>$gid,
                                          'requiresSID'=> 1]);

		$result=json_decode($apiResult , true);
		foreach ( $result['result'] as $device) {

         
            $rec=SQLSelectOne('select * from tudevices where DEV_ID="'.$device['devId'].'"');

            if (isset($device['moduleMap']['infrared'])) {
               $ir_flag = 1;
            } else {
               $ir_flag = 0;
            }                

            if ($rec==NULL) {
    
               $rec['IR_FLAG'] = $ir_flag;
               $rec['TITLE']=$device['name'] ;
               $rec['DEV_ICON']= $device['iconUrl'];
               $rec['DEV_ID']= $device['devId'];
               $rec['TYPE']=$device['category'];
               $rec['LOCAL_KEY']=$device['localKey'];
               $rec['PRODUCT_ID']=$device['productId'];
               $rec['GID_ID']=$gid;
               $rec['MESH_ID']=$device['meshId']; 
               $rec['MAC'] = $device['mac']; 
               $rec['SEND12'] = 0;
               $rec['VER_3_1'] = 0;
               $rec['STATUS'] = 0;
               $rec['CONTROL'] = 0;
               $rec['UUID'] = $device['uuid'];             

               $rec['ID'] = SQLInsert('tudevices', $rec);
            } else {

               if (is_null($rec['MAC'])) $rec['MAC'] =''; 
               if (is_null($rec['LOCAL_KEY'])) $rec['LOCAL_KEY'] =''; 
               if (is_null($rec['MESH_ID'])) $rec['MESH_ID'] =''; 
               if (is_null($rec['IR_FLAG'])) $rec['IR_FLAG'] = 0;
               if (is_null($rec['UUID'])) $rec['UUID'] = '';
            

               if ($rec['UUID'] != $device['uuid'] or $rec['IR_FLAG'] != $ir_flag or $rec['MAC'] != $device['mac'] or $rec['LOCAL_KEY']!=$device['localKey'] or $rec['PRODUCT_ID']!=$device['productId'] or $rec['GID_ID']!=$gid or $rec['MESH_ID']!=$device['meshId']) {
                 $rec['LOCAL_KEY'] = $device['localKey'];
                 $rec['PRODUCT_ID'] = $device['productId'];
                 $rec['GID_ID'] = $gid;
                 $rec['MESH_ID'] = $device['meshId'];
                 $rec['MAC'] = $device['mac'];
                 $rec['IR_FLAG'] = $ir_flag;
                 $rec['UUID'] = $device['uuid'];  
                 
                 $rec['ID'] = SQLUpdate('tudevices',$rec);
               }
            }

            $data='';
            if (substr($device['categoryCode'],0,3)=='wf_') {
               if ($device['moduleMap']['wifi']['isOnline'] ) {
                  $this->processCommand($rec['ID'], 'online', 1);
               } else {
                  $this->processCommand($rec['ID'], 'online', 0);
               }
            } else if (substr($device['categoryCode'],0,4)=='zig_') {
               if ($device['moduleMap']['zigbee']['isOnline'] ) {
                  $this->processCommand($rec['ID'], 'online', 1);
               } else {
                  $this->processCommand($rec['ID'], 'online', 0);
               }
            }       
			
            if ($rec['STATUS']==0) {
				foreach($device['dps'] as $key => $value) {
					$cmd_rec = SQLSelectOne("SELECT * FROM tucommands WHERE DEVICE_ID=".(int)$rec['ID']." AND TITLE LIKE '".DBSafe($key)."'");
            
					if (!$cmd_rec['ID']) {
					  $cmd_rec = array();
					  $cmd_rec['TITLE'] = $key;
					  $cmd_rec['VALUE_MIN'] = $sc[$device['productId']][$key]['min'];
					  $cmd_rec['MODE'] = $sc[$device['productId']][$key]['mode'];
					  $cmd_rec['ALIAS'] = $sc[$device['productId']][$key]['code'];
					  $cmd_rec['VALUE_UNIT'] = $sc[$device['productId']][$key]['unit'];
					  $cmd_rec['VALUE_TYPE'] = $sc[$device['productId']][$key]['type'];

					  $cmd_rec['VALUE_MAX'] = $sc[$device['productId']][$key]['max'];
					  $cmd_rec['VALUE_SCALE'] = $sc[$device['productId']][$key]['scale'];
                 if ($cmd_rec['VALUE_SCALE'] == '') $cmd_rec['VALUE_SCALE']=0;
					  $cmd_rec['DIVIDEDBY2'] = 0;
					  $cmd_rec['DIVIDEDBY10'] = 0;
					  $cmd_rec['DIVIDEDBY100'] = 0;

					  $cmd_rec['DEVICE_ID'] = $rec['ID'];
					  $cmd_rec['ID'] = SQLInsert('tucommands', $cmd_rec);
					} else {
					  $cmd_rec['VALUE_MIN'] = $sc[$device['productId']][$key]['min'];
					  $cmd_rec['MODE'] = $sc[$device['productId']][$key]['mode'];
					  $cmd_rec['ALIAS'] = $sc[$device['productId']][$key]['code'];
					  $cmd_rec['VALUE_UNIT'] = $sc[$device['productId']][$key]['unit'];
					  $cmd_rec['VALUE_MAX'] = $sc[$device['productId']][$key]['max'];
                 if ($cmd_rec['DIVIDEDBY2'] == 0) {
					   $cmd_rec['VALUE_SCALE'] = $sc[$device['productId']][$key]['scale'];
                 } 
                 if ($cmd_rec['VALUE_SCALE'] == '') $cmd_rec['VALUE_SCALE']=0;
					  $cmd_rec['VALUE_TYPE'] = $sc[$device['productId']][$key]['type'];

					  $cmd_rec['ID'] = SQLUpdate('tucommands', $cmd_rec);
					}
               
               if (isset($sc[$device['productId']][$key]['range']) and $sc[$device['productId']][$key]['range']) { 
                  foreach ($sc[$device['productId']][$key]['range'] as  $range_key => $range_value) {	   
                     $rng_rec = SQLSelectOne("SELECT * FROM  turange WHERE COMMAND_ID=".(int)$cmd_rec['ID']." AND RANGE_VALUE='" . $range_key . "'");
                     if (!$rng_rec['ID']) {
                        $rng_rec = array();
                        $rng_rec['COMMAND_ID']=$cmd_rec['ID'];
                        $rng_rec['RANGE_VALUE']=$range_key;
                        $rng_rec['RANGE_DESCRIPTION']=$range_value;
               
                        $rng_rec['ID'] = SQLInsert('turange', $rng_rec);
                     }
                  }
               }
					if (is_bool($value)) {
					   $value=(($value) ? 1:0);
					   $data.=$key.':'.(($value) ? 1:0).' ';
					} else if ($value=='true') {
					   $value=1;
					   $data.=$key.':'.$value.' ';
					} else if ($value=='false') {

					   $value=0;
					   $data.=$key.':'.$value.' ';
					} else {
					   $data.=$key.':'.$value.' ';
					}
					$this->processCommand($rec['ID'], $key, $value);
				}
       
			}

        }
	  }
   } 
   
   function Tuya_Web_DP($device_id, $value, $dps_name, $gid, $gw_id) {
      if (is_null($gw_id) or $gw_id=='') {
         $gw_id=$device_id;
      } 
      
 
      $rec=SQLSelectOne("select VALUE_TYPE from tucommands tc inner join tudevices td ON tc.DEVICE_ID=td.ID where tc.TITLE='" . $dps_name . "' and td.DEV_ID='" . $device_id ."'");
      
      if ($rec['VALUE_TYPE']=='bool') {
         $value=(($value==1)?'true':'false'); 
      } else if ($rec['VALUE_TYPE']=='value') {
         $value=(int)$value;
      } else if ($rec['VALUE_TYPE']=='string' or $rec['VALUE_TYPE']=='enum' or !is_numeric($value)) { 
         $value="'$value'";
      } 


      $dps='{'.$dps_name.':'.$value.'}';

      $apiResult = $this->TuyaWebRequest(['action'=> 'tuya.m.device.dp.publish',
                                         'gid'=>$gid,
                                         'data'=> ['devId'=> $device_id,
                                                 'gwId'=> $gw_id,
                                                 'dps'=> $dps ],
                                          'requiresSID'=> 1]);
                                          
      $result=json_decode($apiResult , true);
      if (!$result['success']) {
         debmes('Ошибка изменения статуса:' . $result['errorCode']);
      
      }   
      
      return $apiResult;
   } 
   
   function TuyaRemoteMsg($dev_id,$value,$mode){
      $token=$this->RefreshToken();
      $sURL = 'https://px1.tuyaeu.com/homeassistant/skill';

         $header = [
               'name'           => $mode,
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
   
   function RGB_to_Tuya ($RGB, $color_v2 = false) {                                 
      $R=hexdec(substr($RGB,0,2));
      $G=hexdec(substr($RGB,2,2));
      $B=hexdec(substr($RGB,4,2));

      $HSL = array();

      $var_R = ($R / 255);
      $var_G = ($G / 255);
      $var_B = ($B / 255);

      $var_Min = min($var_R, $var_G, $var_B);
      $var_Max = max($var_R, $var_G, $var_B);
      $del_Max = $var_Max - $var_Min;

      $V = $var_Max;

      if ($del_Max == 0) {
         $H = 0;
         $S = 0;
      } else {
         $S = $del_Max / $var_Max;

         $del_R = ( ( ( $var_Max - $var_R ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
         $del_G = ( ( ( $var_Max - $var_G ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
         $del_B = ( ( ( $var_Max - $var_B ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;

         if      ($var_R == $var_Max) $H = $del_B - $del_G;
         else if ($var_G == $var_Max) $H = ( 1 / 3 ) + $del_R - $del_B;
         else if ($var_B == $var_Max) $H = ( 2 / 3 ) + $del_G - $del_R;

         if ($H<0) $H++;
         if ($H>1) $H--;
      }


      if (!$color_v2) {
         $HSL['H'] = dechex((int)($H*360));
         $HSL['S'] = dechex((int)($S*255));
         $HSL['V'] = dechex((int)($V*255));

         $Tuya_Color=$RGB.'00';
         if (strlen($HSL['H'])==1) {
          $Tuya_Color .= '0'. $HSL['H'];
         } else {
          $Tuya_Color .= $HSL['H'];    
         }

         if (strlen($HSL['S'])==1) {
          $Tuya_Color .= '0'. $HSL['S'];
         } else {
          $Tuya_Color .= $HSL['S'];    
         }

         if (strlen($HSL['V'])==1) {
          $Tuya_Color .= '0'. $HSL['V'];
         } else {
          $Tuya_Color .= $HSL['V'];    
         }

         return $Tuya_Color;
      } else {
         $HSL['H'] = dechex((int)($H*360));
         $HSL['S'] = dechex((int)($S*1000));
         $HSL['V'] = dechex((int)($V*1000));

         return str_pad($HSL['H'],4, '0', STR_PAD_LEFT) . str_pad($HSL['S'],4, '0', STR_PAD_LEFT) . str_pad($HSL['V'],4, '0', STR_PAD_LEFT);          
         
      }   
   }
   
   function Tuya_to_RGB($tuya_color) { 
      if (strlen($tuya_color) > 12) {
         return substr($tuya_color,0,6); 
      } else {
         $hue=hexdec(substr($tuya_color,0,4));
         $sat=hexdec(substr($tuya_color,4,4));
         $val=hexdec(substr($tuya_color,8,4));

         $sat = $sat/10;
         $val = $val/10;  
         
         $rgb = array(0,0,0);
         //calc rgb for 100% SV, go +1 for BR-range
         for($i=0;$i<4;$i++) {
            if (abs($hue - $i*120)<120) {
              $distance = max(60,abs($hue - $i*120));
              $rgb[$i % 3] = 1 - (($distance-60) / 60);
         }
         }
         //desaturate by increasing lower levels
         $max = max($rgb);
         $factor = 255 * ($val/100);
         for($i=0;$i<3;$i++) {
            //use distance between 0 and max (1) and multiply with value
            $rgb[$i] = round(($rgb[$i] + ($max - $rgb[$i]) * (1 - $sat/100)) * $factor);
         }
         $rgb = sprintf('%02X%02X%02X', $rgb[0], $rgb[1], $rgb[2]);                
         
         return $rgb;
      }   
   }    
   
   function Tuya_IOT_Login() {
      $this->getConfig();
      
      $this->config['TUYA_ACCESS_TOKEN'] = '';
      $this->saveConfig();
      $this->getConfig();
      
      $result = $this->Tuya_IOT_GET('/v1.0/token?grant_type=1', True);

      if (!$result->success) {
          debmes("Can't login to IOT cloud.".$result->msg);
          return;
      }
      
      $access_token = $result->result->access_token;
      $this->config['TUYA_ACCESS_TOKEN'] = $access_token;
      $this->config['TUYA_REFRESH_TOKEN'] = $result->result->refresh_token;
      $this->config['TUYA_TOKEN_EXPIRE_TIME'] = $result->result->expire_time + time();
      $this->config['TUYA_IOT_UID'] = $result->result->uid;

      $this->saveConfig();
      
      return;
      
      $password = hash('sha256', $this->config['TUYA_PASSWD']);
      $username = $this->config['TUYA_USERNAME'];

      $data = 	    array(
                'username' => $username,
                'password' => $password
             );

      $url = '/v1.0/iot-03/users/login';
      $url = '/v1.0/token?grant_type=1';
      $token = $this->Tuya_IOT_POST($url, $data, true);
      $access_token = $token->result->access_token;
      $this->config['TUYA_ACCESS_TOKEN'] = $access_token;
      $this->config['TUYA_REFRESH_TOKEN'] = $token->result->refresh_token;
      $this->config['TUYA_TOKEN_EXPIRE_TIME'] = $token->result->expire_time + time();
      $this->config['TUYA_IOT_UID'] = $token->result->uid;

      $this->saveConfig();
      return $token;   
   }
   
   function Tuya_IOT_Refresh() {
      $this->getConfig();
      $this->config['TUYA_ACCESS_TOKEN'] = '';
      $this->saveConfig();
      $refresh_token = $this->config['TUYA_REFRESH_TOKEN'];
      $url = '/v1.0/iot-03/users/token/'.$refresh_token;
      $url = '/v1.0/token/'.$refresh_token;
      
      $token =  $this->Tuya_IOT_GET($url, '', true);
      
      if (!$token->success) {
         debmes("Can't refresh token for IOT ".$token->msg);
         $token = $this->Tuya_IOT_Login();
      } else {   
         $this->config['TUYA_ACCESS_TOKEN'] = $token->result->access_token;
         $this->config['TUYA_REFRESH_TOKEN'] = $token->result->refresh_token;
         $this->config['TUYA_TOKEN_EXPIRE_TIME'] = $token->result->expire_time + time();
         $this->saveConfig();
      }   
      return $token;
   }   
   
   function Tuya_IOT_POST($url, $data, $token_managment=false){
      $base = 'https://openapi.tuyaeu.com';
      $this->getConfig();
      if (!$token_managment and (time()>($this->config['TUYA_TOKEN_EXPIRE_TIME']-60))) {
         $result = $this->Tuya_IOT_Refresh();
         $this->getConfig();
      }   
      $client_id = $this->config['TUYA_CLIENT_ID'];
      $secret = $this->config['TUYA_CLIENT_SECRET'];
      $access_token = $this->config['TUYA_ACCESS_TOKEN'];

      $data = json_encode($data);
      $sha256 = hash('sha256', $data);
      $stringToSign = 'POST'."\n".$sha256."\n"."\n".$url;

      $t = round(microtime(true)*1000,0);

      $sign = $client_id;
      if (!$token_managment) {
       $sign .= $access_token;
      }
      $sign .= $t.$stringToSign;
      $sign = hash_hmac('sha256',$sign,$secret);
      $sign = strtoupper($sign);

      $headers = ['client_id: ' . $client_id,
               'access_token: '.$access_token,
                 'sign: ' . $sign,
                 't: '.  $t,
                 'sign_method: HMAC-SHA256',
                "Content-Type: application/json"
              ];

      $aHTTP = array(
                     'http' => 
                              array(
                                   'method'  => 'POST', 
                                   'header'  => $headers,
                                   'content' => $data
                                   )
                     );


      $context = stream_context_create($aHTTP);
      $contents = file_get_contents($base.$url, false, $context);
      $result=json_decode($contents);
      return $result;

   }

   function Tuya_IOT_GET($url, $token_managment=false) {
      $base = 'https://openapi.tuyaeu.com';
      $this->getConfig();
  
      $client_id = $this->config['TUYA_CLIENT_ID'];
      $secret = $this->config['TUYA_CLIENT_SECRET'];
      $access_token = $this->config['TUYA_ACCESS_TOKEN'];

      $data = '';
      $sha256 = hash('sha256', $data);
      $stringToSign = 'GET'."\n".$sha256."\n"."\n".$url;

      $t = round(microtime(true)*1000,0);

      $sign = $client_id;
      if (!$token_managment) {
       $sign .= $access_token;
      }
      $sign .= $t.$stringToSign;
      $sign = hash_hmac('sha256',$sign,$secret);
      $sign = strtoupper($sign);

      $headers = ['client_id: ' . $client_id,
                  'access_token: '.$access_token,
                  'sign: ' . $sign,
                  't: '.  $t,
                  'sign_method: HMAC-SHA256',
                "Content-Type: application/json"
              ];

      $aHTTP = array(
                     'http' => 
                              array(
                                   'method'  => 'GET', 
                                   'header'  => $headers,
                                   'content' => $data
                                   )
                     );


      $context = stream_context_create($aHTTP);
      $contents = file_get_contents($base.$url, false, $context);
      $result=json_decode($contents);
      return $result;



   }
   

   function processCommand($device_id, $command, $value, $params = 0, $checkOld = true) {

      $cmd_rec = SQLSelectOne("SELECT * FROM tucommands WHERE DEVICE_ID=".(int)$device_id." AND TITLE LIKE '".DBSafe($command)."'");
         
      if (!$cmd_rec['ID']) {
        $device = SQLSelectOne("SELECT * FROM tudevices WHERE ID=".(int)$device_id);
        $cmd_rec = array();
        $cmd_rec['TITLE'] = $command;
        $cmd_rec['DEVICE_ID'] = $device_id;
             if ($device['TYPE']=='switch') {
               if ($command=='4') {
                 $cmd_rec['ALIAS']='mA';
               } elseif($command=='5'){
                 $cmd_rec['ALIAS']='W';
                 $cmd_rec['DIVIDEDBY10']=1;
               } elseif($command=='6'){
                 $cmd_rec['ALIAS']='V';
                 $cmd_rec['DIVIDEDBY10']=1;
               } elseif ($command=='18') {
                 $cmd_rec['ALIAS']='mA';
               } elseif($command=='19'){
                 $cmd_rec['ALIAS']='W';
                 $cmd_rec['DIVIDEDBY10']=1;
               } elseif($command=='20'){
                 $cmd_rec['ALIAS']='V';
                 $cmd_rec['DIVIDEDBY10']=1;
               } 
             } elseif ($device['TYPE']=='climate') {
               if ($command=='current_temperature') {
                 $cmd_rec['DIVIDEDBY2']=1;
               } elseif($command=='3'){
                 $cmd_rec['ALIAS']='current_temperature';
                 $cmd_rec['DIVIDEDBY2']=1;
               } elseif($command=='2'){
                 $cmd_rec['ALIAS']='temperature';
                 $cmd_rec['DIVIDEDBY2']=1;
               } elseif ($command=='102') {
                 $cmd_rec['DIVIDEDBY2']=1;
               } 
             }

        $cmd_rec['ID'] = SQLInsert('tucommands', $cmd_rec);
      }
      
      if  ($cmd_rec['VALUE_SCALE']==NULL || $cmd_rec['VALUE_SCALE']==0) {    
         if ($cmd_rec['DIVIDEDBY10']) $value=$value/10;
         if ($cmd_rec['DIVIDEDBY2']) $value=$value/2;
         if ($cmd_rec['DIVIDEDBY100']) $value=$value/100;
      } else {
         $value = $value / (10** $cmd_rec['VALUE_SCALE']);
      } 
      
      if ($cmd_rec['COLOR_CONVERT']) {
         $value = $this->Tuya_to_RGB($value);
      }        
      
      if (gettype($value) == 'string' and strlen($value) > 255) {
         $value = substr($value, 0, 255);
      }

      if (is_null($value)) $value='';

      $old_rec = SQLSelectOne('SELECT * FROM tuvalues WHERE ID='.$cmd_rec['ID'].';');
      //$old_value = $cmd_rec['VALUE'];
      if ($old_rec) {
         $old_value = $old_rec['VALUE'];
         if (is_null($old_value)) $old_value='';

         $old_rec['VALUE'] = $value;
         $old_rec['UPDATED'] = date('Y-m-d H:i:s');         

         SQLUpdate('tuvalues', $old_rec);
         
      } else {
         $old_rec = array();
         $old_rec['ID'] = $cmd_rec['ID'];
         $old_rec['VALUE'] = $value;
         $old_rec['UPDATED'] = date('Y-m-d H:i:s');   
         
         SQLInsert('tuvalues', $old_rec);         
      }   


      //$cmd_rec['VALUE'] = $value;
      //$cmd_rec['UPDATED'] = date('Y-m-d H:i:s');
      //SQLUpdate('tucommands', $cmd_rec);

      if ($checkOld and $old_value == $value) return;
         
      if ($command=='state' or $command=='switch_1' or $command=='power' or $command=='Power' or $command=='switch_on') processSubscriptions('TUSTATUS', array('FIELD' => 'STATE','VALUE' => $value,'ID' =>$device_id));
      if ($command=='online') processSubscriptions('TUSTATUS', array('FIELD' => 'ONLINE','VALUE' => $value,'ID' =>$device_id));

      if ($cmd_rec['LINKED_OBJECT'] && $cmd_rec['LINKED_PROPERTY']) {
         if  ($cmd_rec['COLOR_CONVERT']==1) {   
            $value = substr($value,0,6);
         }
         
         if ($cmd_rec['REPLACE_LIST'] != '') {
            $list = explode(',', $cmd_rec['REPLACE_LIST']);
            foreach ($list as $pair) {
                $pair = trim($pair);
                list($new, $old) = explode('=', $pair);
                if ($value == $new) {
                    $value = $old;
                    break;
                }
            }
         }  
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

    $properties = SQLSelect("SELECT tucommands.*, tudevices.VER_3_1, tudevices.DEV_ID,tudevices.CONTROL,tudevices.STATUS, tudevices.LOCAL_KEY,tudevices.DEV_IP,tudevices.TYPE,tudevices.MESH_ID,tudevices.GID_ID,tudevices.MAC FROM tucommands LEFT JOIN tudevices ON tudevices.ID=tucommands.DEVICE_ID WHERE tucommands.LINKED_OBJECT LIKE '".DBSafe($object)."' AND tucommands.LINKED_PROPERTY LIKE '".DBSafe($property)."'");

    if ($properties) {
       
      if ($properties[0]['REPLACE_LIST'] != '') {
         $list = explode(',', $properties[0]['REPLACE_LIST']);
         foreach ($list as $pair) {
             $pair = trim($pair);
             list($new, $old) = explode('=', $pair);
             if ($value == $old) {
                 $value = $new;
                 break;
             }
         }
     }  
     $dps_name = $properties[0]['TITLE'];
     
     if ($properties[0]['COLOR_CONVERT']) {
      $value = $this->RGB_to_Tuya($value, $properties[0]['COLOR_V2']);
      //debmes('New color value:' . $value);
     }   

     if ($properties[0]['DIVIDEDBY2']) {
      $value = $value*2;
     }   

     if ($properties[0]['DIVIDEDBY10']) {
      $value = $value*10;
     }   

     if ($properties[0]['DIVIDEDBY100']) {
      $value = $value*100;
     } 
     
     if ($properties[0]['VALUE_SCALE'] >0) {
      $value = $value * (10** $properties[0]['VALUE_SCALE']);
     }    

     if (((strlen($properties[0]['LOCAL_KEY'])==0 or strlen($properties[0]['DEV_IP'])==0) and (strlen($properties[0]['MAC'])==0 or strlen($properties[0]['MESH_ID'])==0)) or $properties[0]['CONTROL']==0) {

      if ($dps_name=='state') {
         if ($properties[0]['CONTROL']==0) {
            $this->Tuya_Web_DP($properties[0]['DEV_ID'],$value,'1',$properties[0]['GID_ID'],$properties[0]['MESH_ID']);
         } else {    
            $this->TuyaRemoteMsg($properties[0]['DEV_ID'],$value,'turnOnOff');
         }
      } else  if ($dps_name=='brightness') {
         $this->TuyaRemoteMsg($properties[0]['DEV_ID'],$value,'brightnessSet');
      } else  if ($dps_name=='color_temp') {
         $this->TuyaRemoteMsg($properties[0]['DEV_ID'],$value,'colorTemperatureSet');
      } else  if ($dps_name=='color_mode') {
         $this->TuyaRemoteMsg($properties[0]['DEV_ID'],$value,'colorModeSet');
      } else  if ($dps_name=='temperature') {
         $this->TuyaRemoteMsg($properties[0]['DEV_ID'],$value,'temperatureSet');
      } else if ($properties[0]['CONTROL']==0) {
         $this->Tuya_Web_DP($properties[0]['DEV_ID'],$value,$dps_name,$properties[0]['GID_ID'],$properties[0]['MESH_ID']);
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

      if ($properties[0]['VALUE_TYPE']=='bool' or $properties[0]['TITLE']=='state') {
         $dps='{"'.$dps_name.'":'.(($value==1)?'true':'false').'}';
      } else if ($properties[0]['VALUE_TYPE']=='value') {
       $dps='{"'.$dps_name.'":'.$value.'}';
      } else {    
       $dps='{"'.$dps_name.'":"'.$value.'"}';
      }
      
      if (strlen($properties[0]['MESH_ID'])==0) {
         //debmes('Tuya: dps=' .$dps);
         $this->TuyaLocalMsg('SET',$dev_id,$properties[0]['LOCAL_KEY'],$properties[0]['DEV_IP'],$dps,'', $properties[0]['VER_3_1']);
      } else {
         $gw=SQLSelectOne("SELECT * FROM tudevices WHERE DEV_ID='" .$properties[0]['MESH_ID']."'");
         $this->TuyaLocalMsg('SET',$dev_id,$gw['LOCAL_KEY'],$gw['DEV_IP'],$dps,$properties[0]['MAC']);
      }
     }
     $rec=SQLSelectOne("select * from tucommands where ID=".$properties[0]['ID']);
     $rec = SQLSelectOne("SELECT * FROM tuvalues WHERE ID=".$rec['ID'].';');
     $rec['value']=$value;
     SQLUpdate('tucommands',$rec);
    
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
      $rec = SQLSelectOne("SHOW TABLES LIKE 'tudevices';" );
      if ($rec) {
         $table='tudevices';
         $fields = SQLSelect("SHOW FIELDS FROM `$table`;");
         $fields = array_column($fields, 'Field');
         if (!in_array('CONTROL', $fields)) {
            SQLExec("ALTER TABLE tudevices ADD CONTROL int(10) unsigned NOT NULL DEFAULT 0;");
            SQLExec("ALTER TABLE tudevices ADD STATUS int(10) unsigned NOT NULL DEFAULT 0;");
            SQLExec("UPDATE tudevices SET CONTROL=1, STATUS=1 WHERE ONLY_LOCAL=1");
         }   
         
      } 

      $rec = SQLSelectOne("SHOW TABLES LIKE 'tuvalues';");
      if (!$rec) {
         $sql = "CREATE TABLE tuvalues (ID INT, VALUE VARCHAR(255), UPDATED datetime, INDEX USING HASH (ID)) ENGINE = MEMORY;";
         SQLExec($sql);
      }
      
      $rec = SQLSelectOne("SHOW TABLES LIKE 'tuircommand';" );
      if ($rec) {
         $rec = SQLSelect("SHOW COLUMNS FROM tuircommand;" );

         foreach($rec as $field) {
            if ($field['Field'] == 'CPULSE_ALT') {
               if ($field['Type'] !=  "varchar(800)") {
                  SQLExec("ALTER TABLE tuircommand MODIFY CPULSE_ALT varchar(800) NOT NULL DEFAULT '';");
               }
            }
         }  
      }
      parent::install();
      
      setGlobal('cycle_tuyaControl', 'restart');
      setGlobal('cycle_local_tuyaControl', 'restart');
      setGlobal('cycle_tuya_iotControl', 'restart');

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
      SQLExec('DROP TABLE IF EXISTS turange');
      SQLExec('DROP TABLE IF EXISTS tuircommand');

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
 tudevices: BUSY boolean NOT NULL DEFAULT 0
 tudevices: UPDATED datetime
 tudevices: REMOTE_CONTROL boolean NOT NULL DEFAULT 0
 tudevices: ONLY_LOCAL boolean NOT NULL DEFAULT 0
 tudevices: PRODUCT_ID varchar(30) DEFAULT ''
 tudevices: GID_ID varchar(30) DEFAULT '' 
 tudevices: REMOTE_CONTROL_2 boolean NOT NULL DEFAULT 0
 tudevices: MESH_ID varchar(30) DEFAULT ''
 tudevices: MAC varchar(30) DEFAULT ''
 tudevices: SEND12 boolean NOT NULL DEFAULT 0
 tudevices: FLAGS12 varchar(30) DEFAULT ''
 tudevices: VER_3_1 boolean NOT NULL DEFAULT 0
 tudevices: IR_FLAG boolean NOT NULL DEFAULT 0
 tudevices: CONTROL int(10) unsigned NOT NULL DEFAULT 0
 tudevices: STATUS int(10) unsigned NOT NULL DEFAULT 0
 tudevices: UUID varchar(30) NOT NULL DEFAULT ''
 
 
 tucommands: ID int(10) unsigned NOT NULL auto_increment
 tucommands: TITLE varchar(100) NOT NULL DEFAULT ''
 tucommands: VALUE varchar(255) NOT NULL DEFAULT ''
 tucommands: ALIAS varchar(255) NOT NULL DEFAULT ''
 tucommands: SDEVICE_TYPE varchar(255) NOT NULL DEFAULT ''
 tucommands: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 tucommands: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 tucommands: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 tucommands: LINKED_METHOD varchar(100) NOT NULL DEFAULT ''
 tucommands: DIVIDEDBY10 boolean NOT NULL DEFAULT 0
 tucommands: DIVIDEDBY2 boolean NOT NULL DEFAULT 0
 tucommands: DIVIDEDBY100 boolean DEFAULT 0
 tucommands: MODE varchar(10) DEFAULT ''
 tucommands: VALUE_TYPE varchar(10) DEFAULT ''
 tucommands: VALUE_MIN varchar(10) DEFAULT '0'
 tucommands: VALUE_MAX varchar(10) DEFAULT '0'
 tucommands: VALUE_SCALE int(10) DEFAULT 0
 tucommands: VALUE_UNIT varchar(10) DEFAULT ''
 tucommands: COLOR_CONVERT boolean DEFAULT 0
 tucommands: REPLACE_LIST varchar(255) DEFAULT ''
 tucommands: COLOR_V2 boolean DEFAULT 0
 tucommands: UPDATED datetime


 turange: ID int(10) unsigned NOT NULL auto_increment
 turange: COMMAND_ID int(10) unsigned NOT NULL 
 turange: RANGE_VALUE varchar(10) NOT NULL DEFAULT ''
 turange: RANGE_DESCRIPTION varchar(50) NOT NULL DEFAULT ''

 tuircommand: ID int(10) unsigned NOT NULL auto_increment
 tuircommand: DEVICE_ID int(10) unsigned NOT NULL 
 tuircommand: TITLE varchar(20) NOT NULL DEFAULT ''
 tuircommand: COMPRESSPULSE varchar(150) NOT NULL DEFAULT ''
 tuircommand: EXTS varchar(150) NOT NULL DEFAULT ''
 tuircommand: CPULSE_ALT varchar(800) NOT NULL DEFAULT ''
 tuircommand: CPULSE_ALT_FLAG boolean DEFAULT 0
 tuircommand: RF_FLAG boolean DEFAULT 0

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

