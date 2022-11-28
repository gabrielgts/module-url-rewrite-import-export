<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Model;

use Magento\Framework\ObjectManagerInterface;
use Gtstudio\UrlRewriteImportExport\Model\File\ImportDirectory;
use Magento\Framework\Exception\LocalizedException;

/**
 * The factory to create the file model instance
 */
class FileFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * The class for working with a directory that contains imported/report files
     *
     * @var ImportDirectory
     */
    private $importDirectory;

    /**
     * @param ObjectManagerInterface $objectManager Object Manager instance
     * @param ImportDirectory $importDirectory The class for working with a directory
     *        that contains imported/report files
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ImportDirectory $importDirectory
    ) {
        $this->objectManager = $objectManager;
        $this->importDirectory = $importDirectory;
    }

    /**
     * Create File instance with specified parameters
     *
     * @param string $fileName The file name
     * @param string $openMode The mode in which to open the file
     * @return File The class to work with imported/report files
     * @throws LocalizedException The exception that is thrown if cannot open the file
     */
    public function create(string $fileName, string $openMode = 'r'): File
    {
        try {
            return $this->objectManager->create(
                File::class,
                ['fileObject' => $this->createSplFile($fileName, $openMode)]
            );
        } catch (\Exception $e) {
            throw new LocalizedException(__('Cannot open the file %1', $fileName), $e);
        }
    }

    /**
     * Create SplFileObject
     *
     * @param string $fileName The file name
     * @param string $openMode The mode in which to open the file
     * @return \SplFileObject The SplFileObject class offers an object oriented interface for a file
     * @throws \RuntimeException The exception that is thrown if the file is cannot be opened
     * @throws \LogicException The exception that is thrown if the file is a directory
     */
    private function createSplFile(string $fileName, string $openMode): \SplFileObject
    {
        return $this->objectManager->create(
            \SplFileObject::class,
            [
                'file_name' => $this->importDirectory->getPath() . '/' . $fileName,
                'open_mode' => $openMode
            ]
        );
    }
}
