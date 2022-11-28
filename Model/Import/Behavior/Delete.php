<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Model\Import\Behavior;

use Gtstudio\UrlRewriteImportExport\Model\Import;
use Gtstudio\UrlRewriteImportExport\Model\Import\BehaviorInterface;
use Gtstudio\UrlRewriteImportExport\Model\ValidatorPool;
use Gtstudio\UrlRewriteImportExport\Model\Storage;
use Gtstudio\UrlRewriteImportExport\Model\Report;
use Gtstudio\UrlRewriteImportExport\Model\StoreViewResolver;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewriteApi;
use Magento\Framework\Exception\LocalizedException;

/**
 * The behavior to delete url rewrites
 */
class Delete implements BehaviorInterface
{
    /**
     * The pool of validators
     *
     * @var ValidatorPool
     */
    private $validatorPool;

    /**
     * The store view resolver
     *
     * @var StoreViewResolver
     */
    private $storeViewResolver;

    /**
     * The storage of url rewrites
     *
     * @var Storage
     */
    private $storage;

    /**
     * The class to work with reports
     *
     * @var Report
     */
    private $report;

    /**
     * @param Storage $storage The storage of url rewrites
     * @param Report $report The class to work with reports
     * @param ValidatorPool $validatorPool The pool of validators
     * @param StoreViewResolver $storeViewResolver The store view resolver
     */
    public function __construct(
        Storage $storage,
        Report $report,
        ValidatorPool $validatorPool,
        StoreViewResolver $storeViewResolver
    ) {
        $this->storage = $storage;
        $this->report = $report;
        $this->validatorPool = $validatorPool;
        $this->storeViewResolver = $storeViewResolver;
    }

    /**
     * Delete url rewrites
     *
     * @param int $operationId The id of operation from the bulk operation list
     * @param array $rows The list of the url rewrites
     * @throws LocalizedException The exception that is thrown if something goes wrong
     */
    public function execute(int $operationId, array $rows = [])
    {
        $validator = $this->validatorPool->getValidator(Import::BEHAVIOR_DELETE);
        $results = [];
        $data = [];

        foreach ($rows as $line => $row) {
            if (!$validator->isValid($row)) {
                $row[Import::COLUMN_MESSAGES] = __('This line is ignored.')
                    .' ' . implode('. ', $validator->getMessages());
                $results[] = $row;
                continue;
            }

            $data[UrlRewriteApi::ENTITY_TYPE] = 'custom';
            $data[UrlRewriteApi::REQUEST_PATH][] = $row[Import::COLUMN_REQUEST_PATH];
            $data[UrlRewriteApi::STORE_ID][] = $this->storeViewResolver->getIdByCode(
                $row[Import::COLUMN_STORE_VIEW_CODE]
            );
        }

        if ($data) {
            $this->storage->delete($data);
        }

        if ($results) {
            $this->report->save($operationId, $results);
            throw new LocalizedException(__('Some URL rewrites were not deleted'));
        }
    }
}
