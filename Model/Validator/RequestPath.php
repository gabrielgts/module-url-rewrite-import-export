<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Model\Validator;

use Magento\Framework\Validator\AbstractValidator;
use Gtstudio\UrlRewriteImportExport\Model\Import;
use Magento\UrlRewrite\Helper\UrlRewrite as UrlRewriteHelper;
use Magento\Framework\Exception\LocalizedException;

/**
 * The validator for value of request_path column
 */
class RequestPath extends AbstractValidator
{
    /**
     * The url validator
     *
     * @var UrlRewriteHelper
     */
    private $urlRewriteHelper;

    /**
     * @param UrlRewriteHelper $urlRewriteHelper The url validator
     */
    public function __construct(UrlRewriteHelper $urlRewriteHelper)
    {
        $this->urlRewriteHelper = $urlRewriteHelper;
    }

    /**
     * Check if a value of request_path column is valid
     *
     * @param mixed $value The value to check
     * @return bool Return true if the value is valid otherwise return false
     */
    public function isValid($value): bool
    {
        $this->_clearMessages();

        if (empty($value[Import::COLUMN_REQUEST_PATH])) {
            $this->_addMessages([__('Column %1 is empty', Import::COLUMN_REQUEST_PATH_TITLE)]);
            return false;
        }

        try {
            return $this->urlRewriteHelper->validateRequestPath($value[Import::COLUMN_REQUEST_PATH]);
        } catch (LocalizedException $e) {
            $this->_addMessages([$e->getMessage()]);
            return false;
        }
    }
}
