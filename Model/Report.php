<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Model;

/**
 * The class to work with reports
 */
class Report
{
    /**
     * The factory to create the file model instance
     *
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * The template for name of report files
     */
    const REPORT_FILENAME_TEMPLATE = 'operation_%d.csv';

    /**
     * @param FileFactory $fileFactory The factory to create the file model instance
     */
    public function __construct(FileFactory $fileFactory)
    {
        $this->fileFactory = $fileFactory;
    }

    /**
     * Save report
     *
     * @param int $operationId The id of operation from the bulk operation list
     * @param array $rows The list of url rewrites with error messages
     * @return void
     */
    public function save(int $operationId, array $rows = [])
    {
        /** @var File $file */
        $file = $this->fileFactory->create(sprintf(self::REPORT_FILENAME_TEMPLATE, $operationId), 'w');
        $file->addRow([
            Import::COLUMN_REQUEST_PATH_TITLE,
            Import::COLUMN_TARGET_PATH_TITLE,
            Import::COLUMN_REDIRECT_TYPE_TITLE,
            Import::COLUMN_STORE_VIEW_CODE_TITLE,
            Import::COLUMN_MESSAGES_TITLE,
        ]);

        foreach ($rows as $row) {
            $file->addRow($row);
        }
    }
}
