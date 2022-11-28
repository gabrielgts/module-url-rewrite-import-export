<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Ui\Component\Listing\Columns\Column;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * The column with link and id of bulk operation
 */
class Link extends Column
{
    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        $dataSource = parent::prepareDataSource($dataSource);

        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            if (!empty($item['link'])) {
                $item['link'] = $this->context->getUrl($item['link']);
            }
        }

        return $dataSource;
    }
}
