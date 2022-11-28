<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Test\Unit\Model;

use Gtstudio\UrlRewriteImportExport\Model\Storage;
use Magento\UrlRewrite\Model\StorageInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewriteApi;
use Magento\UrlRewrite\Model\UrlRewrite;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewrite as UrlRewriteResourceModel;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class StorageTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var StorageInterface|MockObject
     */
    private $storageMock;

    /**
     * @var UrlRewriteFactory|MockObject
     */
    private $urlRewriteFactoryMock;

    /**
     * @var UrlRewriteResourceModel|MockObject
     */
    private $urlRewriteResourceModelMock;

    /**
     * @var UrlRewrite|MockObject
     */
    private $urlRewriteModelMock;

    /**
     * @var UrlRewrite|MockObject
     */
    private $existedUrlRewriteMock;

    /**
     * @var array
     */
    private $insertUpdateData = [
        UrlRewriteApi::REQUEST_PATH => 'some path',
        UrlRewriteApi::STORE_ID => 'some id',
    ];

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->storageMock = $this->getMockForAbstractClass(StorageInterface::class);
        $this->urlRewriteFactoryMock = $this->createMock(UrlRewriteFactory::class);
        $this->urlRewriteResourceModelMock = $this->createMock(UrlRewriteResourceModel::class);
        $this->urlRewriteModelMock = $this->createMock(UrlRewrite::class);
        $this->existedUrlRewriteMock = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUrlRewriteId'])
            ->getMock();

        $this->storage = new Storage(
            $this->storageMock,
            $this->urlRewriteFactoryMock,
            $this->urlRewriteResourceModelMock
        );

        parent::setUp();
    }

    /**
     * @return void
     */
    public function testDelete()
    {
        $data = ['someKey' => 'someValue'];
        $this->storageMock->expects($this->once())
            ->method('deleteByData')
            ->with($data);

        $this->storage->delete($data);
    }

    /**
     * @return void
     */
    private function generalInsertUpdate()
    {
        $this->urlRewriteModelMock->expects($this->once())
            ->method('setData')
            ->with($this->insertUpdateData);
        $this->urlRewriteFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->urlRewriteModelMock);
        $this->urlRewriteResourceModelMock->expects($this->once())
            ->method('save')
            ->with($this->urlRewriteModelMock);
    }

    /**
     * @return void
     */
    public function testInsertUpdateNew()
    {
        $this->generalInsertUpdate();
        $this->storageMock->expects($this->once())
            ->method('findOneByData')
            ->willReturn(null);
        $this->urlRewriteModelMock->expects($this->never())
            ->method('setId');
        $this->existedUrlRewriteMock->expects($this->never())
            ->method('getUrlRewriteId');

        $this->storage->insertUpdate($this->insertUpdateData);
    }

    /**
     * @return void
     */
    public function testInsertUpdateExisted()
    {
        $this->generalInsertUpdate();
        $this->storageMock->expects($this->once())
            ->method('findOneByData')
            ->willReturn($this->existedUrlRewriteMock);
        $this->urlRewriteModelMock->expects($this->once())
            ->method('setId')
            ->with(888);
        $this->existedUrlRewriteMock->expects($this->once())
            ->method('getUrlRewriteId')
            ->willReturn(888);

        $this->storage->insertUpdate($this->insertUpdateData);
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Cannot insert or update an URL rewrite
     */
    public function testInsertUpdateWithException()
    {
        $this->generalInsertUpdate();
        $this->storageMock->expects($this->once())
            ->method('findOneByData')
            ->willReturn(null);
        $this->urlRewriteModelMock->expects($this->never())
            ->method('setId');
        $this->existedUrlRewriteMock->expects($this->never())
            ->method('getUrlRewriteId');

        $this->urlRewriteResourceModelMock->expects($this->once())
            ->method('save')
            ->with($this->urlRewriteModelMock)
            ->willThrowException(new \Exception('Some error'));

        $this->storage->insertUpdate($this->insertUpdateData);
    }
}
