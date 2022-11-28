<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Model\Validator;

use Magento\Framework\Validator\AbstractValidator;
use Gtstudio\UrlRewriteImportExport\Model\Import;

/**
 * The validator for value of redirect_type column
 */
class RedirectType extends AbstractValidator
{
    /**
     * Check if a value of redirect_type column is valid
     *
     * @param mixed $value The value to check
     * @return bool Return true if the value is valid otherwise return false
     */
    public function isValid($value): bool
    {
        $this->_clearMessages();

        if (!isset($value[Import::COLUMN_REDIRECT_TYPE])) {
            $this->_addMessages([__('Column %1 is empty', Import::COLUMN_REDIRECT_TYPE_TITLE)]);
            return false;
        }

        if (!in_array($value[Import::COLUMN_REDIRECT_TYPE], ['0', '301', '302'], true)) {
            $this->_addMessages([__('Column %1 has wrong redirect code', Import::COLUMN_REDIRECT_TYPE_TITLE)]);
            return false;
        }

        return true;
    }
}
