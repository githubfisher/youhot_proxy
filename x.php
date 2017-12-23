<?php
function logger($msg, $level = 'Info', $path = '')
{
     if (empty($path)) {
         $logs = dirname(__FILE__).'/logs/run_log';
     } else {
         $logs = $path;
     }
     $maxSize = 100000;
     if (file_exists($logs) && (abs(filesize($logs)) >= $maxSize)) {
         file_put_contents($logs, 'Max Size:'.$maxSize.' log cleaned'."\n");
     }
     file_put_contents($logs, date('Y-m-d H:i:s').' '.$level.': '.$msg, FILE_APPEND);
}

require_once(dirname(__FILE__).'/../common/db.php');

do {
    sleep(15);
    $url = 'http://api.ip.data5u.com/dynamic/get.html?order=782ba526238a11f26f3f8b12bfb75094';
    $scc = stream_context_create(array(
        'http' => array(
            'method' => 'GET'
        )
    ));
    $json = file_get_contents($url, false, $scc);
    $data = explode(':', $json);
    if (is_array($data)) {
	$ip = $data[0];
	$port = $data[1];
	$db = db::getIntance();
	$id = $db->getOne("SELECT id FROM proxy WHERE `ip`='$ip' AND `port` = '$port'");
	if($id > 0){
	    $db->update('proxy', array('status' => 1,'times' => 0), "id=$id");
	}
	$proxy = array(
	    'ip' => $ip,
	    'port' => $port,
	    'times' => 0,
	    'get_at' => time(),
	    'status' => 1
	);
	$db->insert("proxy", $proxy);
        logger('get proxy: '.$ip.":".$port."\n"); //info
    } else {
	continue;
    }
} while (true);
