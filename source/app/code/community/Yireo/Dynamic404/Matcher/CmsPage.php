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
class Yireo_Dynamic404_Matcher_CmsPage extends Yireo_Dynamic404_Matcher_Generic
{
    /**
     * @var Mage_Cms_Model_Page
     */
    protected $cmsPage;

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
        $this->cmsPage = Mage::getModel('cms/page');
        $this->store = Mage::app()->getStore();

        parent::__construct($data);
    }

    /**
     * @return string|bool
     */
    public function findBestMatch()
    {
        $lastPart = array_pop($this->parts);
        $cmsPage = $this->getCmsPageByUrlKey($lastPart);

        if ($cmsPage instanceof Mage_Cms_Model_Page && $cmsPage->getId() > 0) {
            return $cmsPage->getIdentifier();
        }

        return false;
    }

    /**
     * @param string $urlKey
     *
     * @return false|Mage_Cms_Model_Page
     */
    private function getCmsPageByUrlKey($urlKey)
    {
        foreach ($this->getStoreIds() as $storeId) {
            /** @var Mage_Cms_Model_Resource_Page_Collection $cmsPageCollection */
            $cmsPageCollection = $this->cmsPage
                ->setStoreId($storeId)
                ->getCollection();

            /** @var Mage_Cms_Model_Page $cmsPage */
            $cmsPage = $cmsPageCollection
                ->addFieldToSelect(['page_id', 'identifier'])
                ->addFieldToFilter('identifier', $urlKey)
                ->addFieldToFilter('is_active', 1)
                ->getFirstItem();

            if ($cmsPage->getId() > 0) {
                return $cmsPage;
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