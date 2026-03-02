<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantSalesReturnMerchantUserGui\Communication\Reader;

use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\ReturnTransfer;

interface CustomerReaderInterface
{
    public function findCustomerByReturn(ReturnTransfer $returnTransfer): ?CustomerTransfer;
}
