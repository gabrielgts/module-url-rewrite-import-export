<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Test\Unit\Model\Import;

use Gtstudio\UrlRewriteImportExport\Model\Import\BehaviorFactory;
use Gtstudio\UrlRewriteImportExport\Model\Import;
use Magento\Framework\ObjectManagerInterface;
use Gtstudio\UrlRewriteImportExport\Model\Import\Behavior;
use Gtstudio\UrlRewriteImportExport\Model\Import\BehaviorInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class BehaviorFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BehaviorFactory
     */
    private $behaviorFactory;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->behaviorFactory = new BehaviorFactory($this->objectManagerMock);

        parent::setUp();
    }

    /**
     * @param string $behavior
     * @param string $class
     * @return void
     * @dataProvider createDataProvider
     */
    public function testCreate(string $behavior, string $class)
    {
        /** @var BehaviorInterface|MockObject $behaviorMock */
        $behaviorMock = $this->getMockForAbstractClass(BehaviorInterface::class);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($class, [])
            ->willReturn($behaviorMock);

        $this->assertSame($behaviorMock, $this->behaviorFactory->create($behavior));
    }

    /**
     * @return array
     */
    public function createDataProvider(): array
    {
        return [
            ['behavior' => Import::BEHAVIOR_DELETE, 'class' => Behavior\Delete::class],
            ['behavior' => Import::BEHAVIOR_ADD_UPDATE, 'class' => Behavior\AddUpdate::class],
        ];
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\RuntimeException
     * @expectedExceptionMessage Wrong behavior
     */
    public function testCreateWithException()
    {
        $this->objectManagerMock->expects($this->never())
            ->method('create');
        $this->behaviorFactory->create('wrongBehavior');
    }
}
