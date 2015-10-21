<?php

class OrganicInternet_SimpleConfigurableProducts_Catalog_Block_Product_View_Type_Configurable
    extends Mage_Catalog_Block_Product_View_Type_Configurable
{
    public function getJsonConfig()
    {
        $config = Zend_Json::decode(parent::getJsonConfig());

        $aProducts = $this->getAllowProducts();

        Mage::dispatchEvent('scp_config_before_prepare', array('config' => &$config, 'products' => $aProducts, 'block' => $this));

        $childProducts = array();

        //Create the extra price and tier price data/html we need.
        foreach ($aProducts as $product) {
            $productId  = $product->getId();
            $childProducts[$productId] = array(
                'price' => $this->_registerJsPrice($this->_convertPrice($product->getPrice())),
                'finalPrice' => $this->_registerJsPrice($this->_convertPrice($product->getFinalPrice())),
                'sku' => $product->getSku(),
            );

            if (Mage::getStoreConfig('SCP_options/product_page/change_name')) {
                $childProducts[$productId]["productName"] = $product->getName();
            }
            if (Mage::getStoreConfig('SCP_options/product_page/change_description')) {
                $childProducts[$productId]["description"] = $this->helper('catalog/output')->productAttribute($product, $product->getDescription(), 'description');
            }
            if (Mage::getStoreConfig('SCP_options/product_page/change_short_description')) {
                $childProducts[$productId]["shortDescription"] = $this->helper('catalog/output')->productAttribute($product, nl2br($product->getShortDescription()), 'short_description');
            }

            if (Mage::getStoreConfig('SCP_options/product_page/change_attributes')) {
                $childBlock = $this->getLayout()->createBlock('catalog/product_view_attributes');
                $childProducts[$productId]["productAttributes"] = $childBlock->setTemplate('catalog/product/view/attributes.phtml')
                    ->setProduct($product)
                    ->toHtml();
            }
            
            $bChangeStock = Mage::getStoreConfig('SCP_options/product_page/change_stock');
            if ($bChangeStock) {
                // Stock status HTML
                $oStockBlock = $this->getLayout()->createBlock('catalog/product_view_type_simple')->setTemplate('catalog/product/view/scpavailability.phtml');
                $childProducts[$productId]["stockStatus"] = $oStockBlock->setProduct($product)->toHtml();

                // Add to cart button
                $oAddToCartBlock = $this->getLayout()->createBlock('catalog/product_view_type_simple')->setTemplate('catalog/product/view/addtocart.phtml');
                $childProducts[$productId]["addToCart"] = $oAddToCartBlock->setProduct($product)->toHtml();
            }
            
            $bShowProductAlerts = Mage::getStoreConfig(Mage_ProductAlert_Model_Observer::XML_PATH_STOCK_ALLOW);
            if ($bShowProductAlerts && !$product->isAvailable()) {
                $oAlertBlock = $this->getLayout()->createBlock('productalert/product_view')
                        ->setTemplate('productalert/product/view.phtml')
                        ->setSignupUrl(Mage::helper('productalert')->setProduct($product)->getSaveUrl('stock'));;
                $childProducts[$productId]["alertHtml"] = $oAlertBlock->toHtml();
            }

            // if image changing is enabled..
            if (Mage::getStoreConfig('SCP_options/product_page/change_image')) {
                // but dont bother if fancy image changing is enabled
                if (!Mage::getStoreConfig('SCP_options/product_page/change_image_fancy')) {
                    // If image is not placeholder...
                    if ($product->getImage()!=='no_selection') {
                        $childProducts[$productId]["imageUrl"] = (string)Mage::helper('catalog/image')->init($product, 'image');
                    }
                }
            }
        }

        // Remove any existing option prices.
        // Removing holes out of existing arrays is not nice,
        // but it keeps the extension's code separate so if Varien's getJsonConfig
        // is added to, things should still work.
        if (is_array($config['attributes'])) {
            foreach ($config['attributes'] as $attributeID => &$info) {
                if (is_array($info['options'])) {
                    foreach ($info['options'] as &$option) {
                        unset($option['price']);
                    }
                    unset($option); // clear foreach var ref

                    /* Sort the Options */
                    $info['options'] = $this->_sortOptions($info['options']);
                }
            }
            unset($info); // clear foreach var ref
        }


        $p = $this->getProduct();
        $config['childProducts'] = $childProducts;
        if ($p->getMaxPossibleFinalPrice() != $p->getFinalPrice()) {
            $config['priceFromLabel'] = $this->__('Price From:');
        } else {
            $config['priceFromLabel'] = $this->__('');
        }
        $config['ajaxBaseUrl'] = Mage::getUrl('oi/ajax/');
        $config['productName'] = $p->getName();
        $config['description'] = $this->helper('catalog/output')->productAttribute($p, $p->getDescription(), 'description');
        $config['shortDescription'] = $this->helper('catalog/output')->productAttribute($p, nl2br($p->getShortDescription()), 'short_description');

        if (Mage::getStoreConfig('SCP_options/product_page/change_image')) {
            $config['imageUrl'] = (string)Mage::helper('catalog/image')->init($p, 'image');
        }

        $childBlock = $this->getLayout()->createBlock('catalog/product_view_attributes');
        $config['productAttributes'] = $childBlock->setTemplate('catalog/product/view/attributes.phtml')
            ->setProduct($this->getProduct())
            ->toHtml();
        
        $bShowProductAlerts = Mage::getStoreConfig(Mage_ProductAlert_Model_Observer::XML_PATH_STOCK_ALLOW);
        if ($bShowProductAlerts && !Mage::registry('child_product')->isAvailable()) {
            $oAlertBlock = $this->getLayout()->createBlock('productalert/product_view')
                    ->setTemplate('productalert/product/view.phtml')
                    ->setSignupUrl(Mage::helper('productalert')->setProduct(Mage::registry('child_product'))->getSaveUrl('stock'));;
            $config['alertHtml'] = $oAlertBlock->toHtml();
        }

        if (Mage::getStoreConfig('SCP_options/product_page/change_image')) {
            if (Mage::getStoreConfig('SCP_options/product_page/change_image_fancy')) {
                $childBlock = $this->getLayout()->createBlock('catalog/product_view_media');
                $config['imageZoomer'] = $childBlock->setTemplate('catalog/product/view/media.phtml')
                    ->setProduct($this->getProduct())
                    ->toHtml();
            }
        }

        if (Mage::getStoreConfig('SCP_options/product_page/show_price_ranges_in_options')) {
            $config['showPriceRangesInOptions'] = true;
            $config['rangeToLabel'] = $this->__('to');
        }

        $oEventData = new Varien_Object(array('config' => &$config, 'products' => $aProducts, 'block' => $this));

        Mage::dispatchEvent('scp_config_after_prepare', array('event_data' => $oEventData));

        return Zend_Json::encode($config);
        //parent getJsonConfig uses the following instead, but it seems to just break inline translate of this json?
        //return Mage::helper('core')->jsonEncode($config);
    }

    /**
     * Preserves the order of attribute options from the position field in the admin attribute option settings
     * @param  array $options
     * @return array
     */
    protected function _sortOptions($options)
    {
        if (count($options)) {
            if (!$this->_read || !$this->_tbl_eav_attribute_option) {
                $resource = Mage::getSingleton('core/resource');

                $this->_read = $resource->getConnection('core_read');
                $this->_tbl_eav_attribute_option = $resource->getTableName('eav_attribute_option');
            }

            // Gather the option_id for all our current options
            $optionIds = array();
            foreach ($options as $option) {
                $optionIds[] = $option['id'];

                $varName  = 'option_id_' . $option['id'];
                $$varName = $option;
            }

            $sql    = "SELECT `option_id` FROM `{$this->_tbl_eav_attribute_option}` WHERE `option_id` IN('" . implode('\',\'', $optionIds) . "') ORDER BY `sort_order`";
            $result = $this->_read->fetchCol($sql);

            $options = array();
            foreach ($result as $optionId) {
                $varName  = 'option_id_' . $optionId;
                $options[] = $$varName;
            }
        }

        return $options;
    }

}
