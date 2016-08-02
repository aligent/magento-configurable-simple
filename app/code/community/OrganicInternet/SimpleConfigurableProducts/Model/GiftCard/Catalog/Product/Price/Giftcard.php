<?php
/**
 * Created by PhpStorm.
 * User: luke
 * Date: 31/10/13
 * Time: 3:30 PM
 */
class OrganicInternet_SimpleConfigurableProducts_Model_GiftCard_Catalog_Product_Price_Giftcard
    extends Enterprise_GiftCard_Model_Catalog_Product_Price_Giftcard
{

    /**
     * Retrieve the product's final price. This differs from the default functionality.
     * Since the product is a gift card, the desired price is the giftcard_amount. For some reason, the default functionality
     * adds the giftcard_amount to $product->getPrice(). This seems strange, as giftcards don't normally use getPrice().
     * Since getPrice() has been rewritten in \OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Product::getPrice to return the
     * lowest giftcard_amount (used for price from...), this getFinalPrice method needs to disregard the getPrice().
     * 
     * @param int                        $qty
     * @param Mage_Catalog_Model_Product $product
     *
     * @return float
     */
    public function getFinalPrice($qty = null, $product)
    {
        $finalPrice = 0;
        if ($product->hasCustomOptions()) {
            $customOption = $product->getCustomOption('giftcard_amount');
            if ($customOption) {
                $finalPrice = $customOption->getValue();
            }
            $finalPrice = $this->_applyOptionsPrice($product, $qty, $finalPrice);
        }

        $product->setData('final_price', $finalPrice);

        return max(0, $product->getData('final_price'));
    }

}