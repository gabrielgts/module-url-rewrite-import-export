<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Test\Unit\Model;

use Gtstudio\UrlRewriteImportExport\Model\Import;
use Gtstudio\UrlRewriteImportExport\Model\File;
use Gtstudio\UrlRewriteImportExport\Model\FileFactory;
use Gtstudio\UrlRewriteImportExport\Model\Import\BehaviorFactory;
use Gtstudio\UrlRewriteImportExport\Model\Import\BehaviorInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ImportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Import
     */
    private $import;

    /**
     * @var FileFactory|MockObject
     */
    private $fileFactoryMock;

    /**
     * @var BehaviorFactory|MockObject
     */
    private $behaviorFactoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->fileFactoryMock = $this->createMock(FileFactory::class);
        $this->behaviorFactoryMock = $this->createMock(BehaviorFactory::class);
        $this->import = new Import($this->fileFactoryMock, $this->behaviorFactoryMock);

        parent::setUp();
    }

    /**
     * @param array $rows
     * @param array $expectedRows
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecute(array $rows, array $expectedRows)
    {
        $operationId = 3;
        $fileName = 'some.file';
        $offset = 0;
        $length = 10;
        $behavior = 'someBehavior';

        /** @var File|MockObject $fileMock */
        $fileMock = $this->createMock(File::class);
        $fileMock->expects($this->once())
            ->method('getRows')
            ->with($offset, $length)
            ->willReturn($rows);
        $this->fileFactoryMock->expects($this->once())
            ->method('create')
            ->with($fileName)
            ->willReturn($fileMock);
        /** @var BehaviorInterface|MockObject $behaviorMock */
        $behaviorMock = $this->getMockForAbstractClass(BehaviorInterface::class);
        $behaviorMock->expects($this->once())
            ->method('execute')
            ->with($operationId, $expectedRows);
        $this->behaviorFactoryMock->expects($this->once())
            ->method('create')
            ->with($behavior)
            ->willReturn($behaviorMock);

        $this->import->execute($operationId, $fileName, $offset, $length, $behavior);
    }

    /**
     * @return array
     */
    public function executeDataProvider(): array
    {
        return [
            [
                'rows' => [
                    ['some/path', 'some/target/path', 302, 'default']
                ],
                'expectedRows' => [
                    ['some/path', 'some/target/path', 302, 'default']
                ],
            ],
            [
                'rows' => [
                    [
                        Import::COLUMN_REQUEST_PATH_TITLE,
                        Import::COLUMN_REQUEST_PATH_TITLE,
                        Import::COLUMN_REDIRECT_TYPE_TITLE,
                        Import::COLUMN_STORE_VIEW_CODE_TITLE
                    ],
                    ['some/path', 'some/target/path', 302, 'default']
                ],
                'expectedRows' => [
                    1 => ['some/path', 'some/target/path', 302, 'default']
                ],
            ],
        ];
    }
}
