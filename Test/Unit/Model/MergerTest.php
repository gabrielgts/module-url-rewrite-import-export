<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Test\Unit\Model;

use Gtstudio\UrlRewriteImportExport\Model\Merger;
use Magento\AsynchronousOperations\Api\Data\OperationListInterfaceFactory;
use Magento\Framework\MessageQueue\MergedMessageInterfaceFactory;
use Magento\AsynchronousOperations\Api\Data\OperationListInterface;
use Magento\Framework\MessageQueue\MergedMessageInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class MergerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Merger
     */
    private $merger;

    /**
     * @var OperationListInterfaceFactory|MockObject
     */
    private $operationListFactory;

    /**
     * @var MergedMessageInterfaceFactory|MockObject
     */
    private $mergedMessageFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->operationListFactory = $this->createMock(OperationListInterfaceFactory::class);
        $this->mergedMessageFactory = $this->createMock(MergedMessageInterfaceFactory::class);
        $this->merger = new Merger($this->operationListFactory, $this->mergedMessageFactory);

        parent::setUp();
    }

    /**
     * @return void
     */
    public function testMerge()
    {
        $topicName = 'topic.name';
        $messages = [$topicName => [1 => 'message1', 2 => 'message2']];
        $operationList = $this->getMockForAbstractClass(OperationListInterface::class);
        $mergedMessage = $this->getMockForAbstractClass(MergedMessageInterface::class);

        $this->operationListFactory->expects($this->atLeastOnce())
            ->method('create')
            ->with(['items' => $messages[$topicName]])
            ->willReturn($operationList);
        $this->mergedMessageFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($mergedMessage);

        $this->assertEquals([$topicName => [$mergedMessage]], $this->merger->merge($messages));
    }
}
