<?php
class FANCourier_Ship_Model_Carrier_FANCourier
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface {

	protected $_code = 'FANCourier';

	public function isTrackingAvailable() {
		return true;
	}

	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
	{
		if (!Mage::getStoreConfig('carriers/'.$this->_code.'/active')) {
			return false;
		}

                $price=array();

                $username = $this->getConfigData('username');
                $parola = $this->getConfigData('password');
                $clientid = $this->getConfigData('clientid');
                $parcel = $this->getConfigData('parcel');
                $labels = $this->getConfigData('labels');
                $ramburs = $this->getConfigData('ramburs');
                $content = $this->getConfigData('content');
                $contcolector = $this->getConfigData('contcolector');
                $redcode = $this->getConfigData('redcode');
                $express = $this->getConfigData('express');
                $paymentdest = $this->getConfigData('paymentdest');
                $paymentrbdest = $this->getConfigData('paymentrbdest');
                $payment0 = $this->getConfigData('payment0');
                $min_gratuit_tara = $this->getConfigData('min_gratuit_tara');
                $min_gratuit_bucuresti = $this->getConfigData('min_gratuit_bucuresti');
                $suma_fixa = $this->getConfigData('suma_fixa');
                $observatii = $this->getConfigData('comment');
                $asigurare = $this->getConfigData('asigurare');
                $totalrb = $this->getConfigData('totalrb');
                $onlyadm = $this->getConfigData('onlyadm');
                $fara_tva = $this->getConfigData('fara_tva');
				//doar km suplimentari
				$doar_km = $this->getConfigData('doar_km');
                $cashondelivery = $this->getConfigData('cashondelivery');
				$pers_contact_expeditor = $this->getConfigData('pers_contact');
				$deschidere_livrare = $this->getConfigData('deschidere');
                $msg="Comanda nu a fost procesata de catre FAN Courier.<br>Va rugam sa corectati datele de livrare conform mesajului de mai jos: <br><br>";

				
				Mage::log('Min gratuit ' +  $min_gratuit_tara);
				
                if ($this->getConfigData('aplicare_curs')){
                    $currencyrate = round(Mage::app()->getStore()->getCurrentCurrencyRate(), 4);
                } else {
                    $currencyrate = 1;
                }
				
				//optiuni
				$optiuni = '';
				if ($deschidere_livrare == 1) $optiuni .= 'A';

                if (is_numeric($min_gratuit_tara)) $min_gratuit_tara = $min_gratuit_tara + 0; else $min_gratuit_tara = 0 + 0;
                if (is_numeric($min_gratuit_bucuresti)) $min_gratuit_bucuresti = $min_gratuit_bucuresti + 0; else $min_gratuit_bucuresti = 0 + 0;
                if (is_numeric($suma_fixa)) $suma_fixa = $suma_fixa + 0; else $suma_fixa = 0 + 0;

                if ($parcel){
                    $plic="0";
                    if (is_numeric($labels)){
                        $colet=$labels;
                    } else {
                        $colet=1;
                    }
                } else {
                    $colet="0";
                    if (is_numeric($labels)){
                        $plic=$labels;
                    } else {
                        $plic=1;
                    }
                }

                if ($totalrb){
                    $totalrb = "1";
                } else {
                    $totalrb = "0";
                }

                $q = 'x';
                foreach ($request->getAllItems() as $item) {
                    $q = $item->GetId();
                }

                $resource = Mage::getSingleton('core/resource');
                $connection = $resource->getConnection('core_write');
                $sales_flat_quote_address = $resource->getTableName('sales_flat_quote_address');
                $sales_flat_quote_item = $resource->getTableName('sales_flat_quote_item');
                $directory_country_region = $resource->getTableName('directory_country_region');
                $sales_flat_quote_payment = $resource->getTableName('sales_flat_quote_payment');

                if (is_numeric($q)) {
                            $query = "SELECT $sales_flat_quote_address.* FROM $sales_flat_quote_address left join $sales_flat_quote_item on $sales_flat_quote_item.quote_id = $sales_flat_quote_address.quote_id WHERE $sales_flat_quote_address.address_type='shipping' and $sales_flat_quote_item.item_id = {$q}";
                            $resource = Mage::getSingleton('core/resource');
                            $readConnection = $resource->getConnection('core_read');
                            $results = $readConnection->fetchAll($query);

                            if (count($results)>0){

                                        if ($asigurare){
                                                $valoaredeclarata = number_format(round((float)$results[0]["subtotal_incl_tax"],2), 2, '.', '');
                                        } else {
                                                $valoaredeclarata = 0;
                                        }

                                        $greutate = number_format(round((float)$results[0]["weight"],0), 0, '.', '');
										//
										if ($greutate>1)
										{
											$plic=0;
											if (is_numeric($labels))
											{
												$colet=$labels;
											} 
											else 
											{
												$colet=1;
											}
										}
										//
                                        if (round((float)$results[0]["weight"],0)>5) $redcode = false;

                                        $lungime=0;
                                        $latime=0;
                                        $inaltime=0;

                                        if ($paymentdest){
                                            $plata_expeditiei="destinatar";
                                        }else{
                                            $plata_expeditiei="expeditor";
                                        }

                                        if ($cashondelivery){
                                            $query_payment = "SELECT distinct method FROM $sales_flat_quote_payment WHERE quote_id = {$results[0]["quote_id"]}";
                                            $resource_payment = Mage::getSingleton('core/resource');
                                            $readConnection_payment = $resource_payment->getConnection('core_read');
                                            $results_payment = $readConnection_payment->fetchAll($query_payment);
                                            foreach ($results_payment as $result_payment) {
                                                $payment_cashondelivery = $result_payment["method"];
                                            }
                                        }

                                        $rambursare_number = 0 + 0;
                                        $rambursare = '';
										
										//Localitate (pt minim gratuit)
										$localitate_dest = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $request->getDestCity());

                                        if (strtolower($localitate_dest) == 'bucuresti' and $min_gratuit_bucuresti!=0) {
                                            $min_gratuit = $min_gratuit_bucuresti;
                                        } else {
                                            $min_gratuit = $min_gratuit_tara;
                                        }

										//In caz de ramburs si/sau cash on delivery
                                        if ($ramburs and ($cashondelivery==0 or ($cashondelivery==1 and $payment_cashondelivery=='cashondelivery')))
										{
                                            if ($contcolector)
											{
                                                $rambursare = number_format(round((float)$results[0]["subtotal"],2), 2, '.', '');
                                                $rambursare_number = round((float)$results[0]["subtotal"],2)+0;
												
												//Valoare fixa adaugare la ramburs / cont colector
                                                if($suma_fixa!=0 and $totalrb=="1" and ($min_gratuit>$rambursare_number or $min_gratuit==0))
                                                {                 
													//$suma_fixa=$10;
                                                	$totalrb="0";
													//Verificare daca minim gratuit < ramburs
													if ($min_gratuit<=$rambursare_number and $min_gratuit!=0)
													{
														$suma_fixa=0;
													}
                                                	$rambursare=$rambursare+$suma_fixa;
                                                }
                                                
                                                if ($min_gratuit<=$rambursare_number and $min_gratuit!=0)
                                                {
                                                	$totalrb="0";
                                                	
                                                }
                                                //sfarsit
												
                                                if ($paymentrbdest){
                                                    $plata_expeditiei_ramburs="destinatar";
                                                }else{
                                                    $plata_expeditiei_ramburs="expeditor";
                                                }
                                            } 
											else 
											{
                                                $rambursare = (string)number_format(round((float)$results[0]["subtotal"],2), 2, '.', '')." LEI";
                                                $rambursare_number = round((float)$results[0]["subtotal"],2)+0;
												
												//Valoare fixa adaugare la ramburs / standard
                                                if($suma_fixa!=0 and $totalrb=="1" and ($min_gratuit>$rambursare_number or $min_gratuit==0))
                                                {                    
                                                	$totalrb="0";													
													//Verificare daca minim gratuit < ramburs
													if ($min_gratuit<=$rambursare_number and $min_gratuit!=0)
													{
														$suma_fixa=0;
													}													
													
                                                	$rambursare=$rambursare+$suma_fixa;
                                                }
                                                
                                                if ($min_gratuit<=$rambursare_number and $min_gratuit!=0)
                                                {
                                                	$totalrb="0";													                                               	
                                                }
                                                //sfarsit
												
												//plata transport ramburs
                                                if ($paymentrbdest)
												{
                                                    $plata_expeditiei_ramburs="destinatar";
                                                }
												else
												{
                                                    $plata_expeditiei_ramburs="expeditor";
                                                }
                                            }
                                        } 
										else 
										{
                                            $rambursare_number = 0;
                                            $rambursare = '';

                                        }
                                        
										//Daca nu exista ramburs
                                        if ($rambursare =='')
										{
                                                $totalrb = "0";
                                                $rambursare = 0;
                                                $contcolector= false;
                                        }

										//Daca e diferit de cash on delivery
                                        if (!($cashondelivery==1 and $payment_cashondelivery=='cashondelivery'))
										{
                                                $plata_expeditiei="expeditor";
												
												//daca exista ramburs
												if($rambursare_number>0)
												{
													//plata ramburs expeditie
													if ($paymentrbdest)
													{
														$plata_expeditiei_ramburs="destinatar";
													}
													else
													{
														$plata_expeditiei_ramburs="expeditor";
													}
												
													//plata expeditiei
													if ($paymentdest)
													{
														$plata_expeditiei="destinatar";
														$observatii=$rambursare;
														
													}
													else
													{
														$plata_expeditiei="expeditor";
													}
												
												}
												else
												{
													$plata_expeditie_ramburs="";
												}
                                        }

                                        
                                        
                                        $judet_dest = $request->getDestRegionID();

                                        $query_directory_country_region = "SELECT * FROM $directory_country_region WHERE region_id = '$judet_dest' LIMIT 0 , 30";
                                        $resource_directory_country_region = Mage::getSingleton('core/resource');
                                        $readConnection_directory_country_region = $resource_directory_country_region->getConnection('core_read');
                                        $results_directory_country_region = $readConnection_directory_country_region->fetchAll($query_directory_country_region);
                                        $judet_dest = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $results_directory_country_region[0]["default_name"]);
                                        $country_dest = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $results_directory_country_region[0]["country_id"]);

                                        $session = Mage::getSingleton('admin/session');
                                        if (!$session->getUser())
                                        {
                                            $session = Mage::getSingleton('customer/session')->getCustomer();
                                            $customerid = Mage::getSingleton('customer/session')->getCustomerId();
                                            //$pers_contact_expeditor = $customerid." [".$session->getFirstname()." ".$session->getLastname()."]";
                                        } else {
                                            $session = Mage::getSingleton('admin/session')->getUser();
                                            //$pers_contact_expeditor = $session->getFirstname()." ".$session->getLastname();
                                        }

                                        $continut='';
                                        if ($content){
                                            $query_sku = "SELECT distinct sku FROM $sales_flat_quote_item WHERE quote_id = {$results[0]["quote_id"]} order by sku";
                                            $resource_sku = Mage::getSingleton('core/resource');
                                            $readConnection_sku = $resource_sku->getConnection('core_read');
                                            $results_sku = $readConnection_sku->fetchAll($query_sku);
                                            foreach ($results_sku as $result_sku) {
                                                $continut = $continut.", ".$result_sku["sku"];
                                            }
                                        }
                                        if ($continut!=""){$continut=substr($continut, 2);}

                                        if (!is_null($results[0]["company"]) and $results[0]["company"]!=""){
                                            $nume_destinatar = $results[0]["company"];
											$nume_destinatar=urlencode($nume_destinatar);
                                            $persoana_contact = $results[0]["firstname"]." ".$results[0]["lastname"];
											$persoana_contact=urlencode($persoana_contact);
                                        } else {
                                            $nume_destinatar = $results[0]["firstname"]." ".$results[0]["lastname"];
											$nume_destinatar=urlencode($nume_destinatar);
                                            $persoana_contact = "";
                                        }

                                        $telefon = $results[0]["telephone"];
                                        
                                        // alex g 12.02.2014 adaugare email pentru transmitere catre selfawb
                                        $email=$results[0]["email"];
                                        //sfarsit alex g
                                        
                                        if (!is_null($results[0]["fax"]) and $results[0]["fax"]!='') $telefon=$telefon." / ".$results[0]["fax"];

                                        $strada = $request->getDestStreet();
                                        $postalcode = $request->getDestPostcode();

                                        $handling = Mage::getStoreConfig('carriers/'.$this->_code.'/handling');
                                        $result = Mage::getModel('shipping/rate_result');

										//cand min gratuit mai mic ca ramburs plata la expeditor
                                        if ($min_gratuit<$rambursare_number and $min_gratuit!=0)
										{
											$plata_expeditiei="expeditor";
										}
										//
										
										
                                        if ($country_dest=="RO"){
                                                $url = 'http://www.selfawb.ro/order.php';
                                                $c = curl_init ($url);
                                                curl_setopt ($c, CURLOPT_POST, true);
                                                curl_setopt ($c, CURLOPT_POSTFIELDS, "username=$username&user_pass=$parola&client_id=$clientid&return=services");
                                                curl_setopt ($c, CURLOPT_RETURNTRANSFER, true);
                                                $page = curl_exec ($c);
                                                curl_close ($c);

                                                $servicii_data = str_getcsv($page,"\n"); // COMPATIBIL PENTRU VERSIUNE PHP > 5.3.X
                                                //$servicii_data = explode("\n",ltrim(rtrim($page))); // COMPATIBIL PENTRU VERSIUNE PHP < 5.2.X

                                                foreach($servicii_data as $tip_serviciu_info){
                                                    $tip_serviciu_info = str_replace('"','',$tip_serviciu_info);
                                                    if ((!$contcolector or round($rambursare, 0)==0)){
                                                        $tip_serviciu = explode(",",$tip_serviciu_info);														
                                                        if ($tip_serviciu[1]==0 and (($tip_serviciu[2]==0 and $tip_serviciu[3]==0) or ($tip_serviciu[2]==1 and $redcode) or ($tip_serviciu[3]==1 and $express))){
                                                                $url = 'http://www.selfawb.ro/order.php';
                                                                $c = curl_init ($url);
                                                                curl_setopt ($c, CURLOPT_POST, true);
                                                                curl_setopt ($c, CURLOPT_POSTFIELDS, "username=$username&user_pass=$parola&client_id=$clientid&plata_expeditiei=$plata_expeditiei&tip_serviciu=$tip_serviciu[0]&localitate_dest=$localitate_dest&judet_dest=$judet_dest&plic=$plic&colet=$colet&greutate=$greutate&lungime=$lungime&latime=$latime&inaltime=$inaltime&valoare_declarata=$valoaredeclarata&plata_ramburs=$plata_expeditiei_ramburs&ramburs=$rambursare&pers_contact_expeditor=$pers_contact_expeditor&observatii=$observatii&continut=$continut&nume_destinatar=$nume_destinatar&persoana_contact=$persoana_contact&telefon=$telefon&email=$email&strada=$strada&postalcode=$postalcode&totalrb=$totalrb&admin=$onlyadm&fara_tva=$fara_tva&suma_fixa=$suma_fixa&doar_km=$doar_km&optiuni=$optiuni");
                                                                curl_setopt ($c, CURLOPT_RETURNTRANSFER, true);
                                                                $page = curl_exec ($c);
                                                                curl_close ($c);
                                                                $price = explode("|||",$page);
                                                                if (!($payment0) and ($min_gratuit>$rambursare_number or $min_gratuit==0)) {$price_standard = $price[0];} else {$price_standard = 0;$suma_fixa=0;}
																//Standard fara ramburs
																
																
																if ($rambursare_number==0)
																{
																	$valoare_produse=round((float)$results[0]["grand_total"],2)+0;
																	if ($min_gratuit<$valoare_produse)
																	{
																		$price_standard = 0;
																	}
																}
																//
                                                                $link_standard = $price[1];
                                                                //if ($suma_fixa!=0) $price_standard = $suma_fixa;
                                                                $price_standard = $price_standard / $currencyrate;

                                                               // if (is_numeric($price_standard) and $link_standard!=""){
                                                                        $method = Mage::getModel('shipping/rate_result_method');
                                                                        $method->setCarrier($this->_code);
                                                                        $method->setCarrierTitle($this->getConfigData('title'));
                                                                        $method->setMethod(str_replace(" ","_",$tip_serviciu[0]));
                                                                        //$method->setMethodTitle("<strong><span class='normal'><a target='_blank' href='http://www.selfawb.ro/order.php?order_id=$link_standard'>$tip_serviciu[0]</a></span></strong>");
                                                                        $method->setMethodTitle("<a href=\"http://www.selfawb.ro/order.php?order_id=$link_standard\" target=\"_blank\">$tip_serviciu[0]</a>");
                                                                        $method->setPrice($price_standard);
                                                                        $method->setCost($price_standard);
                                                                        $result->append($method);
                                                                }else{
                                                                        if ($tip_serviciu[2]==0 and $tip_serviciu[3]==0){
                                                                            $error = Mage::getModel('shipping/rate_result_error');
                                                                            $error->setCarrier($this->_code);
                                                                            $error->setErrorMessage($msg.$page);
                                                                            $result->append($error);
                                                                            return $result;
                                                                        }
                                                                }
                                                        }
                                                        unset($tip_serviciu);
                                                    } else {
                                                        $tip_serviciu = explode(",",$tip_serviciu_info);
                                                        if ($tip_serviciu[1]==1 and (($tip_serviciu[2]==0 and $tip_serviciu[3]==0) or ($tip_serviciu[2]==1 and $redcode) or ($tip_serviciu[3]==1 and $express))){
                                                                $url = 'http://www.selfawb.ro/order.php';
                                                                $c = curl_init ($url);
                                                                curl_setopt ($c, CURLOPT_POST, true);
                                                                curl_setopt ($c, CURLOPT_POSTFIELDS, "username=$username&user_pass=$parola&client_id=$clientid&plata_expeditiei=$plata_expeditiei&tip_serviciu=$tip_serviciu[0]&localitate_dest=$localitate_dest&judet_dest=$judet_dest&plic=$plic&colet=$colet&greutate=$greutate&lungime=$lungime&latime=$latime&inaltime=$inaltime&valoare_declarata=$valoaredeclarata&plata_ramburs=$plata_expeditiei_ramburs&ramburs=$rambursare&pers_contact_expeditor=$pers_contact_expeditor&observatii=$observatii&continut=$continut&nume_destinatar=$nume_destinatar&persoana_contact=$persoana_contact&telefon=$telefon&email=$email&strada=$strada&postalcode=$postalcode&totalrb=$totalrb&admin=$onlyadm&fara_tva=$fara_tva&suma_fixa=$suma_fixa&doar_km=$doar_km&optiuni=$optiuni");
                                                                curl_setopt ($c, CURLOPT_RETURNTRANSFER, true);
                                                                $page = curl_exec ($c);
                                                                curl_close ($c);
                                                                $price = explode("|||",$page);
                                                                if (!($payment0) and ($min_gratuit>$rambursare_number or $min_gratuit==0)) {$price_standard = $price[0];} else {$price_standard = 0;$suma_fixa=0;}
                                                                $link_standard = $price[1];
                                                                //if ($suma_fixa!=0) $price_standard = $suma_fixa;
                                                                $price_standard = $price_standard / $currencyrate;

                                                                if (is_numeric($price_standard) and $link_standard!=""){
                                                                        $method = Mage::getModel('shipping/rate_result_method');
                                                                        $method->setCarrier($this->_code);
                                                                        $method->setCarrierTitle($this->getConfigData('title'));
                                                                        $method->setMethod(str_replace(" ","_",$tip_serviciu[0]));
                                                                        $method->setMethodTitle("<a href=\"http://www.selfawb.ro/order.php?order_id=$link_standard\" target=\"_blank\">$tip_serviciu[0]</a>");
                                                                        $method->setPrice($price_standard);
                                                                        $method->setCost($price_standard);
                                                                        $result->append($method);
                                                                }else{
                                                                        if ($tip_serviciu[2]==0 and $tip_serviciu[3]==0){
                                                                            $error = Mage::getModel('shipping/rate_result_error');
                                                                            $error->setCarrier($this->_code);
                                                                            $error->setErrorMessage($msg.$page);
                                                                            $result->append($error);
                                                                            return $result;
                                                                        }
                                                                }
                                                        }
                                                        unset($tip_serviciu);
                                                    }
                                                }
                                        }

                                        if ($price_standard == 0 )$this->_updateFreeMethodQuote($request);
                                        return $result;
                            }
                }

	}

        protected function _updateFreeMethodQuote($request)
        {
            $freeShipping = false;
            $items = $request->getAllItems();
            $c = count($items);
            for ($i = 0; $i < $c; $i++) {
                if ($items[$i]->getProduct() instanceof Mage_Catalog_Model_Product) {
                    if ($items[$i]->getFreeShipping()) {
                        $freeShipping = true;
                    } else {
                        return;
                    }
                }
            }
            if ($freeShipping) {
                $request->setFreeShipping(true);
            }
        }

	/**
	 * Get Tracking Info
	 *
	 * @param mixed $tracking
	 * @return mixed
	 */
	public function getTrackingInfo($tracking) {
		$result = $this->getTracking($tracking);
		if ($result instanceof Mage_Shipping_Model_Tracking_Result){
			if ($trackings = $result->getAllTrackings()) {
				return $trackings[0];
			}
		} elseif (is_string($result) && !empty($result)) {
			return $result;
		}

		return false;
	}

	/**
	 * Get Tracking
	 *
	 * @param array $trackings
	 * @return Mage_Shipping_Model_Tracking_Result
	 */
	public function getTracking($trackings) {
		$this->_result = Mage::getModel('shipping/tracking_result');
		foreach ((array) $trackings as $code) {
			$this->_getTracking($code);
		}

		return $this->_result;
	}

	/**
	 * Protected Get Tracking, opens the request to FANCourier
	 *
	 * @param string $code
	 * @return boolean
	 */
	protected function _getTracking($code) {

        $msg="AWB $code ";

        $error = Mage::getModel('shipping/tracking_result_error');
        $error->setTracking($code);
        $error->setCarrier('FANCourier');
        $error->setCarrierTitle($this->getConfigData('title'));
        $error->setErrorMessage($msg);

        $username = $this->getConfigData('username');
        $parola = $this->getConfigData('password');
        $clientid = $this->getConfigData('clientid');
        $url = 'http://www.selfawb.ro/awb_tracking_integrat.php';

        $c = curl_init ($url);
        curl_setopt ($c, CURLOPT_POST, true);
        curl_setopt ($c, CURLOPT_POSTFIELDS, "username=$username&user_pass=$parola&client_id=$clientid&AWB=$code&display_mode=5");
        curl_setopt ($c, CURLOPT_RETURNTRANSFER, true);
        $page = curl_exec ($c);
        curl_close ($c);
        if (strlen($page)>40){
            $track = array();
            $track = json_decode($page, true);
            $tracking = Mage::getModel('shipping/tracking_result_status');
            $tracking->setTracking($code);
            $tracking->setCarrier('FANCourier');
            $tracking->setCarrierTitle($this->getConfigData('title'));
            $tracking->addData($track);
        } else {
            $tracking = Mage::getModel('shipping/tracking_result_status');
            $tracking->setCarrier('FANCourier');
            $tracking->setCarrierTitle($this->getConfigData('title'));
            $tracking->setTracking($code);
            $tracking->setPopup(1);
            $tracking->setUrl("http://www.fancourier.ro/awb.php?xawb=$code");
        }
        
        $this->_result->append($tracking);
        return true;
	}

	public function getAllowedMethods() {
		return array($this->_code=>$this->getConfigData('title'));
	}


}