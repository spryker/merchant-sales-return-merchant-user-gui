<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantSalesReturnMerchantUserGui\Communication\Reader;

use ArrayObject;
use Generated\Shared\Transfer\MerchantOrderItemCollectionTransfer;
use Generated\Shared\Transfer\MerchantOrderItemCriteriaTransfer;
use Spryker\Zed\MerchantSalesReturnMerchantUserGui\Dependency\Facade\MerchantSalesReturnMerchantUserGuiToMerchantOmsFacadeInterface;
use Spryker\Zed\MerchantSalesReturnMerchantUserGui\Dependency\Facade\MerchantSalesReturnMerchantUserGuiToMerchantSalesOrderFacadeInterface;

class MerchantOrderReader implements MerchantOrderReaderInterface
{
    /**
     * @var \Spryker\Zed\MerchantSalesReturnMerchantUserGui\Dependency\Facade\MerchantSalesReturnMerchantUserGuiToMerchantSalesOrderFacadeInterface
     */
    protected $merchantSalesOrderFacade;

    /**
     * @var \Spryker\Zed\MerchantSalesReturnMerchantUserGui\Dependency\Facade\MerchantSalesReturnMerchantUserGuiToMerchantOmsFacadeInterface
     */
    protected $merchantOmsFacade;

    /**
     * @param \Spryker\Zed\MerchantSalesReturnMerchantUserGui\Dependency\Facade\MerchantSalesReturnMerchantUserGuiToMerchantSalesOrderFacadeInterface $merchantSalesOrderFacade
     * @param \Spryker\Zed\MerchantSalesReturnMerchantUserGui\Dependency\Facade\MerchantSalesReturnMerchantUserGuiToMerchantOmsFacadeInterface $merchantOmsFacade
     */
    public function __construct(
        MerchantSalesReturnMerchantUserGuiToMerchantSalesOrderFacadeInterface $merchantSalesOrderFacade,
        MerchantSalesReturnMerchantUserGuiToMerchantOmsFacadeInterface $merchantOmsFacade
    ) {
        $this->merchantSalesOrderFacade = $merchantSalesOrderFacade;
        $this->merchantOmsFacade = $merchantOmsFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\MerchantOrderItemCriteriaTransfer $merchantOrderItemCriteriaTransfer
     *
     * @return array<\Generated\Shared\Transfer\MerchantOrderItemTransfer>
     */
    public function getMerchantOrderItems(MerchantOrderItemCriteriaTransfer $merchantOrderItemCriteriaTransfer): array
    {
        $merchantOrderItemTransfers = $this->merchantSalesOrderFacade
            ->getMerchantOrderItemCollection($merchantOrderItemCriteriaTransfer);

        $merchantOrderItemTransfers = $this->merchantOmsFacade->expandMerchantOrderItemsWithManualEvents($merchantOrderItemTransfers);
        $merchantOrderItemTransfers = $this->expandMerchantOrderItemsStateHistory($merchantOrderItemTransfers);
        $indexedMerchantOrderItemTransfers = $this->getMerchantOrderItemsIndexedByIdOrderItem($merchantOrderItemTransfers);

        return $indexedMerchantOrderItemTransfers;
    }

    /**
     * @param \Generated\Shared\Transfer\MerchantOrderItemCollectionTransfer $merchantOrderItemTransfers
     *
     * @return \Generated\Shared\Transfer\MerchantOrderItemCollectionTransfer
     */
    protected function expandMerchantOrderItemsStateHistory(
        MerchantOrderItemCollectionTransfer $merchantOrderItemTransfers
    ): MerchantOrderItemCollectionTransfer {
        $merchantOrderItemIds = [];

        foreach ($merchantOrderItemTransfers->getMerchantOrderItems() as $merchantOrderItemTransfer) {
            $merchantOrderItemIds[] = $merchantOrderItemTransfer->getIdMerchantOrderItemOrFail();
        }

        $stateMachineItemTransfers = $this->merchantOmsFacade
            ->getMerchantOrderItemsStateHistory($merchantOrderItemIds);

        foreach ($merchantOrderItemTransfers->getMerchantOrderItems() as $merchantOrderItemTransfer) {
            if (!isset($stateMachineItemTransfers[$merchantOrderItemTransfer->getIdMerchantOrderItem()])) {
                continue;
            }

            $stateHistory = $stateMachineItemTransfers[$merchantOrderItemTransfer->getIdMerchantOrderItem()];
            $merchantOrderItemTransfer->setStateHistory(new ArrayObject($stateHistory));

            /** @var \Generated\Shared\Transfer\StateMachineItemTransfer $currentState */
            $currentState = reset($stateHistory);

            $merchantOrderItemTransfer->setState($currentState->getStateName());
        }

        return $merchantOrderItemTransfers;
    }

    /**
     * @param \Generated\Shared\Transfer\MerchantOrderItemCollectionTransfer $merchantOrderItemTransfers
     *
     * @return array <int, \Generated\Shared\Transfer\MerchantOrderItemTransfer>
     */
    protected function getMerchantOrderItemsIndexedByIdOrderItem(
        MerchantOrderItemCollectionTransfer $merchantOrderItemTransfers
    ): array {
        $indexedMerchantOrderItemTransfers = [];
        foreach ($merchantOrderItemTransfers->getMerchantOrderItems() as $merchantOrderItemTransfer) {
            $indexedMerchantOrderItemTransfers[$merchantOrderItemTransfer->getIdOrderItemOrFail()] = $merchantOrderItemTransfer;
        }

        return $indexedMerchantOrderItemTransfers;
    }
}
