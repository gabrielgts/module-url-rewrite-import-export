<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Test\Unit\Model;

use Gtstudio\UrlRewriteImportExport\Model\ScheduleBulk;
use Gtstudio\UrlRewriteImportExport\Model\FileFactory;
use Gtstudio\UrlRewriteImportExport\Model\File;
use Gtstudio\UrlRewriteImportExport\Model\Import;
use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ScheduleBulkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ScheduleBulk
     */
    private $scheduleBulk;

    /**
     * @var BulkManagementInterface|MockObject
     */
    private $bulkManagementMock;

    /**
     * @var OperationInterfaceFactory|MockObject
     */
    private $operationFactoryMock;

    /**
     * @var IdentityGeneratorInterface|MockObject
     */
    private $identityServiceMock;

    /**
     * @var UserContextInterface|MockObject
     */
    private $userContextMock;

    /**
     * @var FileFactory|MockObject
     */
    private $fileFactoryMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->bulkManagementMock = $this->getMockForAbstractClass(BulkManagementInterface::class);
        $this->operationFactoryMock = $this->createMock(OperationInterfaceFactory::class);
        $this->identityServiceMock = $this->getMockForAbstractClass(IdentityGeneratorInterface::class);
        $this->userContextMock = $this->getMockForAbstractClass(UserContextInterface::class);
        $this->fileFactoryMock = $this->createMock(FileFactory::class);
        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);

        $this->scheduleBulk = new ScheduleBulk(
            $this->bulkManagementMock,
            $this->operationFactoryMock,
            $this->identityServiceMock,
            $this->userContextMock,
            $this->fileFactoryMock,
            $this->serializerMock
        );

        parent::setUp();
    }

    /**
     * @param int $countScheduledOperations
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecute(int $countScheduledOperations)
    {
        $fileName = 'some.file';
        $behavior = 'someBehavior';
        $uuid = 'someUUID';
        $serializedString = 'some string';
        $operationMock = $this->getMockForAbstractClass(OperationInterface::class);

        /** @var File|MockObject $fileMock */
        $fileMock = $this->createMock(File::class);
        $fileMock->expects($this->once())
            ->method('getRowsCount')
            ->willReturn(1);
        $this->fileFactoryMock->expects($this->once())
            ->method('create')
            ->with($fileName)
            ->willReturn($fileMock);
        $this->identityServiceMock->expects($this->once())
            ->method('generateId')
            ->willReturn($uuid);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with([
                'file_name' => $fileName,
                'offset' => 0,
                'length' => Import::ROWS_PER_OPERATION,
                'behavior' => $behavior,
                'meta_information' => 'The offset of rows: 0',
            ])
            ->willReturn($serializedString);
        $this->operationFactoryMock->expects($this->once())
            ->method('create')
            ->with([
                'data' => [
                    'bulk_uuid' => $uuid,
                    'topic_name' => 'url.rewrite.import',
                    'serialized_data' => $serializedString,
                    'status' => OperationInterface::STATUS_TYPE_OPEN,
                ]
            ])
            ->willReturn($operationMock);
        $this->userContextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn(13);
        $this->bulkManagementMock->expects($this->once())
            ->method('scheduleBulk')
            ->with($uuid, [$operationMock], 'Import URL Rewrites with behavior ' . $behavior, 13)
            ->willReturn($countScheduledOperations);

        if (0 === $countScheduledOperations) {
            $this->expectException(LocalizedException::class);
            $this->expectExceptionMessage('Something went wrong while processing the request.');
        }

        $this->scheduleBulk->execute($fileName, $behavior);
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            ['countScheduledOperations' => 0],
            ['countScheduledOperations' => 1]
        ];
    }
}
