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
 * Factory class to generate matchers
 */
class Yireo_Dynamic404_Factory_Matcher
{
    /**
     * @var Mage_Core_Model_App
     */
    private $app;

    /**
     * Yireo_Dynamic404_Factory_Matcher constructor.
     */
    public function __construct()
    {
        $this->app = Mage::app();
    }

    /**
     * @param array $data
     *
     * @return Yireo_Dynamic404_Api_Matcher[]
     */
    public function getMatchers($data = [])
    {
        $matcherClasses = $this->getMatcherClasses();
        $matchers = [];

        foreach ($matcherClasses as $matcherClass) {
            $matchers[] = new $matcherClass($data);
        }

        return $matchers;
    }


    /**
     * @return array
     */
    protected function getMatcherClasses()
    {
        $matcherClasses = [
            Yireo_Dynamic404_Matcher_SimpleMatch::class,
            Yireo_Dynamic404_Matcher_UrlRewrite::class,
            Yireo_Dynamic404_Matcher_Product::class,
            Yireo_Dynamic404_Matcher_Category::class,
        ];

        $this->app->dispatchEvent('dynamic404_get_matcher_classes', $matcherClasses);

        return $matcherClasses;
    }
}