<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Test\Unit\Model;

use Gtstudio\UrlRewriteImportExport\Model\File;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class FileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var File
     */
    private $file;

    /**
     * @var \SplFileObject|MockObject
     */
    private $splFileMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->splFileMock = $this->getMockBuilder(\SplFileObject::class)
            ->setConstructorArgs(['php://memory'])
            ->getMock();
        $this->splFileMock->expects($this->once())
            ->method('setFlags')
            ->with(\SplFileObject::READ_CSV);
        $this->file = new File($this->splFileMock);

        parent::setUp();
    }

    /**
     * @return void
     */
    public function testAddRow()
    {
        $data = ['some value'];
        $this->splFileMock->expects($this->once())
            ->method('fputcsv')
            ->with($data)
            ->willReturn(10);

        $this->assertSame(10, $this->file->addRow($data));
    }

    /**
     * @return void
     */
    public function testGetRowsCount()
    {
        $this->splFileMock->expects($this->once())
            ->method('rewind');
        $this->splFileMock->expects($this->once())
            ->method('seek')
            ->with(PHP_INT_MAX);
        $this->splFileMock->expects($this->once())
            ->method('key')
            ->willReturn(10);

        $this->assertSame(11, $this->file->getRowsCount());
    }

    /**
     * @param int $offset
     * @param int $length
     * @param bool $eof
     * @return void
     * @dataProvider getRowsDataProvider
     */
    public function testGetRows(int $offset, int $length, bool $eof)
    {
        $this->splFileMock->expects($this->once())
            ->method('seek')
            ->with($offset);
        $this->splFileMock->expects($this->once())
            ->method('key')
            ->willReturn($offset);
        $this->splFileMock->expects($this->once())
            ->method('current')
            ->willReturn('some value');
        $this->splFileMock->expects($this->once())
            ->method('next');
        $this->splFileMock->expects($this->once())
            ->method('eof')
            ->willReturn($eof);

        $this->assertSame(
            [$offset => 'some value'],
            $this->file->getRows($offset, $length)
        );
    }

    /**
     * @return array
     */
    public function getRowsDataProvider(): array
    {
        return [
            ['offset' => 0, 'length' => 1, 'eof' => false],
            ['offset' => 0, 'length' => 2, 'eof' => true],
        ];
    }
}
