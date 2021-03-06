<?php
/**
 * Dynamic404 plugin for Magento
 *
 * @package     Yireo_Dynamic404
 * @author      Yireo
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

/**
 * Matcher class to find matches in product URL keys
 */
class Yireo_Dynamic404_Matcher_Product extends Yireo_Dynamic404_Matcher_Generic
{
    /**
     * @var Mage_Catalog_Model_Product
     */
    protected $product;

    /**
     * @var Mage_Core_Model_Store
     */
    protected $store;

    /**
     * Yireo_Dynamic404_Matcher_Product constructor
     *
     * @param array $data
     */
    public function __construct($data = [])
    {
        $this->product = Mage::getModel('catalog/product');
        $this->store = Mage::app()->getStore();

        parent::__construct($data);
    }

    /**
     * @return string|bool
     */
    public function findBestMatch()
    {
        $lastPart = array_pop($this->parts);
        $product = $this->getProductByUrlKey($lastPart);

        if ($product instanceof Mage_Catalog_Model_Product && $product->getId() > 0) {
            $product->load($product->getId());
            return $product->getProductUrl();
        }

        return false;
    }

    /**
     * @param string $urlKey
     *
     * @return false|Mage_Catalog_Model_Product
     */
    private function getProductByUrlKey($urlKey)
    {
        foreach ($this->getStoreIds() as $storeId) {

            /** @var Mage_Catalog_Model_Resource_Product_Collection $productsCollection */
            $productsCollection = $this->product
                ->setStoreId($storeId)
                ->getCollection();

            /** @var Mage_Catalog_Model_Product $product */
            $products = $productsCollection
                ->addAttributeToFilter('url_key', $urlKey)
                ->addAttributeToFilter('status', 1)
                ->addAttributeToSelect(['visibility', 'url_path', 'url_key'])
            ;
            //echo $productsCollection->getSelect();

            foreach ($products as $product) {
                if ($product->isVisibleInSiteVisibility() === false) {
                    continue;
                }

                return $product;
            }
        }

        return false;
    }


    /**
     * @return int[]
     */
    private function getStoreIds()
    {
        $storeIds = [0];
        $storeIds[] = $this->store->getId();

        $stores = $this->store->getWebsite()->getStores();
        foreach ($stores as $store) {

            /** @var Mage_Core_Model_Store $store */
            if (in_array($store->getId(), $storeIds)) {
                continue;
            }

            $storeIds[] = $store->getId();
        }

        return $storeIds;
    }
}