<?php

class OrganicInternet_SimpleConfigurableProducts_Helper_Data
    extends Mage_Core_Helper_Abstract {

    public function getProductAttributeData() {

        $attributeData = array();

        try {

            /** @var Mage_Core_Controller_Request_Http $request */
            $request = Mage::app()->getRequest();

            $params = $request->getParams();

            $block = Mage::getBlockSingleton('catalog/product_view_type_configurable');
            
            /** @var Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection $attributes */
            $attributes = $block->getAllowAttributes();
            foreach ($attributes as $attribute) {
                /** @var Mage_Catalog_Model_Product_Type_Configurable_Attribute $attribute */

                /** @var Mage_Catalog_Model_Resource_Eav_Attribute $productAttribute */
                $productAttribute = $attribute->getProductAttribute();

                $attributeCode = $productAttribute->getAttributeCode();

                if (isset($params['AV'][$attributeCode])) {
                    $attributeData[$attribute->getAttributeId()] = array(
                        'attribute_id'   => $attribute->getAttributeId(),
                        'attribute_code' => $attributeCode,
                        'value'          => $params['AV'][$attributeCode]
                    );
                }

            }

        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $attributeData;
    }

}
