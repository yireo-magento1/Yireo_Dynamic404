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
 * Matcher class to find matches in category URL keys
 */
class Yireo_Dynamic404_Matcher_Category extends Yireo_Dynamic404_Matcher_Generic
{
    /**
     * @var Mage_Catalog_Model_Category
     */
    protected $category;

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
        $this->category = Mage::getModel('catalog/category');
        $this->store = Mage::app()->getStore();

        parent::__construct($data);
    }

    /**
     * @return string|bool
     */
    public function findBestMatch()
    {
        $lastPart = array_pop($this->parts);
        $category = $this->getCategoryByUrlKey($lastPart);

        if ($category instanceof Mage_Catalog_Model_Category && $category->getId() > 0) {
            $category->load($category->getId());
            return $category->getUrl();
        }

        return false;
    }

    /**
     * @param string $urlKey
     *
     * @return false|Mage_Catalog_Model_Category
     */
    private function getCategoryByUrlKey($urlKey)
    {
        foreach ($this->getStoreIds() as $storeId) {
            /** @var Mage_Catalog_Model_Resource_Category_Collection $categoryCollection */
            $categoryCollection = $this->category
                ->setStoreId($storeId)
                ->getCollection();

            /** @var Mage_Catalog_Model_Category $category */
            $category = $categoryCollection->addAttributeToFilter('url_key', $urlKey)->getFirstItem();

            if ($category->getId() > 0) {
                return $category;
            }
        }

        return false;
    }

    /**
     * @return int[]
     */
    private function getStoreIds()
    {
        $storeIds = [];
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