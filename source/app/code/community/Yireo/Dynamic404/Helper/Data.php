<?php
/**
 * Google Dynamic404 for Magento
 *
 * @package     Yireo_Dynamic404
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

/**
 * General helper
 */
class Yireo_Dynamic404_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Switch to determine whether this extension is enabled or not
     *
     * @return bool
     */
    public function enabled()
    {
        if ((bool)$this->getStoreConfig('advanced/modules_disable_output/Yireo_Dynamic404', false)) {
            return false;
        }

        if ((bool)$this->getStoreConfig('enabled') === false) {
            return false;
        }

        return true;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public function getStoreConfig($value, $prefix = true)
    {
        if ($prefix) {
            $value = 'dynamic404/settings/' . $value;
        }

        return Mage::getStoreConfig($value);
    }

    /**
     * @param string $path
     * @return bool
     */
    public function log($path)
    {
        if ((bool)$this->getStoreConfig('debugging') === false) {
            return false;
        }

        $message = 'Unresolved 404 for '.$path;
        $fileName = 'yireo_dynamic404.log';
        Mage::log($message, Zend_Log::INFO, $fileName);
        return true;
    }
}
