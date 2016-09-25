<?php
class OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Product_Type_Simple
    extends Mage_Catalog_Model_Product_Type_Simple
{
    /**
     * Later this should be refactored to live elsewhere probably,
     * but it's ok here for the time being
     * @return int|false
     */
    private function _getCpid()
    {
        $cpid = $this->getProduct()->getCustomOption('cpid');
        if ($cpid) {
            return $cpid;
        }

        $br = $this->getProduct()->getCustomOption('info_buyRequest');
        if ($br) {
            $brData = unserialize($br->getValue());
            if (!empty($brData['cpid'])) {
                return $brData['cpid'];
            }
        }

        return false;
    }

    /**
     * Add the CPID to the product when preparing for cart
     * @param  Varien_Object              $buyRequest
     * @param  Mage_Catalog_Model_Product $product
     * @return array
     */
    public function prepareForCart(Varien_Object $buyRequest, $product = null)
    {
        $product = $this->getProduct($product);
        parent::prepareForCart($buyRequest, $product);
        if ($buyRequest->_getcpid()) {
            $product->addCustomOption('cpid', $buyRequest->_getcpid());
        }
        return array($product);
    }

    /**
     * Return whether or not the product has a CPID set
     * @return boolean
     */
    public function hasConfigurableProductParentId()
    {
        $cpid = $this->_getCpid();
        //Mage::log("cpid: ". $cpid);
        return !empty($cpid);
    }

    /**
     * Return the CPID
     * @return int|false
     */
    public function getConfigurableProductParentId()
    {
        return $this->_getCpid();
    }
}
