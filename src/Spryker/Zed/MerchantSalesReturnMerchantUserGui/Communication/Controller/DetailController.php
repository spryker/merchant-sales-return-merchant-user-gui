<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantSalesReturnMerchantUserGui\Communication\Controller;

use Generated\Shared\Transfer\MerchantOrderItemCriteriaTransfer;
use Generated\Shared\Transfer\ReturnFilterTransfer;
use Generated\Shared\Transfer\ReturnTransfer;
use Spryker\Service\UtilText\Model\Url\Url;
use Spryker\Zed\Kernel\Communication\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Spryker\Zed\MerchantSalesReturnMerchantUserGui\Communication\MerchantSalesReturnMerchantUserGuiCommunicationFactory getFactory()
 */
class DetailController extends AbstractController
{
    /**
     * @var string
     */
    protected const PARAM_ID_RETURN = 'id-return';

    /**
     * @uses \Spryker\Zed\MerchantSalesReturnMerchantUserGui\Communication\Controller\IndexController::indexAction()
     * @var string
     */
    protected const ROUTE_RETURN_LIST = '/merchant-sales-return-merchant-user-gui';

    /**
     * @var string
     */
    protected const MESSAGE_RETURN_NOT_FOUND_ERROR = 'Requested return with ID %id% was not found.';
    /**
     * @var string
     */
    protected const MESSAGE_PARAM_ID = '%id%';
    /**
     * @var string
     */
    protected const DEFAULT_LABEL_CLASS = 'label-default';
    /**
     * @var string
     */
    protected const MESSAGE_MERCHANT_NOT_FOUND_ERROR = 'Merchant for current user not found.';

    /**
     * @phpstan-return \Symfony\Component\HttpFoundation\RedirectResponse|array<mixed>
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     */
    public function indexAction(Request $request)
    {
        $idMerchant = $this->getFactory()->getMerchantUserFacade()->getCurrentMerchantUser()->getIdMerchant();

        if (!$idMerchant) {
            return $this->redirectToReturnList(static::MESSAGE_MERCHANT_NOT_FOUND_ERROR);
        }

        $idSalesReturn = $this->castId($request->get(static::PARAM_ID_RETURN));
        $returnTransfer = $this->findReturn($request);

        if (!$returnTransfer) {
            return $this->redirectToReturnList(static::MESSAGE_RETURN_NOT_FOUND_ERROR, [
                static::MESSAGE_PARAM_ID => $idSalesReturn,
            ]);
        }

        $salesOrderItemIds = $this->extractSalesOrderItemIdsFromReturn($returnTransfer);
        $merchantOrderItemTransfers = $this->getMerchantOrderItems($salesOrderItemIds);

        return [
            'return' => $returnTransfer,
            'customer' => $this->getFactory()->createCustomerReader()->findCustomerByReturn($returnTransfer),
            'uniqueOrderReferences' => $this->extractUniqueOrderReferencesFromReturn($returnTransfer),
            'uniqueItemStateLabels' => $this->extractUniqueItemStateLabelsFromReturn($merchantOrderItemTransfers),
            'uniqueOrderItemManualEvents' => $this->extractUniqueOrderItemManualEvents($merchantOrderItemTransfers),
            'merchantOrderItems' => $merchantOrderItemTransfers,
        ];
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Generated\Shared\Transfer\ReturnTransfer|null
     */
    protected function findReturn(Request $request): ?ReturnTransfer
    {
        $idSalesReturn = $this->castId(
            $request->get(static::PARAM_ID_RETURN)
        );

        return $this->getFactory()
            ->getSalesReturnFacade()
            ->getReturns((new ReturnFilterTransfer())->addIdReturn($idSalesReturn))
            ->getReturns()
            ->getIterator()
            ->current();
    }

    /**
     * @param array<int> $salesOrderItemIds
     *
     * @return array<\Generated\Shared\Transfer\MerchantOrderItemTransfer>
     */
    protected function getMerchantOrderItems(array $salesOrderItemIds): array
    {
        $merchantOrderItemCriteriaTransfer = (new MerchantOrderItemCriteriaTransfer())
            ->setOrderItemIds($salesOrderItemIds);

        return $this
            ->getFactory()
            ->createMerchantOrderReader()
            ->getMerchantOrderItems($merchantOrderItemCriteriaTransfer);
    }

    /**
     * @phpstan-return array<int, string>
     *
     * @param \Generated\Shared\Transfer\ReturnTransfer $returnTransfer
     *
     * @return array<string>
     */
    protected function extractUniqueOrderReferencesFromReturn(ReturnTransfer $returnTransfer): array
    {
        $uniqueOrderReferences = [];

        foreach ($returnTransfer->getReturnItems() as $returnItemTransfer) {
            $orderItemTransfer = $returnItemTransfer->getOrderItemOrFail();
            $idSalesOrder = $orderItemTransfer->getFkSalesOrderOrFail();
            $orderReference = $orderItemTransfer->getOrderReferenceOrFail();

            $uniqueOrderReferences[$idSalesOrder] = $orderReference;
        }

        return $uniqueOrderReferences;
    }

    /**
     * @phpstan-return array<string, string>
     *
     * @param array<\Generated\Shared\Transfer\MerchantOrderItemTransfer> $merchantOrderItemTransfers
     *
     * @return array<string>
     */
    protected function extractUniqueItemStateLabelsFromReturn(array $merchantOrderItemTransfers): array
    {
        $uniqueItemStates = [];

        foreach ($merchantOrderItemTransfers as $merchantOrderItemTransfer) {
            $state = $merchantOrderItemTransfer->getState();

            $uniqueItemStates[$state] = $this
                    ->getFactory()
                    ->getConfig()
                    ->getItemStateToLabelClassMapping()[$state] ?? static::DEFAULT_LABEL_CLASS;
        }

        return $uniqueItemStates;
    }

    /**
     * @phpstan-return array<int, string>
     *
     * @param array<\Generated\Shared\Transfer\MerchantOrderItemTransfer> $merchantOrderItemTransfers
     *
     * @return array<string>
     */
    protected function extractUniqueOrderItemManualEvents(array $merchantOrderItemTransfers): array
    {
        $allOrderItemManualEvents = [];

        foreach ($merchantOrderItemTransfers as $merchantOrderItem) {
            $allOrderItemManualEvents = array_merge($allOrderItemManualEvents, $merchantOrderItem->getManualEvents());
        }

        return array_unique($allOrderItemManualEvents);
    }

    /**
     * @phpstan-return array<int, int>
     *
     * @param \Generated\Shared\Transfer\ReturnTransfer $returnTransfer
     *
     * @return array<int>
     */
    protected function extractSalesOrderItemIdsFromReturn(ReturnTransfer $returnTransfer): array
    {
        $salesOrderItemIds = [];

        foreach ($returnTransfer->getReturnItems() as $returnItemTransfer) {
            $salesOrderItemIds[] = $returnItemTransfer->getOrderItemOrFail()->getIdSalesOrderItemOrFail();
        }

        return $salesOrderItemIds;
    }

    /**
     * @param string $message
     * @param array<mixed> $data
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectToReturnList(string $message, array $data = []): RedirectResponse
    {
        $this->addErrorMessage($message, $data);
        $redirectUrl = Url::generate(static::ROUTE_RETURN_LIST)->build();

        return $this->redirectResponse($redirectUrl);
    }
}
