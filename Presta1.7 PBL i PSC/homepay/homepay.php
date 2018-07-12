<?php

/**
 * Homepay module
 * 
 * @author Homepay
 * @copyright Copyright (c) 2017 Homepay
 * 
 * http://www.homepay.pl
 */

	if (! defined ( '_PS_VERSION_' )) {
	exit ();
	}
class Homepay extends PaymentModule {
	
	protected $_html = '';
	protected $_postErrors = array ();
	
	public function __construct() {
	    
		$this->name = 'homepay';
		$this->tab = 'payments_gateways';
		$this->version = '1.0.0';
		$this->ps_versions_compliancy = array (
				'min' => '1.7',
				'max' => _PS_VERSION_ 
		);
		$this->author = 'Homepay';
		$this->is_eu_compatible = 1;		
		$this->currencies = true;
		$this->currencies_mode = 'checkbox';
		$this->bootstrap = true;
		
		parent::__construct ();
		
		$this->displayName = $this->l ( 'Homepay' );
		$this->description = $this->l ( 'Accepts payments by Homepay' );
		
		$this->confirm_uninstall = $this->l('Are you sure you want to uninstall? You will lose all your settings!');
	}
	
	/**
	 * @return bool
	 */	
	public function install() {
	    
	    if (! parent::install () 
	    		|| ! $this->registerHook ( 'paymentOptions' ) 
	 //   		|| ! $this->registerHook ( 'displayPaymentEU' ) 
	    		|| ! $this->registerHook ('paymentReturn')) {
			return false;
		}
		return true;
	}	
	
	/**
	 * @return bool
	 */
	public function uninstall() {
		if (! parent::uninstall ()
				|| ! Configuration::deleteByName ( 'HOMEPAY_USER' )
				|| ! Configuration::deleteByName ( 'HOMEPAY_PUBLIC' )
				|| ! Configuration::deleteByName ( 'HOMEPAY_PRIVATE' )
				|| ! Configuration::deleteByName ( 'HOMEPAY_PUBLIC_PSC' )
				|| ! Configuration::deleteByName ( 'HOMEPAY_PRIVATE_PSC' )
				|| ! Configuration::deleteByName ( 'HOMEPAY_PBL' )
				|| ! Configuration::deleteByName ( 'HOMEPAY_PSC' )) {
					return false;
				}
				return true;
	}

	/**
	 * Check that the configuration fields are empty.
	 * @access protected
	 */	
	protected function _postValidation() {
		if (Tools::isSubmit ( 'btnSubmit' )) {
			if (! Tools::getValue ( 'HOMEPAY_USER' ))
				$this->_postErrors [] = $this->l ( 'User id are required.' );
				elseif (! Tools::getValue ( 'HOMEPAY_PUBLIC' ))
				$this->_postErrors [] = $this->l ( 'Public key is required.' );
				elseif (! Tools::getValue ( 'HOMEPAY_PRIVATE' ))
				$this->_postErrors [] = $this->l ( 'Private key is required.' );
				elseif (! Tools::getValue ( 'HOMEPAY_PUBLIC_PSC' ))
				$this->_postErrors [] = $this->l ( 'Public key PSC is required.' );
				elseif (! Tools::getValue ( 'HOMEPAY_PRIVATE_PSC' ))
				$this->_postErrors [] = $this->l ( 'Private key PSC is required.' );
		}
	}

