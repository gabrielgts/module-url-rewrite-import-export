<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Test\Unit\Block\Adminhtml\Url\Rewrite;

use Gtstudio\UrlRewriteImportExport\Block\Adminhtml\Url\Rewrite\BackButton;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\UrlInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class BackButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BackButton
     */
    private $backButton;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilderMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->backButton = new BackButton($this->contextMock);

        $this->contextMock->expects($this->once())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilderMock);
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('*/*/')
            ->willReturn('localhost/some-url');

        parent::setUp();
    }

    /**
     * @return void
     */
    public function testGetBackUrl()
    {
        $this->assertSame('localhost/some-url', $this->backButton->getBackUrl());
    }

    /**
     * @return void
     */
    public function testGetButtonData()
    {
        $expectedResult = [
            'label' => __('Back'),
            'on_click' => "location.href = 'localhost/some-url';",
            'class' => 'back',
            'sort_order' => 10
        ];

        $this->assertEquals($expectedResult, $this->backButton->getButtonData());
    }
}
