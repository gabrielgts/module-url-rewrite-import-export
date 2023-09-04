<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Model\File;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * The class for working with a directory that contains imported/report files
 */
class ImportDirectory
{
    /**
     * The name of directory that contains imported/report files
     */
    const URL_REWRITE_IMPORT_DIR = 'url_rewrite_import';

    /**
     * The instance of the object to work with FS
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param Filesystem $filesystem The instance of the object to work with FS
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Get the absolute path of directory with imported/report files
     *
     * @return string The absolute path of directory with imported/report files
     */
    public function getPath(): string
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath(static::URL_REWRITE_IMPORT_DIR);
    }

    /**
     * Get absolute path of an imported/report file by its name
     *
     * @param string $fileName The file name
     * @return string The absolute path of file
     */
    public function getFilePathByName(string $fileName): string
    {
        return $this->getPath() . '/' . $fileName;
    }

    /**
     * Check if imported/report file exists by its name
     *
     * @param string $fileName The file name
     * @return bool Return true if a file exists otherwise, return false
     */
    public function isFileExist(string $fileName): bool
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)
                ->isExist(static::URL_REWRITE_IMPORT_DIR . '/' . $fileName);
    }
}
