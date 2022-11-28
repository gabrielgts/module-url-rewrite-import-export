<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Test\Unit\Model\Validator;

use Gtstudio\UrlRewriteImportExport\Model\Validator\StoreView;
use Gtstudio\UrlRewriteImportExport\Model\Import;
use Gtstudio\UrlRewriteImportExport\Model\StoreViewResolver;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class StoreViewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StoreView
     */
    private $storeView;

    /**
     * @var StoreViewResolver|MockObject
     */
    private $storeViewResolver;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->storeViewResolver = $this->createMock(StoreViewResolver::class);
        $this->storeView = new StoreView($this->storeViewResolver);

        parent::setUp();
    }

    /**
     * @return void
     */
    public function testIsValidEmpty()
    {
        $this->storeViewResolver->expects($this->never())
            ->method('getIdByCode');
        $this->assertFalse($this->storeView->isValid([]));
        $this->assertEquals(
            [__('Column %1 is empty', Import::COLUMN_STORE_VIEW_CODE_TITLE)],
            $this->storeView->getMessages()
        );
    }

    /**
     * @return void
     */
    public function testIsValidWrongCode()
    {
        $code = 'code';
        $this->storeViewResolver->expects($this->once())
            ->method('getIdByCode')
            ->with($code)
            ->willReturn(null);
        $this->assertFalse($this->storeView->isValid([Import::COLUMN_STORE_VIEW_CODE => $code]));
        $this->assertEquals(
            [__('Store View with %1 code does not exist', $code)],
            $this->storeView->getMessages()
        );
    }

    /**
     * @return void
     */
    public function testIsValid()
    {
        $code = 'code';
        $this->storeViewResolver->expects($this->once())
            ->method('getIdByCode')
            ->with($code)
            ->willReturn(1);
        $this->assertTrue($this->storeView->isValid([Import::COLUMN_STORE_VIEW_CODE => $code]));
        $this->assertEquals([], $this->storeView->getMessages());
    }
}