	/**
	 * Saving data from the form and displays a confirmation.
	 * @access protected 
	 * @return string 
	 */	
	protected function _postProcess() {
		if (Tools::isSubmit ( 'btnSubmit' )) {
			Configuration::updateValue ( 'HOMEPAY_USER', Tools::getValue ( 'HOMEPAY_USER' ) );
			Configuration::updateValue ( 'HOMEPAY_PUBLIC', Tools::getValue ( 'HOMEPAY_PUBLIC' ) );
			Configuration::updateValue ( 'HOMEPAY_PRIVATE', Tools::getValue ( 'HOMEPAY_PRIVATE' ) );
			Configuration::updateValue ( 'HOMEPAY_PUBLIC_PSC', Tools::getValue ( 'HOMEPAY_PUBLIC_PSC' ) );
			Configuration::updateValue ( 'HOMEPAY_PRIVATE_PSC', Tools::getValue ( 'HOMEPAY_PRIVATE_PSC' ) );
			Configuration::updateValue ( 'HOMEPAY_PBL', Tools::getValue ( 'HOMEPAY_PBL' ) );
			Configuration::updateValue ( 'HOMEPAY_PSC', Tools::getValue ( 'HOMEPAY_PSC' ) );
		}
		$this->_html .= $this->displayConfirmation ( $this->l ( 'Settings updated' ) );
	}
	
	/**
	 * Displays SMARTY infos.tpl.
	 * @access protected
	 * 
	 * @return string
	 */	
	protected function _displayHomepay() {
	    
	    return $this->display(__FILE__, 'infos.tpl');
	}

	/**
	 * Displays the configuration form and information.
	 * @return string|array
	 */
	public function getContent() {
	 
	    if (Tools::isSubmit('btnSubmit')) {
	       $this->_postValidation();
	       if (!count($this->_postErrors))
	           $this->_postProcess();
	       else
	           foreach ($this->_postErrors as $err)
	               $this->_html .= $this->displayError($err);
	    }
	    
	    else
	        $this->_html .='<br />';
	    
	    $this->_html .= $this->_displayHomepay();
	    $this->_html .= $this->displayForm();
	    
	    return $this->_html;	          
	}

	/**
	 * Hook for payment options.
     * @param array $params
     * 
     * @return array
	 */
	public function hookPaymentOptions($params) {
	    
	    if (!$this->active) {
	        return;
	    }	    
	    if (!$this->checkCurrency ($params ['cart'] )) {
	        return;
	    }	    
	    $paymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
	    $paymentOption->setCallToActionText ($this->l ('Pay with Homepay'))
	    ->setLogo ($this->getHomepayLogo('homepay_u_icon.png'))
	    ->setModuleName ($this->name)
	    ->setAction ($this->context->link->getModuleLink ($this->name, 'payment'));
	    
	    return array (
	        $paymentOption
	    );
	}

	/**
	 * Logo path.
	 * @param string $file
	 * 
	 * @return array|boolean|mixed|string
	 */
	public function getHomepayLogo($file = 'homepay_logo.png') {
	    
		return Media::getMediaPath ( _PS_MODULE_DIR_ . $this->name . '/img/' . $file );
	}
	
