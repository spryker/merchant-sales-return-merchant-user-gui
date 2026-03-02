<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantSalesReturnMerchantUserGui\Dependency\Facade;

use Generated\Shared\Transfer\ReturnCollectionTransfer;
use Generated\Shared\Transfer\ReturnCreateRequestTransfer;
use Generated\Shared\Transfer\ReturnFilterTransfer;
use Generated\Shared\Transfer\ReturnReasonCollectionTransfer;
use Generated\Shared\Transfer\ReturnReasonFilterTransfer;
use Generated\Shared\Transfer\ReturnResponseTransfer;

class MerchantSalesReturnMerchantUserGuiToSalesReturnFacadeBridge implements MerchantSalesReturnMerchantUserGuiToSalesReturnFacadeInterface
{
    /**
     * @var \Spryker\Zed\SalesReturn\Business\SalesReturnFacadeInterface
     */
    protected $salesReturnFacade;

    /**
     * @param \Spryker\Zed\SalesReturn\Business\SalesReturnFacadeInterface $salesReturnFacade
     */
    public function __construct($salesReturnFacade)
    {
        $this->salesReturnFacade = $salesReturnFacade;
    }

    public function getReturns(ReturnFilterTransfer $returnFilterTransfer): ReturnCollectionTransfer
    {
        return $this->salesReturnFacade->getReturns($returnFilterTransfer);
    }

    public function getReturnReasons(ReturnReasonFilterTransfer $returnReasonFilterTransfer): ReturnReasonCollectionTransfer
    {
        return $this->salesReturnFacade->getReturnReasons($returnReasonFilterTransfer);
    }

    public function createReturn(ReturnCreateRequestTransfer $returnCreateRequestTransfer): ReturnResponseTransfer
    {
        return $this->salesReturnFacade->createReturn($returnCreateRequestTransfer);
    }
}
