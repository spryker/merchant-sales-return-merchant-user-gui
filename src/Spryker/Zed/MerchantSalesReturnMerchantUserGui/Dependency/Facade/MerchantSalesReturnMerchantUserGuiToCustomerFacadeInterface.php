<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantSalesReturnMerchantUserGui\Dependency\Facade;

use Generated\Shared\Transfer\CustomerResponseTransfer;

interface MerchantSalesReturnMerchantUserGuiToCustomerFacadeInterface
{
    public function findCustomerByReference(string $customerReference): CustomerResponseTransfer;
}
