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
 * API interface for matchers
 */
interface Yireo_Dynamic404_Api_Matcher
{
    /**
     * @param array $parts
     * @param string $urlSuffix
     *
     * @return string
     */
    public function findBestMatch($parts, $urlSuffix);
}