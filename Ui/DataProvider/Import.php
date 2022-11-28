<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Ui\DataProvider;

/**
 * The mock of data provider.
 * UI component form requires data provider to provides data.
 * But form for import url rewrites only send csv file to import, so we create this mock.
 */
class Import extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @inheritdoc
     */
    public function getData()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
    }
}
