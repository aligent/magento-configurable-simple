<?php
class OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Product
    extends Mage_Catalog_Model_Product
{
    public function getMaxPossibleFinalPrice()
    {
        $priceModel = $this->getPriceModel();
        if(is_callable(array($priceModel, 'getMaxPossibleFinalPrice'))) {
            return $priceModel->getMaxPossibleFinalPrice($this);
        } elseif (is_callable(array($priceModel, 'getMaxAmount'))) {
            return $priceModel->getMaxAmount($this);
        } else {
            #return $this->_getData('minimal_price');
            return parent::getMaxPrice();
        }
    }

    /**
     * Returns the minimum giftcard_amount, rather that the product's getPrice().
     * This is because giftcards don't have a base price set, and we need to return something useful to display "Price from..." etc.
     * 
     * @return float
     */
    public function getPrice() {
        $price = parent::getPrice();
        if (Enterprise_GiftCard_Model_Catalog_Product_Type_Giftcard::TYPE_GIFTCARD === $this->getTypeId()
            && is_callable(array($priceModel = $this->getPriceModel(), 'getMinAmount'))) {
            $price = max($priceModel->getMinAmount($this), $price);
        }
        return $price;
    }

    public function isVisibleInSiteVisibility()
    {
        #Force visible any simple products which have a parent conf product.
        #this will only apply to products which have been added to the cart
        if(is_callable(array($this->getTypeInstance(), 'hasConfigurableProductParentId'))
            && $this->getTypeInstance()->hasConfigurableProductParentId()) {
           return true;
        } else {
            return parent::isVisibleInSiteVisibility();
        }
    }


    public function getProductUrl($useSid = null)
    {
        if(is_callable(array($this->getTypeInstance(), 'hasConfigurableProductParentId'))
            && $this->getTypeInstance()->hasConfigurableProductParentId()) {

            $confProdId = $this->getTypeInstance()->getConfigurableProductParentId();
            return Mage::getModel('catalog/product')->load($confProdId)->getProductUrl();

        } else {
            return parent::getProductUrl($useSid);
        }
    }
}
