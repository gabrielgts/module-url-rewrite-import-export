<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Model;

use Gtstudio\UrlRewriteImportExport\Model\Import\BehaviorFactory;
use Magento\Framework\Exception\LocalizedException;
use Gtstudio\UrlRewriteImportExport\Model\Import\BehaviorInterface;

/**
 * Class to import url rewrites
 */
class Import
{
    const BEHAVIOR_ADD_UPDATE = 'add_update';
    const BEHAVIOR_DELETE = 'delete';

    const ROWS_PER_OPERATION = 5000;

    const COLUMN_REQUEST_PATH = 0;
    const COLUMN_TARGET_PATH = 1;
    const COLUMN_REDIRECT_TYPE = 2;
    const COLUMN_STORE_VIEW_CODE = 3;
    const COLUMN_MESSAGES = 4;
    const COLUMN_REQUEST_PATH_TITLE = 'request_path';
    const COLUMN_TARGET_PATH_TITLE = 'target_path';
    const COLUMN_REDIRECT_TYPE_TITLE = 'redirect_type';
    const COLUMN_STORE_VIEW_CODE_TITLE = 'store_code';
    const COLUMN_MESSAGES_TITLE = 'messages';

    /**
     * The factory to create the file model instance
     *
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * The factory to create the behavior instance
     *
     * @var BehaviorFactory
     */
    private $behaviorFactory;

    /**
     * @param FileFactory $fileFactory The factory to create the file model instance
     * @param BehaviorFactory $behaviorFactory The factory to create the behavior instance
     */
    public function __construct(
        FileFactory $fileFactory,
        BehaviorFactory $behaviorFactory
    ) {
        $this->fileFactory = $fileFactory;
        $this->behaviorFactory = $behaviorFactory;
    }

    /**
     * @param int $operationId The id of operation from the bulk operation list
     * @param string $fileName The name of the imported file
     * @param int $offset The rows offset to read from the imported file
     * @param int $length The number of rows to read from the imported file
     * @param string $behavior The behavior name
     * @throws LocalizedException The exception that is thrown if the behavior fails
     */
    public function execute(int $operationId, string $fileName, int $offset, int $length, string $behavior)
    {
        $rows = $this->removeFirstLine(
            $this->fileFactory->create($fileName)
                ->getRows($offset, $length)
        );

        /** @var BehaviorInterface $behaviorObj */
        $behaviorObj = $this->behaviorFactory->create($behavior);
        $behaviorObj->execute($operationId, $rows);
    }

    /**
     * Remove the first row if it is title
     *
     * @param array $rows The rows list
     * @return array The rows list without first title row
     */
    private function removeFirstLine(array $rows = []): array
    {
        if (!empty($rows[0][self::COLUMN_REQUEST_PATH])
            && $rows[0][self::COLUMN_REQUEST_PATH] === self::COLUMN_REQUEST_PATH_TITLE) {
            unset($rows[0]);
        }

        return $rows;
    }
}
