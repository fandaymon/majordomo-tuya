<?php
/**
* Tuya Cycle
*/

chdir(dirname(__FILE__) . '/../');

include_once('./config.php');
include_once('./lib/loader.php');
include_once('./lib/threads.php');

set_time_limit(0);

$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);

include_once('./load_settings.php');
include_once(DIR_MODULES . 'control_modules/control_modules.class.php');

$ctl = new control_modules();

include_once(DIR_MODULES . 'tuya/tuya.class.php');

$tuya_module = new tuya();
$tuya_module->getConfig();

echo date('H:i:s') . ' Running ' . basename(__FILE__) . PHP_EOL;

$latest_check = 0;
$latest_disc = 0;

$cycle_debug = false;

$tuya_interval=30;


if ($tuya_module->config['TUYA_INTERVAL']) $tuya_interval=$tuya_module->config['TUYA_INTERVAL'];

 
echo date('H:i:s') . ' Init Tuya ' . PHP_EOL;
echo date('H:i:s') . " Discover period - $tuya_interval seconds" . PHP_EOL;



while (1) {

	if ((time()-$latest_check) >= $tuya_interval) {
		$latest_check = time();
		setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
		$tuya_module->requestLocalStatus();
                if ($tuya_module->config['TUYA_REFRESH_TOKEN']!=NULL) {
                  $token=$tuya_module->RefreshToken();
                  $tuya_module->Tuya_Discovery_Devices($token);
                 } else {
		  $tuya_username = $tuya_module->config['TUYA_USERNAME'];
                  $tuya_passwd = $tuya_module->config['TUYA_PASSWD'];
                  $tuya_bztype = $tuya_module->config['TUYA_BZTYPE'];
                  $tuya_ccode = $tuya_module->config['TUYA_CCODE'] ;
        
                  $token=json_decode($tuya_module->getToken($tuya_username,$tuya_passwd,$tuya_bztype,$tuya_ccode));
                  if (isset($token->responseStatus) && $token->responseStatus === 'error') {
                    $message = $token->responseMsg;
                    debmes($message);
                   } else {
                    $tuya_module->config['TUYA_ACCESS_TOKEN']=$token->access_token;
		    $tuya_module->config['TUYA_REFRESH_TOKEN']=$token->refresh_token;
                    $tuya_module->config['TUYA_TIME']=time()+$token->expires_in;
                    $tuya_module->Tuya_Discovery_Devices($token->access_token);      

                    $tuya_module->saveConfig();
		   }
                 }
	}
	if (file_exists('./reboot') || IsSet($_GET['onetime'])) {
		$db->Disconnect();
		echo date('H:i:s') . ' Stopping by command REBOOT or ONETIME' . basename(__FILE__) . PHP_EOL;
		exit;
	}

	sleep(1);
}

echo date('H:i:s') . ' Unexpected close of cycle' . PHP_EOL;

DebMes('Unexpected close of cycle: ' . basename(__FILE__));
