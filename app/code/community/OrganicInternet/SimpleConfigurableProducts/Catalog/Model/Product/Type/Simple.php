<?php
class OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Product_Type_Simple
    extends Mage_Catalog_Model_Product_Type_Simple
{
    /**
     * Later this should be refactored to live elsewhere probably,
     * but it's ok here for the time being
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

    public function prepareForCart(Varien_Object $buyRequest, $product = null)
    {
        $product = $this->getProduct($product);
        parent::prepareForCart($buyRequest, $product);
        if ($buyRequest->_getcpid()) {
            $product->addCustomOption('cpid', $buyRequest->_getcpid());
        }
        return array($product);
    }

    public function hasConfigurableProductParentId()
    {
        $cpid = $this->_getCpid();
        //Mage::log("cpid: ". $cpid);
        return !empty($cpid);
    }

    public function getConfigurableProductParentId()
    {
        return $this->_getCpid();
    }
}
