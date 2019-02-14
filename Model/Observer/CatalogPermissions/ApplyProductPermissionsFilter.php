<?php

namespace Algolia\AlgoliaSearch\Model\Observer\CatalogPermissions;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Algolia\AlgoliaSearch\Factory\CatalogPermissionsFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Model\StoreManager;


class ApplyProductPermissionsFilter implements ObserverInterface
{
    private $permissionsFactory;
    private $customerSession;
    private $storeManager;

    public function __construct(
        CatalogPermissionsFactory $permissionsFactory,
        CustomerSession $customerSession,
        StoreManager $storeManager
    ) {
        $this->permissionsFactory = $permissionsFactory;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
    }

    public function execute(Observer $observer)
    {
        $storeId = $this->storeManager->getStore()->getId();
        if (!$this->permissionsFactory->isCatalogPermissionsEnabled($storeId)) {
            return $this;
        }

        /** @var \Magento\Framework\DataObject $transport */
        $attributes = $observer->getData('attributes');
        $customerGroupId = $this->customerSession->getCustomerGroupId();


        return $this;
    }
}
