# Yireo Dynamic404
Magento extension for redirecting 404s automatically

More information: @todo

## Installation
You can install this module in various ways:

2) Download the Magento source archive from our site, extract the files and upload the
files to your Magento root. Make sure to flush the Magento cache. Make sure to logout 
once you're done.

3) Use modman to install the git repository for you:

    modman init
    modman clone https://github.com/yireo/Yireo_Dynamic404
    modman update Yireo_Dynamic404

4) Using composer

## Instructions for using composer

Use composer to install this extension. Before you can do this under Magento 1, you need to install the composer installer first:

    composer require magento-hackathon/magento-composer-installer

Make sure to set the `magento-root-dir` properly. Test this by running:

    composer update --no-dev

Once the composer installer is correctly setup, you can install our extension:    

    composer require yireo/yireo_dynamic404

Done.

Bring your towel.
