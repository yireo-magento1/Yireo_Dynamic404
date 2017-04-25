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
class Yireo_Dynamic404_Matcher_UrlRewrite implements Yireo_Dynamic404_Api_Matcher
{
    /**
     * @var Mage_Core_Model_Abstract
     */
    protected $resource;

    /**
     * Yireo_Dynamic404_Matcher_UrlRewrite constructor.
     */
    public function __construct()
    {
        /** @var Mage_Core_Model_Resource $resource */
        $this->resource = Mage::getSingleton('core/resource');
    }

    /**
     * @param array $parts
     *
     * @return string|bool
     */
    public function findBestMatch($parts, $urlSuffix)
    {
        $results = $this->getUrlRewriteMatches($parts, $urlSuffix);

        foreach ($results as $result) {
            $resultParts = explode('/', $result['request_path']);
            if (count($resultParts) === count($parts)) {
                return $result['request_path'];
            }
        }

        return false;
    }

    /**
     * @param array $parts
     * @param string $urlSuffix
     *
     * @return array
     */
    protected function getUrlRewriteMatches($parts, $urlSuffix)
    {
        /** @var Varien_Db_Adapter_Interface $db */
        $db = $this->resource->getConnection('core_read');
        $urlRewriteTable = $this->resource->getTableName('core_url_rewrite');

        $partsSearch = implode('%/', $parts).'%'.$urlSuffix;
        $selectFields = [$db->quoteIdentifier('request_path'), $db->quoteIdentifier('target_path')];
        $query = 'SELECT '.implode(',', $selectFields).' FROM ' . $db->quoteIdentifier($urlRewriteTable);
        $query .= ' WHERE '.$db->quoteIdentifier('request_path').' LIKE '.$db->quote($partsSearch);
        $query .= ' AND store_id = '. (int) Mage::app()->getStore()->getId();
        $query .= ' ORDER BY '.$db->quoteIdentifier('url_rewrite_id');
        $query .= ' LIMIT 0,100';

        //echo $query;exit;

        $results = $db->fetchAll($query);

        return $results;
    }
}