<?php

namespace Algolia\AlgoliaSearch\Model\Observer\CatalogPermissions;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\ResourceModel\Group\Collection as CustomerGroupCollection;
use Algolia\AlgoliaSearch\Factory\CatalogPermissionsFactory;

class CategoryPermissions implements ObserverInterface
{
    private $permissionsFactory;
    private $customerGroupCollection;

    public function __construct(
        CustomerGroupCollection $customerGroupCollection,
        CatalogPermissionsFactory $permissionsFactory
    ) {
        $this->customerGroupCollection = $customerGroupCollection;
        $this->permissionsFactory = $permissionsFactory;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\DataObject $transport */
        $transport = $observer->getData('categoryObject');
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $observer->getData('category');
        $storeId = $category->getStoreId();

        if (!$this->permissionsFactory->isCatalogPermissionsEnabled($storeId)) {
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
