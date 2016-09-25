<?php
class OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Product
    extends Mage_Catalog_Model_Product
{
    /**
     * Get the maximum possible final price
     * @return mixed
     */
    public function getMaxPossibleFinalPrice()
    {
        if (is_callable(array($this->getPriceModel(), 'getMaxPossibleFinalPrice'))) {
            return $this->getPriceModel()->getMaxPossibleFinalPrice($this);
        } else {
            // return $this->_getData('minimal_price');
            return parent::getMaxPrice();
        }
    }

    /**
     * Return whether the product is visible in the site, factoring in its configurable parent
     * @return boolean
     */
    public function isVisibleInSiteVisibility()
    {
        // Force visible any simple products which have a parent conf product.
        // this will only apply to products which have been added to the cart
        if ((is_callable(array($this->getTypeInstance(), 'hasConfigurableProductParentId')))
            && ($this->getTypeInstance()->hasConfigurableProductParentId())
        ) {
            return true;
        }
        
        return parent::isVisibleInSiteVisibility();
    }

    /**
     * Get the product's URL, factoring in its configurable parent
     * @param  bool $useSid
     * @return string
     */
    public function getProductUrl($useSid = null)
    {
        if ((is_callable(array($this->getTypeInstance(), 'hasConfigurableProductParentId')))
            && ($this->getTypeInstance()->hasConfigurableProductParentId())
        ) {
            $confProdId = $this->getTypeInstance()->getConfigurableProductParentId();
            return Mage::getModel('catalog/product')->load($confProdId)->getProductUrl();
        }
        
        return parent::getProductUrl($useSid);
    }
}
