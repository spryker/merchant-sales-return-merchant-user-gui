<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantSalesReturnMerchantUserGui\Communication\Form;

use Spryker\Service\UtilText\Model\Url\Url;
use Spryker\Zed\Kernel\Communication\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @method \Spryker\Zed\MerchantSalesReturnMerchantUserGui\MerchantSalesReturnMerchantUserGuiConfig getConfig()
 * @method \Spryker\Zed\MerchantSalesReturnMerchantUserGui\Communication\MerchantSalesReturnMerchantUserGuiCommunicationFactory getFactory()
 */
class EventItemTriggerForm extends AbstractType
{
    /**
     * @var string
     */
    protected const OPTION_EVENT = 'OPTION_EVENT';

    /**
     * @var string
     */
    protected const OPTION_SUBMIT_BUTTON_CLASS = 'OPTION_SUBMIT_BUTTON_CLASS';

    /**
     * @var string
     */
    protected const OPTION_ACTION_QUERY_PARAMETERS = 'OPTION_ACTION_QUERY_PARAMETERS';

    /**
     * @var string
     */
    protected const BUTTON_SUBMIT = 'submit';

    /**
     * @uses \Spryker\Zed\MerchantSalesReturnMerchantUserGui\Communication\Controller\OmsTriggerController::submitTriggerEventItemAction()
     *
     * @var string
     */
    protected const ACTION_ROUTE = '/merchant-sales-return-merchant-user-gui/oms-trigger/submit-trigger-event-item';

    /**
     * @phpstan-param \Symfony\Component\Form\FormBuilderInterface<mixed> $builder
     *
     * @param \Symfony\Component\Form\FormBuilderInterface|mixed[] $builder
     * @param array<string, mixed> $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addSubmitButtonField($builder, $options)
            ->setAction($builder, $options);
    }

    /**
     * @phpstan-param \Symfony\Component\Form\FormBuilderInterface<mixed> $builder
     *
     * @param \Symfony\Component\Form\FormBuilderInterface|mixed[] $builder
     * @param array<string, mixed> $options
     *
     * @return $this
     */
    protected function addSubmitButtonField(FormBuilderInterface $builder, array $options)
    {
        $fieldOptions = [
            'label' => $options[static::OPTION_EVENT],
        ];

        if ($options[static::OPTION_SUBMIT_BUTTON_CLASS]) {
            $fieldOptions['attr'] = [
                'class' => $options[static::OPTION_SUBMIT_BUTTON_CLASS],
            ];
        }

        $builder->add(static::BUTTON_SUBMIT, SubmitType::class, $fieldOptions);

        return $this;
    }

    /**
     * @phpstan-param \Symfony\Component\Form\FormBuilderInterface<mixed> $builder
     *
     * @param \Symfony\Component\Form\FormBuilderInterface|mixed[] $builder
     * @param array<string, mixed> $options
     *
     * @return $this
     */
    protected function setAction(FormBuilderInterface $builder, array $options)
    {
        $builder->setAction(
            $this->generateCreateActionUrl($options),
        );

        return $this;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return string
     */
    protected function generateCreateActionUrl(array $options): string
    {
        return Url::generate(static::ACTION_ROUTE, $options[static::OPTION_ACTION_QUERY_PARAMETERS]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            static::OPTION_EVENT => null,
            static::OPTION_SUBMIT_BUTTON_CLASS => null,
            static::OPTION_ACTION_QUERY_PARAMETERS => [],
        ]);
    }
}
