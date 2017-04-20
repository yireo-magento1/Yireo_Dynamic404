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
 * FixUrl Observer model
 */
class Yireo_Dynamic404_Observer_FixUrl
{
    /**
     * @var Mage_Core_Controller_Request_Http
     */
    private $request;

    /**
     * @var Zend_Controller_Response_Http
     */
    private $response;

    /**
     * @var string
     */
    private $urlSuffix = '';

    /**
     * Yireo_Dynamic404_Observer_FixUrl constructor.
     */
    public function __construct()
    {
        $this->request = Mage::app()->getRequest();
        $this->response = Mage::app()->getResponse();
    }

    /**
     * Attempt to fix broken URLs
     *
     * @param Varien_Event_Observer $observer
     * @return Yireo_Dynamic404_Observer_FixUrl
     */
    public function execute(Varien_Event_Observer $observer)
    {
        if ($this->isAllowedAction() === false) {
            return $this;
        }

        $pathParts = $this->getPathParts();
        $result = $this->findBestMatch($pathParts);

        //echo $result; exit;

        if (empty($result)) {
            return $this;
        }

        $this->response->clearHeaders();
        $this->response->setRedirect(Mage::getBaseUrl().$result, 301);
    }

    /**
     * @param $parts
     *
     * @return mixed
     */
    protected function findBestMatch($parts)
    {
        $results = $this->getUrlRewriteMatches($parts);

        foreach ($results as $result) {
            $resultParts = explode('/', $result['request_path']);
            if (count($resultParts) === count($parts)) {
                return $result['request_path'];
            }
        }

        return false;
    }

    /**
     * @param $parts
     *
     * @return mixed
     */
    protected function getUrlRewriteMatches($parts)
    {
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');

        /** @var Varien_Db_Adapter_Interface $db */
        $db = $resource->getConnection('core_read');
        $urlRewriteTable = $resource->getTableName('core_url_rewrite');

        $partsSearch = implode('%/', $parts).'%'.$this->urlSuffix;
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

    /**
     * @return array
     */
    protected function getPathParts()
    {
        $path = $this->request->getPathInfo();
        $path = preg_replace('/^\//', '', $path);

        $pathSuffix = false;
        if (preg_match('/\.html$/', $path, $pathSuffix)) {
            $path = preg_replace('/\.html$/', '', $path);
            $this->urlSuffix = $pathSuffix[0];
        }

        $pathParts = explode('/', $path);

        foreach ($pathParts as $index => $pathPart) {
            $pathParts[$index] = preg_replace('/-([0-9]+)$/', '', $pathPart);
        }

        return $pathParts;
    }

    /**
     * @return bool
     */
    protected function isAllowedAction()
    {
        if ($this->request->getActionName() === 'noRoute') {
            return true;
        }

        return false;
    }
}
