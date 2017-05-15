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
     * @var Yireo_Dynamic404_Helper_Data
     */
    private $helper;

    /**
     * @var Mage_Core_Helper_Url
     */
    private $coreUrlHelper;

    /**
     * @var Yireo_Dynamic404_Factory_Matcher
     */
    private $matcherFactory;

    /**
     * Yireo_Dynamic404_Observer_FixUrl constructor.
     */
    public function __construct()
    {
        $this->request = Mage::app()->getRequest();
        $this->response = Mage::app()->getResponse();
        $this->helper = Mage::helper('dynamic404');
        $this->coreUrlHelper = Mage::helper('core/url');
        $this->matcherFactory = new Yireo_Dynamic404_Factory_Matcher;
    }

    /**
     * Attempt to fix broken URLs
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Yireo_Dynamic404_Observer_FixUrl
     */
    public function execute(Varien_Event_Observer $observer)
    {
        if ($this->isAllowedAction() === false) {
            return $this;
        }

        $path = $this->getPathInfo();
        $urlSuffix = $this->stripUrlSuffixFromPath($path);
        $pathParts = $this->getPathParts($path);

        $data = [
            'path' => $path,
            'parts' => $pathParts,
            'urlSuffix' => $urlSuffix,
        ];

        $matchers = $this->matcherFactory->getMatchers($data);
        foreach ($matchers as $matcher) {
            $result = $matcher->findBestMatch();

            if (empty($result)) {
                continue;
            }

            if (!preg_match('/^(http|https):\/\//', $result)) {
                $result = Mage::getBaseUrl() . $result;
            }

            if ($result === $this->coreUrlHelper->getCurrentUrl()) {
                continue;
            }

            break;
        }

        if (empty($result)) {
            $this->helper->log($this->request->getHttpHost() . $this->request->getRequestUri());
            return $this->forwardToSearch();
        }

        $queryParts = $this->request->getQuery();
        $query = http_build_query($queryParts);
        if (!empty($query)) {
            $result .= '?' . $query;
        }

        $this->response->clearHeaders();
        $this->response->setRedirect($result, 301);
    }

    /**
     * @return Yireo_Dynamic404_Observer_FixUrl
     */
    protected function forwardToSearch()
    {
        return $this;
    }

    /**
     * @return string
     */
    protected function getPathInfo()
    {
        $path = $this->request->getOriginalPathInfo();
        $path = preg_replace('/^\//', '', $path);
        return $path;
    }

    /**
     * @param $path
     *
     * @return string
     */
    protected function stripUrlSuffixFromPath(&$path)
    {
        $pathSuffix = false;
        $urlSuffix = '';

        if (preg_match('/\.html$/', $path, $pathSuffix)) {
            $path = preg_replace('/\.html$/', '', $path);
            $urlSuffix = $pathSuffix[0];
        }

        return $urlSuffix;
    }

    /**
     * @param string $path
     *
     * @return array
     */
    protected function getPathParts($path)
    {
        $pathParts = explode('/', $path);

        foreach ($pathParts as $index => $pathPart) {
            $pathParts[$index] = preg_replace('/-([0-9]+)$/', '', $pathPart);
        }

        return $pathParts;
    }

    /**
     * @return bool
     * @throws RuntimeException
     */
    protected function isAllowedAction()
    {
        if ($this->helper->enabled() === false) {
            return false;
        }

        if ($this->request->getActionName() !== 'noRoute') {
            return false;
        }

        if ($this->isInvalidRequest() === true) {
            throw new RuntimeException('Invalid URL path');
        }

        if ($this->hasValidUrlExtension() === false) {
            throw new RuntimeException('Invalid URL extension');
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function isInvalidRequest()
    {
        $pathInfo = $this->getPathInfo();
        $invalidPaths = [
            'admin_html',
            'webmail',
            'typo3',
            'backup',
            'wordpress',
        ];

        foreach ($invalidPaths as $invalidPath) {
            if (stristr($pathInfo, $invalidPath.'/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function hasValidUrlExtension()
    {
        $ignoreExtensions = ['csv', 'doc', 'xml', 'log', 'php', 'sql', 'ini', 'gz', 'zip'];
        $pathInfo = $this->getPathInfo();

        foreach ($ignoreExtensions as $ignoreExtension) {
            if (preg_match('/'.$ignoreExtension.'$/', $pathInfo)) {
                return false;
            }
        }

        return true;
    }
}
