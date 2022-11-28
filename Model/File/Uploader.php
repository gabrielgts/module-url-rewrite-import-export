<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Model\File;

/**
 * The file uploader to upload file from a tmp directory to the destination folder
 */
class Uploader extends \Magento\Framework\File\Uploader
{
    /**
     * The class for working with a directory that contains imported/report files
     *
     * @var ImportDirectory
     */
    private $importDirectory;

    /**
     * @param ImportDirectory $importDirectory The class for working with a directory
     *        that contains imported/report files
     * @param string $fileId The key of array $_FILES that contain information about uploaded file
     * @param array $allowedExtensions The list of allowed file extensions
     * @throws \Exception An exception is thrown when the file was not uploaded
     */
    public function __construct(
        ImportDirectory $importDirectory,
        string $fileId,
        array $allowedExtensions = []
    ) {
        $this->_allowedExtensions = $allowedExtensions;
        $this->importDirectory = $importDirectory;
        parent::__construct($fileId);
    }

    /**
     * @return array The information about saved file
     * @throws \Exception An exception is thrown when there is some problem with moving file from tmp directory
     */
    public function upload(): array
    {
        return $this->save(
            $this->importDirectory->getPath(),
            time() . '.' . $this->getFileExtension()
        );
    }
}
