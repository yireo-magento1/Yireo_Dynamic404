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
class Yireo_Dynamic404_Matcher_UrlRewrite extends Yireo_Dynamic404_Matcher_Generic
{
    /**
     * @var Mage_Core_Model_Abstract
     */
    protected $resource;

    /**
     * Yireo_Dynamic404_Matcher_UrlRewrite constructor.
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
        $results = $this->getUrlRewriteMatches($this->parts, $this->urlSuffix);

        foreach ($results as $result) {
            $resultParts = explode('/', $result['request_path']);
            if (count($resultParts) === count($this->parts)) {
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

        $selectFields = [$db->quoteIdentifier('request_path'), $db->quoteIdentifier('target_path')];

        $whereSearch = [];
        $whereSearch[] = $db->quoteIdentifier('request_path').' LIKE '.$db->quote(implode('%/', $parts).'%'.$urlSuffix);
        $whereSearch[] = $db->quoteIdentifier('request_path').' LIKE '.$db->quote('%'.array_pop($parts).$urlSuffix);

        $query = 'SELECT '.implode(',', $selectFields).' FROM ' . $db->quoteIdentifier($urlRewriteTable);
        $query .= ' WHERE ('.implode(' OR ', $whereSearch).')';
        $query .= ' AND store_id = '. (int) Mage::app()->getStore()->getId();
        $query .= ' ORDER BY '.$db->quoteIdentifier('url_rewrite_id');
        $query .= ' LIMIT 0,100';

        $results = $db->fetchAll($query);

        return $results;
    }
}