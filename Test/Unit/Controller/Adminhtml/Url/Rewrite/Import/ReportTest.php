<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Test\Unit\Controller\Adminhtml\Url\Rewrite\Import;

use Gtstudio\UrlRewriteImportExport\Controller\Adminhtml\Url\Rewrite\Import\Report;
use Gtstudio\UrlRewriteImportExport\Model\File\ImportDirectory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory as ResponseFileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Psr\Log\LoggerInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ReportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Report
     */
    private $report;

    /**
     * @var ImportDirectory|MockObject
     */
    private $importDirectoryMock;

    /**
     * @var ResponseFileFactory|MockObject
     */
    private $responseFileFactoryMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $responseMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var MessageManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var RedirectFactory|MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var Redirect|MockObject
     */
    private $redirectMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var int
     */
    private $operationId = 7;

    /**
     * @var string
     */
    private $fileName = 'operation_7.csv';

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->importDirectoryMock = $this->createMock(ImportDirectory::class);
        $this->responseFileFactoryMock = $this->createMock(ResponseFileFactory::class);
        $this->responseMock = $this->getMockForAbstractClass(ResponseInterface::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->redirectMock = $this->createMock(Redirect::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->messageManagerMock = $this->getMockForAbstractClass(MessageManagerInterface::class);
        $this->resultRedirectFactory = $this->createMock(RedirectFactory::class);
        $this->redirectMock = $this->createMock(Redirect::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactory);
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('operation')
            ->willReturn($this->operationId);

        $this->report = new Report(
            $this->contextMock,
            $this->importDirectoryMock,
            $this->responseFileFactoryMock,
            $this->loggerMock
        );

        parent::setUp();
    }

    /**
     * @return void
     */
    public function testExecuteWhenFileDoesNotExist()
    {
        $this->importDirectoryMock->expects($this->once())
            ->method('isFileExist')
            ->with($this->fileName)
            ->willReturn(false);
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('Report file for operation %1 does not exist', $this->operationId))
            ->willReturnSelf();
        $this->resultRedirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->redirectMock);
        $this->redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/url_rewrite/import/')
            ->willReturnSelf();

        $this->assertEquals($this->redirectMock, $this->report->execute());
    }

    /**
     * @return void
     */
    public function testExecuteWhenFileExists()
    {
        $filePath = 'some/path';
        $this->importDirectoryMock->expects($this->once())
            ->method('isFileExist')
            ->with($this->fileName)
            ->willReturn(true);
        $this->importDirectoryMock->expects($this->once())
            ->method('getFilePathByName')
            ->with($this->fileName)
            ->willReturn($filePath);
        $this->responseFileFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                $this->fileName,
                ['type' => 'filename', 'value' => $filePath],
                DirectoryList::VAR_DIR
            )
            ->willReturn($this->responseMock);

        $this->assertSame($this->responseMock, $this->report->execute());
    }

    /**
     * @return void
     */
    public function testExecuteWhenFileExistsWithException()
    {
        $filePath = 'some/path';
        $message = 'Some error';
        $exception = new \Exception($message);

        $this->importDirectoryMock->expects($this->once())
            ->method('isFileExist')
            ->with($this->fileName)
            ->willReturn(true);
        $this->importDirectoryMock->expects($this->once())
            ->method('getFilePathByName')
            ->with($this->fileName)
            ->willReturn($filePath);
        $this->responseFileFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                $this->fileName,
                ['type' => 'filename', 'value' => $filePath],
                DirectoryList::VAR_DIR
            )
            ->willThrowException($exception);
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($exception);
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($message)
            ->willReturnSelf();
        $this->resultRedirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->redirectMock);
        $this->redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/url_rewrite/import/')
            ->willReturnSelf();

        $this->assertEquals($this->redirectMock, $this->report->execute());
    }
}
