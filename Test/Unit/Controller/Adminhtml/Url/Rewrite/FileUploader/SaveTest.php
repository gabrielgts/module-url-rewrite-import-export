<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Test\Unit\Controller\Adminhtml\Url\Rewrite\FileUploader;

use Gtstudio\UrlRewriteImportExport\Controller\Adminhtml\Url\Rewrite\FileUploader\Save;
use Magento\Backend\App\Action\Context;
use Gtstudio\UrlRewriteImportExport\Model\File\Uploader as FileUploader;
use Gtstudio\UrlRewriteImportExport\Model\File\UploaderFactory as FileUploaderFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Save
     */
    private $save;

    /**
     * @var FileUploaderFactory|MockObject
     */
    private $fileUploaderFactoryMock;

    /**
     * @var FileUploader|MockObject
     */
    private $fileUploader;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var ResultInterface|MockObject
     */
    private $resultMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->fileUploaderFactoryMock = $this->createMock(FileUploaderFactory::class);
        $this->fileUploader = $this->createMock(FileUploader::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->resultMock = $this->getMockBuilder(ResultInterface::class)
            ->setMethods(['setData'])
            ->getMockForAbstractClass();

        $this->contextMock->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($this->resultMock);
        $this->fileUploaderFactoryMock->expects($this->once())
            ->method('create')
            ->with(['fileId' =>'import_file'])
            ->willReturn($this->fileUploader);

        $this->save = new Save(
            $this->contextMock,
            $this->fileUploaderFactoryMock,
            $this->loggerMock
        );

        parent::setUp();
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $this->fileUploader->expects($this->once())
            ->method('upload')
            ->willReturn(['tmp_name' => 'tmp', 'path' => 'some_path', 'file_name' => 'somefile.file']);
        $this->resultMock->expects($this->once())
            ->method('setData')
            ->with(['file_name' => 'somefile.file'])
            ->willReturnSelf();

        $this->assertEquals($this->resultMock, $this->save->execute());
    }

    /**
     * @return void
     */
    public function testExecuteWithException()
    {
        $message = 'Some error';
        $code = 503;
        $exception = new \Exception($message, $code);

        $this->fileUploader->expects($this->once())
            ->method('upload')
            ->willThrowException($exception);
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($exception);
        $this->resultMock->expects($this->once())
            ->method('setData')
            ->with(['error' => $message, 'errorcode' => $code])
            ->willReturnSelf();

        $this->assertEquals($this->resultMock, $this->save->execute());
    }
}
