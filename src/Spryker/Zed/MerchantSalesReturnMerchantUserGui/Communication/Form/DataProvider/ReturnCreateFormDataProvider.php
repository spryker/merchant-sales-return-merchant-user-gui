<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantSalesReturnMerchantUserGui\Communication\Form\DataProvider;

use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\ReturnItemTransfer;
use Generated\Shared\Transfer\ReturnReasonFilterTransfer;
use Spryker\Zed\MerchantSalesReturnMerchantUserGui\Dependency\Facade\MerchantSalesReturnMerchantUserGuiToGlossaryFacadeInterface;
use Spryker\Zed\MerchantSalesReturnMerchantUserGui\Dependency\Facade\MerchantSalesReturnMerchantUserGuiToSalesReturnFacadeInterface;

class ReturnCreateFormDataProvider
{
    /**
     * @var string
     */
    protected const CUSTOM_REASON = 'Custom reason';

    /**
     * @var string
     */
    protected const CUSTOM_REASON_KEY = 'custom_reason';

    /**
     * @uses \Spryker\Zed\MerchantSalesReturnMerchantUserGui\Communication\Form\ReturnCreateForm::FIELD_RETURN_ITEMS
     * @var string
     */
    protected const FIELD_RETURN_ITEMS = 'returnItems';

    /**
     * @uses \Spryker\Zed\MerchantSalesReturnMerchantUserGui\Communication\Form\ReturnCreateForm::OPTION_RETURN_REASONS
     * @var string
     */
    protected const OPTION_RETURN_REASONS = 'option_return_reasons';

    /**
     * @var \Spryker\Zed\MerchantSalesReturnMerchantUserGui\Dependency\Facade\MerchantSalesReturnMerchantUserGuiToSalesReturnFacadeInterface
     */
    protected $salesReturnFacade;

    /**
     * @var \Spryker\Zed\MerchantSalesReturnMerchantUserGui\Dependency\Facade\MerchantSalesReturnMerchantUserGuiToGlossaryFacadeInterface
     */
    protected $glossaryFacade;

    /**
     * @param \Spryker\Zed\MerchantSalesReturnMerchantUserGui\Dependency\Facade\MerchantSalesReturnMerchantUserGuiToSalesReturnFacadeInterface $salesReturnFacade
     * @param \Spryker\Zed\MerchantSalesReturnMerchantUserGui\Dependency\Facade\MerchantSalesReturnMerchantUserGuiToGlossaryFacadeInterface $glossaryFacade
     */
    public function __construct(
        MerchantSalesReturnMerchantUserGuiToSalesReturnFacadeInterface $salesReturnFacade,
        MerchantSalesReturnMerchantUserGuiToGlossaryFacadeInterface $glossaryFacade
    ) {
        $this->salesReturnFacade = $salesReturnFacade;
        $this->glossaryFacade = $glossaryFacade;
    }

    /**
     * @phpstan-return array<mixed>
     *
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return array
     */
    public function getData(OrderTransfer $orderTransfer): array
    {
        $orderTransfer = $this->translateReturnPolicyMessages($orderTransfer);

        return [
            static::FIELD_RETURN_ITEMS => $this->mapReturnItemTransfers($orderTransfer),
        ];
    }

    /**
     * @phpstan-return array<int|string, mixed>
     *
     * @return array
     */
    public function getOptions(): array
    {
        return [
            static::OPTION_RETURN_REASONS => $this->prepareReturnReasonChoices(),
        ];
    }

    /**
     * @phpstan-return array<int, array<string, \Generated\Shared\Transfer\ItemTransfer>>
     *
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return array
     */
    protected function mapReturnItemTransfers(OrderTransfer $orderTransfer): array
    {
        $returnItemTransfers = [];

        foreach ($orderTransfer->getItems() as $itemTransfer) {
            $returnItemTransfers[] = [ReturnItemTransfer::ORDER_ITEM => $itemTransfer];
        }

        return $returnItemTransfers;
    }

    /**
     * @phpstan-return array<int|string, mixed>
     *
     * @return array<string>
     */
    protected function prepareReturnReasonChoices(): array
    {
        $returnReasonChoices = [];
        $returnReasonTransfers = $this->salesReturnFacade->getReturnReasons(new ReturnReasonFilterTransfer())
            ->getReturnReasons();

        foreach ($returnReasonTransfers as $returnReasonTransfer) {
            $returnReason = $this->glossaryFacade->translate($returnReasonTransfer->getGlossaryKeyReasonOrFail());

            $returnReasonChoices[$returnReason] = $returnReason;
        }

        $returnReasonChoices[static::CUSTOM_REASON] = static::CUSTOM_REASON_KEY;

        return $returnReasonChoices;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\OrderTransfer
     */
    protected function translateReturnPolicyMessages(OrderTransfer $orderTransfer): OrderTransfer
    {
        foreach ($orderTransfer->getItems() as $itemTransfer) {
            $this->translateReturnPolicyMessage($itemTransfer);
        }

        return $orderTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     *
     * @return \Generated\Shared\Transfer\ItemTransfer
     */
    protected function translateReturnPolicyMessage(ItemTransfer $itemTransfer): ItemTransfer
    {
        if (!$itemTransfer->getReturnPolicyMessages()->count()) {
            return $itemTransfer;
        }

        foreach ($itemTransfer->getReturnPolicyMessages() as $returnPolicyMessage) {
            if (!$returnPolicyMessage->getValue()) {
                continue;
            }

            $translatedMessage = $this->glossaryFacade->translate(
                $returnPolicyMessage->getValueOrFail(),
                $returnPolicyMessage->getParameters()
            );

            $returnPolicyMessage->setMessage($translatedMessage);
        }

        return $itemTransfer;
    }
}
