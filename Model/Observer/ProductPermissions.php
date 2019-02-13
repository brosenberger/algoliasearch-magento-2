<?php

namespace Algolia\AlgoliaSearch\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\ResourceModel\Group\Collection as CustomerGroupCollection;
use Algolia\AlgoliaSearch\Factory\CatalogPermissionsFactory;

class ProductPermissions implements ObserverInterface
{
    const CATALOG_PERMISSIONS_ENABLED_CONFIG_PATH = 'catalog/magento_catalogpermissions/enabled';

    private $scopeConfig;
    private $permissionsFactory;
    private $customerGroupCollection;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CustomerGroupCollection $customerGroupCollection,
        CatalogPermissionsFactory $permissionsFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->customerGroupCollection = $customerGroupCollection;
        $this->permissionsFactory = $permissionsFactory;
    }

    public function isCatalogPermissionsEnabled($storeId)
    {
        return $this->scopeConfig->isSetFlag(
            self::CATALOG_PERMISSIONS_ENABLED_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\DataObject $transport */
        $transport = $observer->getData('custom_data');
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getData('productObject');
        $storeId = $product->getStoreId();

        if (!$this->isCatalogPermissionsEnabled($storeId)) {
            return $this;
        }

        /** @var \Magento\CatalogPermissions\Model\Permission\Index $permissionsIndex */
        if ($permissionsIndex = $this->permissionsFactory->getPermissionsIndex()) {
            $permissionsHelper = $this->permissionsFactory->getPermissionsHelper();

            $permissions = [];

            $collection = $this->customerGroupCollection;
            foreach ($collection as $customerGroup) {
                $customerGroupId = $customerGroup->getCustomerGroupId();
                $permissionsIndex->addIndexToProduct($product, $customerGroupId);

                $permissions['customer_group_' . $customerGroupId] = 1;

                if ($product->getData('grant_catalog_category_view') == -2
                    || $product->getData('grant_catalog_category_view') != -1
                    && !$permissionsHelper->isAllowedCategoryView($storeId, $customerGroupId)
                ) {
                    $permissions['customer_group_' . $customerGroupId] = 0;
                }
            }

            $transport->setData('catalog_permissions', $permissions);

        }

        return $this;
    }
}
