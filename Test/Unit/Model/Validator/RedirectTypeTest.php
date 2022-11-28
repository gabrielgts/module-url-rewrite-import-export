<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Test\Unit\Model\Validator;

use Gtstudio\UrlRewriteImportExport\Model\Validator\RedirectType;
use Gtstudio\UrlRewriteImportExport\Model\Import;

class RedirectTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RedirectType
     */
    private $redirectType;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->redirectType = new RedirectType();

        parent::setUp();
    }

    /**
     * @param array $data
     * @param bool $isValid
     * @param array $expectedMessages
     * @return void
     * @dataProvider isValidDataProvider
     */
    public function testIsValid(array $data, bool $isValid, array $expectedMessages)
    {
        $this->assertSame($isValid, $this->redirectType->isValid($data));
        $this->assertEquals($expectedMessages, $this->redirectType->getMessages());
    }

    /**
     * @return array
     */
    public function isValidDataProvider(): array
    {
        return [
            [
                'data' => [],
                'isValid' => false,
                'expectedMessages' => [__('Column %1 is empty', Import::COLUMN_REDIRECT_TYPE_TITLE)],
            ],
            [
                'data' => [Import::COLUMN_REDIRECT_TYPE => '503'],
                'isValid' => false,
                'expectedMessages' => [__('Column %1 has wrong redirect code', Import::COLUMN_REDIRECT_TYPE_TITLE)],
            ],
            [
                'data' => [Import::COLUMN_REDIRECT_TYPE => '0'],
                'isValid' => true,
                'expectedMessages' => [],
            ],
            [
                'data' => [Import::COLUMN_REDIRECT_TYPE => '301'],
                'isValid' => true,
                'expectedMessages' => [],
            ],
            [
                'data' => [Import::COLUMN_REDIRECT_TYPE => '302'],
                'isValid' => true,
                'expectedMessages' => [],
            ],
        ];
    }
}
