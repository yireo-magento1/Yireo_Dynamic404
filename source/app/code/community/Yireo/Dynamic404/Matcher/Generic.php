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
 * Generic matcher class
 */
abstract class Yireo_Dynamic404_Matcher_Generic implements Yireo_Dynamic404_Api_Matcher
{
    /**
     * @var string
     */
    protected $path = '';

    /**
     * @var array
     */
    protected $parts = [];

    /**
     * @var string
     */
    protected $urlSuffix = '';

    /**
     * Yireo_Dynamic404_Matcher_Product constructor
     *
     * @param array $data
     */
    public function __construct($data = [])
    {
        $this->path = $data['path'];
        $this->parts = $data['parts'];
        $this->urlSuffix = $data['urlSuffix'];
    }

    /**
     * @return string|bool
     */
    abstract public function findBestMatch();
}