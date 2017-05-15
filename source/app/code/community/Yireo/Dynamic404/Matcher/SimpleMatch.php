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
 * Matcher class to find matches in URL rewrites
 */
class Yireo_Dynamic404_Matcher_SimpleMatch extends Yireo_Dynamic404_Matcher_Generic
{
    /**
     * @var Mage_Core_Model_Abstract
     */
    protected $resource;

    /**
     * Yireo_Dynamic404_Matcher_SimpleMatch constructor
     *
     * @param array $data
     */
    public function __construct($data = [])
    {
        /** @var Mage_Core_Model_Resource $resource */
        $this->resource = Mage::getSingleton('core/resource');

        parent::__construct($data);
    }

    /**
     * @return string|bool
     */
    public function findBestMatch()
    {
        if ($productUrl = $this->matchRawProductUrl()) {
            return $productUrl;
        }

        return false;
    }

    /**
     * @return bool
     */
    private function matchRawProductUrl()
    {
        if (preg_match('/catalog\/product\/view\/id\/([0-9]+)/', $this->path, $match)) {

            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::getModel('catalog/product')->load($match[1]);

            if ($this->allowProductUrl($product)) {
                return $product->getProductUrl();
            }
        }

        return false;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return bool
     */
    private function allowProductUrl(Mage_Catalog_Model_Product $product)
    {
        if (!$product->isVisibleInCatalog()) {
            return false;
        }

        if ($product->isDisabled()) {
            return false;
        }

        return true;
    }
}