	/**
	 * Check currency
	 * @param $cart
	 * 
	 * @return boolean
	 */
	public function checkCurrency($cart) {
		$currency_order = new Currency ( $cart->id_currency );
		$currencies_module = $this->getCurrency ( $cart->id_currency );
		
		if (is_array ( $currencies_module )) {
			foreach ( $currencies_module as $currency_module ) {
				if ($currency_order->id == $currency_module ['id_currency']) {
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Creates a form.
	 * @return string|array
	 */
	public function displayForm() {
		$fields_form ['user_id'] = array (
				'form' => array (
						'legend' => array (
								'title' => $this->l ( 'Homepay id użytkownika' ),
								'icon' => 'icon-th'
						),
						'input' => array (
								array (
										'type' => 'text',
										'label' => $this->l ( 'Id użytkownika' ),
										'name' => 'HOMEPAY_USER',
										'required' => true
								)
						),
						'submit' => array (
								'title' => $this->l ( 'Save' )
								
						)
				)
		);
		
		$fields_form ['transfers'] = array (
				'form' => array (
						'legend' => array (
								'title' => $this->l ( 'Homepay Przelewy' ),
								'icon' => 'icon-th'
						),
						'input' => array (
								array (
										'type' => 'text',
										'label' => $this->l ( 'Klucz publiczny' ),
										'name' => 'HOMEPAY_PUBLIC',
										'required' => true
								),
								array (
										'type' => 'text',
										'label' => $this->l ( 'Klucz prywatny' ),
										'name' => 'HOMEPAY_PRIVATE',
										'required' => true
								),
								array (
										'type' => 'switch',
										'label' => $this->l ( 'Szybkie przelewy' ),
										'name' => 'HOMEPAY_PBL',
										'values' => array (
												array (
														'id' => 'active_on',
														'value' => 1,
														'label' => $this->l ( 'Enabled' )
												),
												array (
														'id' => 'active_off',
														'value' => 0,
														'label' => $this->l ( 'Disabled' )
												)
										)
								)
						),
						'submit' => array (
								'title' => $this->l ( 'Save' )
								
						)
				)
		);
		
		
		$fields_form ['psc'] = array (
				'form' => array (
						'legend' => array (
								'title' => $this->l ( 'Homepay PSC' ),
								'icon' => 'icon-th'
						),
						'input' => array (
								array (
										'type' => 'text',
										'label' => $this->l ( 'Klucz publiczny' ),
										'name' => 'HOMEPAY_PUBLIC_PSC',
										'required' => true
								),
								array (
										'type' => 'text',
										'label' => $this->l ( 'Klucz prywatny' ),
										'name' => 'HOMEPAY_PRIVATE_PSC',
										'required' => true
								),
								array (
										'type' => 'switch',
										'label' => $this->l ( 'Paysafecard' ),
										'name' => 'HOMEPAY_PSC',
										'values' => array (
												array (
														'id' => 'active_on',
														'value' => 1,
														'label' => $this->l ( 'Enabled' )
												),
												array (
														'id' => 'active_off',
														'value' => 0,
														'label' => $this->l ( 'Disabled' )
												)
										)
								)
						),
						'submit' => array (
								'title' => $this->l ( 'Save' )
								
						)
				)
		);
		
		$helper = new HelperForm ();
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite ( 'AdminModules' );
		$helper->show_toolbar = false;
		$helper->title = $this->displayName;
		$helper->submit_action = 'btnSubmit';
		$helper->tpl_vars = array (
				'fields_value' => $this->getConfigFieldsValues ()
		);
		return $helper->generateForm ( $fields_form );
	}
	
	/**
	 * Saving value from form.
	 *
	 * @return mixed[]
	 *
	 * @access private
	 */
	private function getConfigFieldsValues() {
		return array (
				'HOMEPAY_USER' => Tools::getValue ( 'HOMEPAY_USER', Configuration::get ( 'HOMEPAY_USER' ) ),
				'HOMEPAY_PUBLIC' => Tools::getValue ( 'HOMEPAY_PUBLIC', Configuration::get ( 'HOMEPAY_PUBLIC' ) ),
				'HOMEPAY_PRIVATE' => Tools::getValue ( 'HOMEPAY_PRIVATE', Configuration::get ( 'HOMEPAY_PRIVATE' ) ),
				'HOMEPAY_PUBLIC_PSC' => Tools::getValue ( 'HOMEPAY_PUBLIC_PSC', Configuration::get ( 'HOMEPAY_PUBLIC_PSC' ) ),
				'HOMEPAY_PRIVATE_PSC' => Tools::getValue ( 'HOMEPAY_PRIVATE_PSC', Configuration::get ( 'HOMEPAY_PRIVATE_PSC' ) ),
				'HOMEPAY_PBL' => Tools::getValue ( 'HOMEPAY_PBL', Configuration::get ( 'HOMEPAY_PBL' ) ),
				'HOMEPAY_PSC' => Tools::getValue ( 'HOMEPAY_PSC', Configuration::get ( 'HOMEPAY_PSC' ) )
		);
	}
	/**
	 * SMART path.
	 * @param string $name
	 *
	 * @return string
	 */
	public function buildTemplatePath($name, $type) {

		return 'module:homepay/views/templates/' . $type . '/' . $name . '.tpl';
	}
}