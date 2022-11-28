<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Model;

use SplFileObject;

/**
 * Class to work with imported/report files
 */
class File
{
    /**
     * The SplFileObject class offers an object oriented interface for a file
     *
     * @var SplFileObject
     */
    private $fileObject;

    /**
     * @param SplFileObject $fileObject The SplFileObject class offers an object oriented interface for a file
     */
    public function __construct(
        SplFileObject $fileObject
    ) {
        $this->fileObject = $fileObject;
        $this->fileObject->setFlags(SplFileObject::READ_CSV);
    }

    /**
     * Get rows count
     *
     * @return int
     */
    public function getRowsCount(): int
    {
        $this->fileObject->rewind();
        $this->fileObject->seek(PHP_INT_MAX);

        return $this->fileObject->key() + 1;
    }

    /**
     * Get rows list
     *
     * @param int $offset The rows offset to read from the imported file
     * @param int $length The number of rows to read from the imported file
     * @return array The rows list
     */
    public function getRows(int $offset, int $length = 100): array
    {
        $rows = [];
        $iteration = 1;
        $this->fileObject->seek($offset);

        do {
            $rows[$this->fileObject->key()] = $this->fileObject->current();
            $this->fileObject->next();
            $iteration++;
        } while (!$this->fileObject->eof() && $iteration <= $length);

        return $rows;
    }

    /**
     * Add row
     *
     * @param array $fields The fields list to add
     * @return int|bool Returns the length of the written string or FALSE on failure
     */
    public function addRow(array $fields = [])
    {
        return $this->fileObject->fputcsv($fields);
    }
}
