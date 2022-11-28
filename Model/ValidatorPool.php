<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Model;

use Magento\Framework\ValidatorFactory;
use Magento\Framework\Validator;
use Magento\Framework\Exception\ConfigurationMismatchException;

/**
 * The pool of validators
 */
class ValidatorPool
{
    /**
     * The list of validators grouped by behaviors
     *
     * @var array
     */
    private $validators = [];

    /**
     * The factory for @see Validator
     *
     * @var ValidatorFactory
     */
    private $validatorFactory;

    /**
     * @param ValidatorFactory $validatorFactory The factory for @see Validator
     * @param array $validators The list of validators grouped by behaviors
     */
    public function __construct(
        ValidatorFactory $validatorFactory,
        array $validators
    ) {
        $this->validatorFactory = $validatorFactory;
        $this->validators = $validators;
    }

    /**
     * Get grouped validators for behavior
     *
     * @param string $behavior The behavior name
     * @return Validator The validator that encapsulates the list of validators for the behavior
     * @throws ConfigurationMismatchException The exception that is thrown if the list of validators
     *         is not configured for the behavior
     */
    public function getValidator(string $behavior): Validator
    {
        if (empty($this->validators[$behavior])) {
            throw new ConfigurationMismatchException(__('There are no validators for %1 behavior', $behavior));
        }

        $validator = $this->validatorFactory->create();
        foreach ($this->validators[$behavior] as $validatorObj) {
            $validator->addValidator($validatorObj);
        }

        return $validator;
    }
}
