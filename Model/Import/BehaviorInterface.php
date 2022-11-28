<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Model\Import;

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface for behavior of the import ulr rewrites
 */
interface BehaviorInterface
{
    /**
     * @param int $operationId The id of operation from the bulk operation list
     * @param array $rows The list of the url rewrites
     * @throws LocalizedException The exception that is thrown if something goes wrong
     */
    public function execute(int $operationId, array $rows = []);
}
