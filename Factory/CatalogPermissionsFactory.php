<?php

namespace Algolia\AlgoliaSearch\Factory;

use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;

class CatalogPermissionsFactory
{
    private $moduleManager;
    private $objectManager;

    public function __construct(
        Manager $moduleManager,
        ObjectManagerInterface $objectManager
    ) {
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
    }

    public function isCatalogPermissionsModuleEnabled()
    {
        return $this->moduleManager->isEnabled('Magento_CatalogPermissions');
    }

    public function getPermissionsIndex()
    {
        return $this->isCatalogPermissionsModuleEnabled() ?
            $this->objectManager->create('\Magento\CatalogPermissions\Model\Permission\Index') : false;
    }

    public function getPermissionsHelper()
    {
        return $this->isCatalogPermissionsModuleEnabled() ?
            $this->objectManager->create('\Magento\CatalogPermissions\Helper\Data') : false;
    }
}
