<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Test\Unit\Model\Validator;

use Gtstudio\UrlRewriteImportExport\Model\Validator\TargetPath;
use Gtstudio\UrlRewriteImportExport\Model\Import;

class TargetPathTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TargetPath
     */
    private $targetPath;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->targetPath = new TargetPath();

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
        $this->assertSame($isValid, $this->targetPath->isValid($data));
        $this->assertEquals($expectedMessages, $this->targetPath->getMessages());
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
                'expectedMessages' => [__('Column %1 is empty', Import::COLUMN_TARGET_PATH_TITLE)],
            ],
            [
                'data' => [Import::COLUMN_TARGET_PATH => 'some/path'],
                'isValid' => true,
                'expectedMessages' => [],
            ],
        ];
    }
}
