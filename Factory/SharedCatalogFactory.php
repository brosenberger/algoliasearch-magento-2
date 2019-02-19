<?php

namespace Algolia\AlgoliaSearch\Factory;

use Magento\Catalog\Model\Product;
use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SharedCatalogFactory
{
    const SHARED_CATALOG_ENABLED_CONFIG_PATH = 'btob/website_configuration/sharedcatalog_active';

    private $scopeConfig;
    private $moduleManager;
    private $objectManager;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Manager $moduleManager,
        ObjectManagerInterface $objectManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
    }

    public function isSharedCatalogEnabled($storeId)
    {
        return $this->scopeConfig->isSetFlag(
            self::SHARED_CATALOG_ENABLED_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isSharedCatalogModuleEnabled()
    {
        return $this->moduleManager->isEnabled('Magento_SharedCatalog');
    }

    public function getSharedCatalogProductItemResource()
    {
        return $this->isSharedCatalogModuleEnabled() ?
            $this->objectManager->create('\Magento\SharedCatalog\Model\ResourceModel\ProductItem') : false;
    }

    public function isInSharedCatalogForCustomerGroup(Product $product, $customerGroupId)
    {
        /** @var \Magento\SharedCatalog\Model\ResourceModel\ProductItem $resourceModel */
        $resourceModel = $this->getSharedCatalogProductItemResource();
        $connection = $resourceModel->getConnection();

        $select = $connection->select()->from(
            ['shared_catalog_product' => $resourceModel->getMainTable()]
        )->where(
            'customer_group_id = ?',
            $customerGroupId
        )->where(
            'sku = ?',
            $product->getSku()
        );

        $shared = $connection->fetchRow($select);
        if ($shared) {
            return true;
        }

        return false;
    }

}
