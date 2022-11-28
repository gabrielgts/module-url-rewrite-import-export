<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Controller\Adminhtml\Url\Rewrite\Import;

use Magento\Backend\App\Action\Context;
use Psr\Log\LoggerInterface;
use Gtstudio\UrlRewriteImportExport\Model\ScheduleBulk;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * The controller to run scheduling of import operations
 */
class Run extends \Gtstudio\UrlRewriteImportExport\Controller\Adminhtml\Url\Rewrite
{
    /**
     * The scheduler for import operations
     *
     * @var ScheduleBulk
     */
    private $scheduleBulk;

    /**
     * The instance of logger
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context The constructor modification point for Magento\Backend\App\Action
     * @param ScheduleBulk $scheduleBulk The scheduler for import operations
     * @param LoggerInterface $logger The instance of logger
     */
    public function __construct(
        Context $context,
        ScheduleBulk $scheduleBulk,
        LoggerInterface $logger
    ) {
        $this->scheduleBulk = $scheduleBulk;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Schedule import operations
     *
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        try {
            $importFile = $this->getRequest()->getParam('import_file')[0];
            $behavior = $this->getRequest()->getParam('behavior');
            $this->scheduleBulk->execute($importFile['file'], $behavior);
            $this->messageManager->addSuccessMessage(__('Operations of import was scheduled...'));
        } catch (\Exception $e) {
            $this->logger->error($e);
            $this->messageManager->addErrorMessage(
                __('Operations of import was not scheduled... More information in Magento logs.')
            );
        }

        return $this->resultRedirectFactory->create()
            ->setPath('*/url_rewrite/import/');
    }
}
