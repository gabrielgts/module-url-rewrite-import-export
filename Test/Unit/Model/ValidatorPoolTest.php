<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Test\Unit\Model;

use Gtstudio\UrlRewriteImportExport\Model\ValidatorPool;
use Magento\Framework\ValidatorFactory;
use Magento\Framework\Validator;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ValidatorPoolTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ValidatorPool
     */
    private $validatorPool;

    /**
     * @var ValidatorFactory|MockObject
     */
    private $validatorFactoryMock;

    /**
     * @var Validator|MockObject
     */
    private $someValidator;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->validatorFactoryMock = $this->createMock(ValidatorFactory::class);
        $this->someValidator = $this->createMock(Validator::class);

        $this->validatorPool = new ValidatorPool(
            $this->validatorFactoryMock,
            ['someBehavior' => ['someValidator' => $this->someValidator]]
        );

        parent::setUp();
    }

    /**
     * @return void
     */
    public function testGetValidator()
    {
        /** @var Validator|MockObject $compositValidator */
        $compositValidator = $this->createMock(Validator::class);
        $compositValidator->expects($this->once())
            ->method('addValidator')
            ->with($this->someValidator);
        $this->validatorFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($compositValidator);

        $this->assertSame($compositValidator, $this->validatorPool->getValidator('someBehavior'));
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\ConfigurationMismatchException
     * @expectedExceptionMessage There are no validators for wrongBehavior behavior
     */
    public function testGetValidatorWithException()
    {
        $this->validatorPool->getValidator('wrongBehavior');
    }
}
