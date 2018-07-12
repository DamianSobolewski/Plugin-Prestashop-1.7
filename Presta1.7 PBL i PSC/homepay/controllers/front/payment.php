<?php


class HomepayPaymentModuleFrontController extends ModuleFrontController {

	public $page_name = 'module-payment-submit';
	private $homepay;
	
	public function __construct() {
		parent::__construct();
		$this->page_name = 'module-payment-submit';
		$this->display_column_left = false;
	}
	
	public function postProcess() {
		$this->homepay = new Homepay();
	}

	/**
	 * @return array
	 * @see ModuleFrontControllerCore::initContent()
	 */
	public function initContent() {
		
		parent::initContent();
		
		$this->showPayMethod();
		
		$currency = $this->module->getCurrency((int)$this->context->cart->id_currency);

		$total = number_format($this->context->cart->getOrderTotal(true), 2, '.', '');

		$this->context->smarty->assign(array(
				'backLink' => $this->context->link->getPageLink('order', null, null, array('step' => 3), true),
				'total' => $this->totalPrice(),
				'currency' => $currency,
				'image' => $this->homepay->getHomepayLogo(),
				'this_path' => $this->module->getPathUri()));
		
		$this->context->smarty->assign(array(
				'data'=> $data = $this->payByLink(),
				'dataPSC'=> $dataPSC = $this->payByPSC()
		));
	}

	/**
	 * Pay by link transfer
	 * @return string[]
	 */
	public function payByLink() {
		
		return $this->getData(
				Configuration::get('HOMEPAY_USER'),
				Configuration::get('HOMEPAY_PUBLIC'),
				$this->totalPrice() * 100,
				0,
				'',
				$this->context->cart->id,
				urlencode(''. $this->context->shop->getBaseUrl().''),
				urlencode(''. $this->context->shop->getBaseUrl().''),
				urlencode(''. $this->context->shop->getBaseUrl().'modules/homepay/validation.php'),
				Configuration::get('HOMEPAY_PRIVATE')
				);
	}
	
	/**
	 * Pay by "Paysafecard"
	 * @return string[]
	 */
	public function payByPSC() {
		
		return $this->getData(
				Configuration::get('HOMEPAY_USER'),
				Configuration::get('HOMEPAY_PUBLIC_PSC'),
				$this->totalPrice() * 100,
				'',
				'',
				$this->context->cart->id,
				urlencode(''. $this->context->shop->getBaseUrl().''),
				urlencode(''. $this->context->shop->getBaseUrl().''),
				urlencode(''. $this->context->shop->getBaseUrl().'modules/homepay/validationPSC.php'),
				Configuration::get('HOMEPAY_PRIVATE_PSC')
				);
	}
	
	/**
	 * Get data for payByLink and payByPSC
	 * @param $uid
	 * @param $publicKey
	 * @param $amount
	 * @param $mode
	 * @param $label
	 * @param $control
	 * @param $sucessUrl
	 * @param $failureUrl
	 * @param $notifyUrl
	 * @param $crc
	 * @return string[]
	 */
	private function getData($uid, $publicKey, $amount, $mode, $label, $control, $sucessUrl, $failureUrl, $notifyUrl, $crc){
		$data = [
				'uid' => $uid,
				'public_key' => $publicKey,
				'amount' => $amount,
				'mode'=> $mode,
				'label' => $label,
				'control' => $control,
				'success_url' => $sucessUrl,
				'failure_url' => $failureUrl,
				'notify_url' => $notifyUrl
		];
		
		$data['crc'] = md5(join('', $data) . $crc);
		
		return $data;
	}
	
	/**
	 * @access private
	 */
	private function showPayMethod() {
	
	$this->setTemplate($this->homepay->buildTemplatePath('payment', 'front'));
	}
	
	/**
	 * Total price
	 * @return string
	 */
	protected function totalPrice() {
		
		return (number_format($this->context->cart->getOrderTotal(true), 2, '.', ''));
	}

}