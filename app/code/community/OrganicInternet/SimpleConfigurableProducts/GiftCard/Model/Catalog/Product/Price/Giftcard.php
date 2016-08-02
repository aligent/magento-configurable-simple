<?php
/**
 * @author: Luke Mills
 * Date: 16/10/13
 */

class OrganicInternet_SimpleConfigurableProducts_GiftCard_Model_Catalog_Product_Price_Giftcard 
    extends Enterprise_GiftCard_Model_Catalog_Product_Price_Giftcard
{

    /**
     * @TODO: This _may_ have an impact on flat indexing and the category page. May need to test this and come up with a different solution if necessary. 
     */
    public function getFinalPrice($qty=null, $product) {
        return max($this->getMinAmount($product), parent::getFinalPrice($qty, $product));
    }
    
}