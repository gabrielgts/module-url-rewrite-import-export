<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Model;

use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Scheduler to schedule operations of import url rewrites
 */
class ScheduleBulk
{
    /**
     * The Bulk Manager instance
     *
     * @var BulkManagementInterface
     */
    private $bulkManagement;

    /**
     * The Operation Factory instance
     *
     * @var OperationInterfaceFactory
     */
    private $operationFactory;

    /**
     * The Identity Service instance
     *
     * @var IdentityGeneratorInterface
     */
    private $identityService;

    /**
     * The current user identification
     *
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * The factory to create the file model instance
     *
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * The serializer instance
     *
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param BulkManagementInterface $bulkManagement The Bulk Manager instance
     * @param OperationInterfaceFactory $operartionFactory The Operation Factory instance
     * @param IdentityGeneratorInterface $identityService The Identity Service instance
     * @param UserContextInterface $userContextInterface The current user identification
     * @param FileFactory $fileFactory The factory to create the file model instance
     * @param SerializerInterface $serializer The serializer instance
     */
    public function __construct(
        BulkManagementInterface $bulkManagement,
        OperationInterfaceFactory $operartionFactory,
        IdentityGeneratorInterface $identityService,
        UserContextInterface $userContextInterface,
        FileFactory $fileFactory,
        SerializerInterface $serializer
    ) {
        $this->userContext = $userContextInterface;
        $this->bulkManagement = $bulkManagement;
        $this->operationFactory = $operartionFactory;
        $this->identityService = $identityService;
        $this->fileFactory = $fileFactory;
        $this->serializer = $serializer;
    }

    /**
     * Schedule operations of import url rewrites
     *
     * @param string $fileName The file name with imported url rewrites
     * @param string $behavior The behavior name of import
     * @return void
     * @throws LocalizedException The exception that is thrown if something went wrong during schedule
     */
    public function execute(string $fileName, string $behavior)
    {
        /** @var File $objectFile */
        $objectFile = $this->fileFactory->create($fileName);
        $rowsCount = $objectFile->getRowsCount();

        if ($rowsCount > 0) {
            $iteration = ceil($rowsCount / Import::ROWS_PER_OPERATION);
            $bulkUuid = $this->identityService->generateId();
            $operations = [];

            while ($iteration > 0) {
                $iteration--;
                $offset = $iteration * Import::ROWS_PER_OPERATION;

                $serializedData = [
                    'file_name' => $fileName,
                    'offset' => $offset,
                    'length' => Import::ROWS_PER_OPERATION,
                    'behavior' => $behavior,
                    'meta_information' => 'The offset of rows: ' . $offset,
                ];
                $data = [
                    'data' => [
                        'bulk_uuid' => $bulkUuid,
                        'topic_name' => 'url.rewrite.import',
                        'serialized_data' => $this->serializer->serialize($serializedData),
                        'status' => OperationInterface::STATUS_TYPE_OPEN,
                    ]
                ];

                $operations[] = $this->operationFactory->create($data);
            }

            $result = $this->bulkManagement->scheduleBulk(
                $bulkUuid,
                $operations,
                'Import URL Rewrites with behavior ' . $behavior,
                $this->userContext->getUserId()
            );

            if (!$result) {
                throw new LocalizedException(
                    __('Something went wrong while processing the request.')
                );
            }
        }
    }
}
