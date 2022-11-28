<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Test\Unit\Model;

use Gtstudio\UrlRewriteImportExport\Model\FileFactory;
use Gtstudio\UrlRewriteImportExport\Model\File;
use Magento\Framework\ObjectManagerInterface;
use Gtstudio\UrlRewriteImportExport\Model\File\ImportDirectory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class FileFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var ImportDirectory|MockObject
     */
    private $importDirectoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->importDirectoryMock = $this->createMock(ImportDirectory::class);
        $this->fileFactory = new FileFactory($this->objectManagerMock, $this->importDirectoryMock);

        parent::setUp();
    }

    /**
     * @return void
     */
    public function testCreate()
    {
        $fileName = 'some.file';
        $dirPath = 'some/dir/path';

        /** @var \SplFileObject|MockObject $splFileMock */
        $splFileMock = $this->getMockBuilder(\SplFileObject::class)
            ->setConstructorArgs(['php://memory'])
            ->getMock();

        /** @var File|MockObject $fileMock */
        $fileMock = $this->createMock(File::class);
        $this->importDirectoryMock->expects($this->once())
            ->method('getPath')
            ->willReturn($dirPath);
        $this->objectManagerMock->expects($this->exactly(2))
            ->method('create')
            ->withConsecutive(
                [\SplFileObject::class, ['file_name' => $dirPath . '/' . $fileName, 'open_mode' => 'r']],
                [File::class, ['fileObject' => $splFileMock]]
            )
            ->willReturnOnConsecutiveCalls(
                $splFileMock,
                $fileMock
            );

        $this->assertSame($fileMock, $this->fileFactory->create($fileName));
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Cannot open the file some.file
     */
    public function testCreateWithException()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willThrowException(new \Exception('Some error'));
        $this->fileFactory->create('some.file');
    }
}
