<?php

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/homepay.php');

function check_ip()
{
	if(empty($_SERVER['REMOTE_ADDR'])){
		return false;
	}
	if(ini_get('allow_url_fopen') != 1){
		return gethostbyname("get.homepay.pl") == $_SERVER['REMOTE_ADDR'];
	}
	$handle = fopen('http://get.homepay.pl/index.htm', 'r');
	$data = trim(stream_get_contents($handle));
	fclose($handle);
	return in_array($_SERVER['REMOTE_ADDR'], explode(',', $data));
}

$data_entry = $_POST['json'];

$data = json_decode($_POST['json'], true);


$homepay = new Homepay();


$ret = array();

foreach($data as $entry) {
	if(isset($entry['psc_id']))
	{
		$ret[] = array(
				'psc_id' => $entry['psc_id'],
				'psc_return' => 1
		);
		if(!Order::getOrderByCartId($entry['psc_merchant_data'])) {
			$cart = new Cart((int)$entry['psc_merchant_data']);
			
			if($entry['psc_status'] != '2') {
				$homepay->validateOrder((int)$entry['psc_merchant_data'],
						(int)(Configuration::get('PS_OS_PAYMENT')),
						(double)$entry['psc_amount'],
						$homepay->displayName,
						'ID: ' . $entry['psc_id'] . ' @ ' . $entry['psc_time'],
						array(), NULL, false, $cart->secure_key);
				
			} else {
				$homepay->validateOrder((int)$entry['psc_merchant_data'],
						(int)(Configuration::get('PS_OS_ERROR')),
						(double)$entry['psc_amount'],
						$homepay->displayName, 'ID: ' . $entry['psc_id'] . ' @ ' . $entry['psc_time'],
						array(), NULL, false,
						$cart->secure_key);
			}
			
		}
	}
}

echo json_encode($ret);
?>