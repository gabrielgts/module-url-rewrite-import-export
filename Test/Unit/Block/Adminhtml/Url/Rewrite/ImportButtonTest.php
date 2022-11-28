<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Test\Unit\Block\Adminhtml\Url\Rewrite;

use Gtstudio\UrlRewriteImportExport\Block\Adminhtml\Url\Rewrite\ImportButton;

class ImportButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ImportButton
     */
    private $importButton;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->importButton = new ImportButton();

        parent::setUp();
    }

    /**
     * @return void
     */
    public function testGetButtonData()
    {
        $this->assertEquals(
            [
                'label' => __('Import'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => [
                        'buttonAdapter' => [
                            'actions' => [
                                [
                                    'targetName' => 'url_rewrite_import_form',
                                    'actionName' => 'save',
                                    'params' => [
                                        false
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'sort_order' => 0,
            ],
            $this->importButton->getButtonData()
        );
    }
}
