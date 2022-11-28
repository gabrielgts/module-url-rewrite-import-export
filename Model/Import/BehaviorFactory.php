<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Model\Import;

use Gtstudio\UrlRewriteImportExport\Model\Import;
use Magento\Framework\ObjectManagerInterface;
use Gtstudio\UrlRewriteImportExport\Model\Import\Behavior;
use Magento\Framework\Exception\RuntimeException;

/**
 * The factory to create the behavior instance
 */
class BehaviorFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager Object Manager instance
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create instance of behavior
     *
     * @param string $behavior The behavior name
     * @return BehaviorInterface The instance of behavior
     * @throws RuntimeException The exception that is thrown if incorrect behavior name
     */
    public function create(string $behavior): BehaviorInterface
    {
        switch ($behavior) {
            case Import::BEHAVIOR_ADD_UPDATE:
                return $this->objectManager->create(Behavior\AddUpdate::class);
            case Import::BEHAVIOR_DELETE:
                return $this->objectManager->create(Behavior\Delete::class);
            default:
                throw new RuntimeException(__('Wrong behavior'));
        }
    }
}
