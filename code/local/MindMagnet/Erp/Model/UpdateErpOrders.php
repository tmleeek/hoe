<?php

class MindMagnet_Erp_Model_UpdateErpOrders
{

    /**
     *
     * @return string - orders last 5 days - XML format
     * @params - null
     *
     */
    public function updateOrdersErp()
    {
        $collection = Mage::getModel('sales/order')->getCollection()
                     ->addAttributeToSelect('*')
                     ->addFieldToFilter('created_at', array(
                            'from'     => strtotime('-5 day', time()),
                            'to'       => time(),
                            'datetime' => true
                    ));

        $out = '';
        $out .= '<?xml version="1.0" encoding="UTF-8"?>';
        $out .= '<xml>';
        $out .= '<root>'
        ;
        if (count($collection) == 0){
            $out .= 'Nu sunt comenzi pentru ultimele 5 zile!';
        }
            foreach($collection as $order)
            {

                try{
                    if ($billingAddress = $order->getBillingAddress()){
                        $billingCity = $billingAddress->getCity();
                        $billingStreet = $billingAddress->getData('street');
                        $billingRegion = $billingAddress->getRegion();
                        $billingPhone = $billingAddress->getTelephone();
                    }

                    if ($shippingAddress = $order->getShippingAddress()){
                        $shippingCity = $shippingAddress->getCity();
                        $shippingStreet = $shippingAddress->getData('street');
                        $shippingRegion = $shippingAddress->getRegion();
                        $shippingPhone = $shippingAddress->getTelephone();
                    }

                    $out .= '<order>';
                        $out .= '<serie>HFP</serie>';
                        $out .= '<nr>';
                            $out .=  $order->getId();
                        $out .= '</nr>';
                        $out .= '<total>';
                            $out .=  $order->getGrandTotal();
                        $out .= '</total>';
                        $out .= '<date>';
                          $out .=  $order->getCreatedAt();
                        $out .= '</date>';
                        $out .= '<firstname>';
                            $out .=  $order->getCustomerFirstname();
                        $out .= '</firstname>';
                        $out .= '<lastname>';
                            $out .=  $order->getCustomerLastname();
                        $out .= '</lastname>';
                        $out .= '<telephone>';
                            $out .= $billingPhone;
                        $out .= '</telephone>';
                        //@TODO cnp
                        $out .= '<cnp>';
                            $out .=  $order->getBillingAddress()->getVatId();
                        $out .= '</cnp>';
                        $out .= '<email>';
                            $out .=  $order->getCustomerEmail();
                        $out .= '</email>';
                        $out .= '<address>';
                            $out .=  $billingStreet;
                        $out .= '</address>';
                        $out .= '<city>';
                            $out .= $billingCity;
                        $out .= '</city>';
                        $out .= '<judet>';
                            $out .=  $billingRegion;
                        $out .= '</judet>';

                        $out .= '<shipping_address>';
                            $out .=  $shippingStreet;
                        $out .= '</shipping_address>';
                        $out .= '<shipping_city>';
                            $out .= $shippingCity;
                        $out .= '</shipping_city>';
                        $out .= '<shipping_judet>';
                            $out .=  $shippingRegion;
                        $out .= '</shipping_judet>';


                        $out .= '<comment>';
                            $out .=  $order->getCustomerNote();
                        $out .= '</comment>';
                        $out .= '<lines>';

                        /* Get order products */
                            $orderedItems = $order->getAllItems();
                            foreach ($orderedItems as $item)
                            {
                                $out .= '<line>';
                                    $out .= '<cod>';
                                        $out .= $item->getSku();
                                    $out .= '</cod>';
                                    $out .= '<name>';
                                        $out .= htmlspecialchars($item->getName());
                                    $out .= '</name>';
                                    $out .= '<quantity>';
                                        $out .= (int)$item->getQtyOrdered();
                                    $out .= '</quantity>';
                                    $out .= '<price>';
                                        $out .= $item->getPrice();
                                    $out .= '</price>';
                                $out .= '</line>';
                            }

                            if ($order->getShippingDescription())
                            {
                            $out .= '<line>';
                                $out .=  '<cod>1</cod>';
                                $out .=  '<name>';
                                    $out .=  htmlspecialchars($order->getShippingDescription());
                                $out .=  '</name>';
                                $out .=  '<quantity>1</quantity>';
                                $out .=  '<price>';
                                    $out .= $order->getShippingAmount();
                                $out .=  '</price>';
                            $out .= '</line>';
                            }
                        $out .= '</lines>';
                    $out .= '</order>';

                } catch (Exception $e) {
                    Mage::logException('ERP Update Orders -> '.$e->getMessage());
                }

            };

            $out .= '</root>';
            $out .= '</xml>';

        Mage::log('xml created', Zend_Log::INFO ,'erp_orders.log');

        return $out;
    }
}