<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Block\Adminhtml\Url\Rewrite;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * BackButton in the UI
 */
class BackButton implements ButtonProviderInterface
{
    /**
     * Constructor modification point for Magento\Backend\Block\Widget
     * @see \Magento\Backend\Block\Widget\Context
     *
     * @var Context
     */
    private $context;

    /**
     * @param Context $context Constructor modification point
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Get array with button configuration
     *
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
            'class' => 'back',
            'sort_order' => 10
        ];
    }

    /**
     * Get URL for back button
     *
     * @return string
     */
    public function getBackUrl(): string
    {
        return $this->context->getUrlBuilder()->getUrl('*/*/');
    }
}
