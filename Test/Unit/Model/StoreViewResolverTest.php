<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Test\Unit\Model;

use Gtstudio\UrlRewriteImportExport\Model\StoreViewResolver;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class StoreViewResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StoreViewResolver
     */
    private $storeViewResolver;

    /**
     * @var StoreRepositoryInterface|MockObject
     */
    private $storeRepositoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->storeRepositoryMock = $this->getMockForAbstractClass(StoreRepositoryInterface::class);
        $this->storeViewResolver = new StoreViewResolver($this->storeRepositoryMock);

        parent::setUp();
    }

    /**
     * @return void
     */
    public function testGetIdByCode()
    {
        /** @var StoreInterface|MockObject $firstStoreMock */
        $firstStoreMock = $this->getMockForAbstractClass(StoreInterface::class);
        $firstStoreMock->expects($this->once())
            ->method('getCode')
            ->willReturn('firstCode');
        $firstStoreMock->expects($this->once())
            ->method('getId')
            ->willReturn(6);
        /** @var StoreInterface|MockObject $secondStoreMock */
        $secondStoreMock = $this->getMockForAbstractClass(StoreInterface::class);
        $secondStoreMock->expects($this->once())
            ->method('getCode')
            ->willReturn('secondCode');
        $secondStoreMock->expects($this->once())
            ->method('getId')
            ->willReturn(9);
        $this->storeRepositoryMock->expects($this->once())
            ->method('getList')
            ->willReturn([$firstStoreMock, $secondStoreMock]);

        $this->assertSame(6, $this->storeViewResolver->getIdByCode('firstCode'));
        $this->assertSame(9, $this->storeViewResolver->getIdByCode('secondCode'));
        $this->assertSame(null, $this->storeViewResolver->getIdByCode('wrongCode'));
    }
}
