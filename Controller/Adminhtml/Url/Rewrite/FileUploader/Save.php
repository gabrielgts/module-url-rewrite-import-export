<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Controller\Adminhtml\Url\Rewrite\FileUploader;

use Magento\Backend\App\Action\Context;
use Gtstudio\UrlRewriteImportExport\Model\File\Uploader as FileUploader;
use Gtstudio\UrlRewriteImportExport\Model\File\UploaderFactory as FileUploaderFactory;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface;

/**
 * The controller to save uploaded file
 */
class Save extends \Gtstudio\UrlRewriteImportExport\Controller\Adminhtml\Url\Rewrite
{
    /**
     * The factory for creating file uploader object
     *
     * @var FileUploaderFactory
     */
    private $fileUploaderFactory;

    /**
     * The instance of logger
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context The constructor modification point
     * @param FileUploaderFactory $fileUploaderFactory The factory for creating file uploader object
     * @param LoggerInterface $logger The instance of logger
     */
    public function __construct(
        Context $context,
        FileUploaderFactory $fileUploaderFactory,
        LoggerInterface $logger
    ) {
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Upload file to the destination folder
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            /** @var FileUploader $fileUploader */
            $fileUploader = $this->fileUploaderFactory->create(['fileId' =>'import_file']);
            $result = $fileUploader->upload();
            unset($result['tmp_name'], $result['path']);
        } catch (\Exception $e) {
            $this->logger->error($e);
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
