<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Controller\Adminhtml\Url\Rewrite\Import;

use Gtstudio\UrlRewriteImportExport\Model\File\ImportDirectory;
use Gtstudio\UrlRewriteImportExport\Model\Report as FileReport;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory as ResponseFileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Psr\Log\LoggerInterface;

/**
 * The controller to send a report file of async operation by its id
 */
class Report extends \Gtstudio\UrlRewriteImportExport\Controller\Adminhtml\Url\Rewrite
{
    /**
     * The class for working with a directory that contains imported/report files
     *
     * @var ImportDirectory
     */
    private $importDirectory;

    /**
     * The factory for creating a response file
     *
     * @var ResponseFileFactory
     */
    private $responseFileFactory;

    /**
     * The instance of logger
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context The constructor modification point for Magento\Backend\App\Action
     * @param ImportDirectory $importDirectory The class for working with a directory
     *        that contains imported/report files
     * @param ResponseFileFactory $responseFileFactory The factory for creating a response file
     * @param LoggerInterface $logger The instance of logger
     */
    public function __construct(
        Context $context,
        ImportDirectory $importDirectory,
        ResponseFileFactory $responseFileFactory,
        LoggerInterface $logger
    ) {
        $this->importDirectory = $importDirectory;
        $this->responseFileFactory = $responseFileFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Send a report file to download or redirect to main import page if the file does not exist
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $operationId = (int) $this->getRequest()->getParam('operation');

        $fileName = sprintf(FileReport::REPORT_FILENAME_TEMPLATE, $operationId);
        if ($this->importDirectory->isFileExist($fileName)) {
            try {
                return $this->responseFileFactory->create(
                    $fileName,
                    ['type' => 'filename', 'value' => $this->importDirectory->getFilePathByName($fileName)],
                    DirectoryList::VAR_DIR
                );
            } catch (\Exception $e) {
                $this->logger->error($e);
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        } else {
            $this->messageManager->addErrorMessage(
                __('Report file for operation %1 does not exist', $operationId)
            );
        }

        return $this->resultRedirectFactory->create()
            ->setPath('*/url_rewrite/import/');
    }
}
