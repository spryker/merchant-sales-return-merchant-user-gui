<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantSalesReturnMerchantUserGui\Communication\Reader;

use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\ReturnTransfer;
use Spryker\Zed\MerchantSalesReturnMerchantUserGui\Dependency\Facade\MerchantSalesReturnMerchantUserGuiToCustomerFacadeInterface;
use Spryker\Zed\MerchantSalesReturnMerchantUserGui\Dependency\Facade\MerchantSalesReturnMerchantUserGuiToSalesFacadeInterface;

class CustomerReader implements CustomerReaderInterface
{
    /**
     * @var \Spryker\Zed\MerchantSalesReturnMerchantUserGui\Dependency\Facade\MerchantSalesReturnMerchantUserGuiToCustomerFacadeInterface
     */
    protected $customerFacade;

    /**
     * @var \Spryker\Zed\MerchantSalesReturnMerchantUserGui\Dependency\Facade\MerchantSalesReturnMerchantUserGuiToSalesFacadeInterface
     */
    protected $salesFacade;

    public function __construct(
        MerchantSalesReturnMerchantUserGuiToSalesFacadeInterface $salesFacade,
        MerchantSalesReturnMerchantUserGuiToCustomerFacadeInterface $customerFacade
    ) {
        $this->salesFacade = $salesFacade;
        $this->customerFacade = $customerFacade;
    }

    public function findCustomerByReturn(ReturnTransfer $returnTransfer): ?CustomerTransfer
    {
        $customerReference = $returnTransfer->getCustomerReference();

        if (!$customerReference) {
            return $this->findCustomerByReturnOrder($returnTransfer);
        }

        $customerTransfer = $this->customerFacade
            ->findCustomerByReference($customerReference)
            ->getCustomerTransfer();

        if (!$customerTransfer) {
            return $this->findCustomerByReturnOrder($returnTransfer);
        }

        return $customerTransfer;
    }

    protected function findCustomerByReturnOrder(ReturnTransfer $returnTransfer): ?CustomerTransfer
    {
        $idSalesOrder = $returnTransfer->getReturnItems()
            ->getIterator()
            ->current()
            ->getOrderItem()
            ->getFkSalesOrder();

        $orderTransfer = $this->salesFacade->findOrderByIdSalesOrder($idSalesOrder);

        if (!$orderTransfer) {
            return null;
        }

        return (new CustomerTransfer())
            ->setEmail($orderTransfer->getEmail())
            ->setFirstName($orderTransfer->getFirstName())
            ->setLastName($orderTransfer->getLastName());
    }
}
