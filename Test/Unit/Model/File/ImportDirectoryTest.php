<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Test\Unit\Model\File;

use Gtstudio\UrlRewriteImportExport\Model\File\ImportDirectory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ImportDirectoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ImportDirectory
     */
    private $importDirectory;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystemMock;

    /**
     * @var ReadInterface|MockObject
     */
    private $directoryReadMock;

    /**
     * @var string
     */
    private $directoryAbsolutePath = 'some/absolute/path';

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->directoryReadMock = $this->getMockForAbstractClass(ReadInterface::class);
        $this->importDirectory = new ImportDirectory($this->filesystemMock);

        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->directoryReadMock);

        parent::setUp();
    }

    /**
     * @return void
     */
    public function testGetPath()
    {
        $this->directoryReadMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with(ImportDirectory::URL_REWRITE_IMPORT_DIR)
            ->willReturn($this->directoryAbsolutePath);

        $this->assertSame($this->directoryAbsolutePath, $this->importDirectory->getPath());
    }

    /**
     * @return void
     */
    public function testGetFilePathByName()
    {
        $fileName = 'some.file';

        $this->directoryReadMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with(ImportDirectory::URL_REWRITE_IMPORT_DIR)
            ->willReturn($this->directoryAbsolutePath);

        $this->assertSame(
            $this->directoryAbsolutePath . '/' . $fileName,
            $this->importDirectory->getFilePathByName($fileName)
        );
    }

    /**
     * @return void
     */
    public function testIsFileExist()
    {
        $fileName = 'some.file';
        $result = true;
        $this->directoryReadMock->expects($this->once())
            ->method('isExist')
            ->with(ImportDirectory::URL_REWRITE_IMPORT_DIR . '/' . $fileName)
            ->willReturn($result);

        $this->assertSame($result, $this->importDirectory->isFileExist($fileName));
    }
}
