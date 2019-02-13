<?php

namespace Algolia\AlgoliaSearch\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\ResourceModel\Group\Collection as CustomerGroupCollection;
use Algolia\AlgoliaSearch\Factory\CatalogPermissionsFactory;

class CategoryPermissions implements ObserverInterface
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
        $transport = $observer->getData('categoryObject');
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $observer->getData('category');
        $storeId = $category->getStoreId();

        if (!$this->isCatalogPermissionsEnabled($storeId)) {
            return $this;
        }

        /** @var \Magento\CatalogPermissions\Model\Permission\Index $permissionsIndex */
        if ($permissionsIndex = $this->permissionsFactory->getPermissionsIndex()) {
            $permissions = [];

            $collection = $this->customerGroupCollection;
            foreach ($collection as $customerGroup) {
                $customerGroupId = $customerGroup->getCustomerGroupId();
                $restrictedIds = $permissionsIndex->getRestrictedCategoryIds($customerGroupId, null);

                $permissions['customer_group_' . $customerGroupId] = in_array($category->getId(), $restrictedIds) ? 0 : 1;
            }

            $transport->setData('catalog_permissions', $permissions);
        }

        return $this;
    }
}
