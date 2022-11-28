<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Bulk\OperationManagementInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * The consumer to run import url rewrites
 */
class Consumer
{
    /**
     * The serializer instance
     *
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * The Entity Manager instance
     *
     * @var EntityManager
     */
    private $entityManager;

    /**
     * The class to import url rewrites
     *
     * @var Import
     */
    private $import;

    /**
     * The instance of logger
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * The Operation Management instance
     *
     * @var OperationManagementInterface
     */
    private $operationManagement;

    /**
     * @param Import $import The class to import url rewrites
     * @param SerializerInterface $serializer The serializer instance
     * @param EntityManager $entityManager The Entity Manager instance
     * @param OperationManagementInterface $operationManagement The Operation Management instance
     * @param LoggerInterface $logger The instance of logger
     */
    public function __construct(
        Import $import,
        SerializerInterface $serializer,
        EntityManager $entityManager,
        OperationManagementInterface $operationManagement,
        LoggerInterface $logger
    ) {
        $this->import = $import;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->operationManagement = $operationManagement;
        $this->logger = $logger;
    }

    /**
     * Run operations of import url rewrite from the operations list
     *
     * @param \Magento\AsynchronousOperations\Api\Data\OperationListInterface $operationList
     * @return void
     * @throws LocalizedException The exception that is thrown if there is problem during save operation
     */
    public function processOperations(\Magento\AsynchronousOperations\Api\Data\OperationListInterface $operationList)
    {
        foreach ($operationList->getItems() as $operation) {
            $errorCode = null;
            $status = OperationInterface::STATUS_TYPE_COMPLETE;
            $message = null;
            $data = $this->serializer->unserialize($operation->getSerializedData());

            // Save the operation in the DB
            try {
                $this->entityManager->save($operation);
            } catch (\Exception $e) {
                $this->logger->error($e);
                throw new LocalizedException(__('Cannot save an operation. More information in Magento logs.'), $e);
            }

            // Run import url rewrites
            try {
                $this->import->execute(
                    $operation->getId(),
                    $data['file_name'],
                    $data['offset'],
                    $data['length'],
                    $data['behavior']
                );
            } catch (LocalizedException $e) {
                $data['entity_link'] = 'adminhtml/url_rewrite_import/report/operation/' . $operation->getId();
                $message = $e->getMessage();
                $errorCode = $e->getCode();
                $status = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            }

            // Change status of operation
            $this->operationManagement->changeOperationStatus(
                $operation->getId(),
                $status,
                $errorCode,
                $message,
                $this->serializer->serialize($data)
            );
        }
    }
}
