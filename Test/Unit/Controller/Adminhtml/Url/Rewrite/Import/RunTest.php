<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Test\Unit\Controller\Adminhtml\Url\Rewrite\Import;

use Gtstudio\UrlRewriteImportExport\Controller\Adminhtml\Url\Rewrite\Import\Run;
use Magento\Backend\App\Action\Context;
use Psr\Log\LoggerInterface;
use Gtstudio\UrlRewriteImportExport\Model\ScheduleBulk;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class RunTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Run
     */
    private $run;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var ScheduleBulk|MockObject
     */
    private $scheduleBulkMock;

    /**
     * @var MessageManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var Redirect|MockObject
     */
    private $redirectMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var RedirectFactory|MockObject
     */
    private $resultRedirectFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->scheduleBulkMock = $this->createMock(ScheduleBulk::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->redirectMock = $this->createMock(Redirect::class);
        $this->messageManagerMock = $this->getMockForAbstractClass(MessageManagerInterface::class);
        $this->resultRedirectFactory = $this->createMock(RedirectFactory::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);

        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactory);

        $this->run = new Run(
            $this->contextMock,
            $this->scheduleBulkMock,
            $this->loggerMock
        );

        parent::setUp();
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $fileName = 'some.file';
        $importFile = [['file' => $fileName]];
        $behavior = 'somebehavior';

        $this->requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap([
                ['import_file', null, $importFile],
                ['behavior', null, $behavior]
            ]);
        $this->scheduleBulkMock->expects($this->once())
            ->method('execute')
            ->with($fileName, $behavior);
        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('Operations of import was scheduled...'));
        $this->resultRedirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->redirectMock);
        $this->redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/url_rewrite/import/')
            ->willReturnSelf();

        $this->assertEquals($this->redirectMock, $this->run->execute());
    }

    /**
     * @return void
     */
    public function testExecuteWithException()
    {
        $fileName = 'some.file';
        $importFile = [['file' => $fileName]];
        $behavior = 'somebehavior';
        $exception = new \Exception();

        $this->requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap([
                ['import_file', null, $importFile],
                ['behavior', null, $behavior]
            ]);
        $this->scheduleBulkMock->expects($this->once())
            ->method('execute')
            ->with($fileName, $behavior)
            ->willThrowException($exception);
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($exception);
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('Operations of import was not scheduled... More information in Magento logs.'));
        $this->resultRedirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->redirectMock);
        $this->redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/url_rewrite/import/')
            ->willReturnSelf();

        $this->assertEquals($this->redirectMock, $this->run->execute());
    }
}
