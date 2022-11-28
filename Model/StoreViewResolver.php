<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Model;

use Magento\Store\Api\StoreRepositoryInterface;

/**
 * The store view resolver
 */
class StoreViewResolver
{
    /**
     * The store repository
     *
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * The mapping of codes and ids stores
     *
     * E.g. ['first_code' => 6, 'second_code' => 9]
     *
     * @var array
     */
    private $stores;

    /**
     * @param StoreRepositoryInterface $storeRepository The store repository
     */
    public function __construct(StoreRepositoryInterface $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    /**
     * Get store id by its code
     *
     * @param string $code The code of store
     * @return int|null The id of store
     */
    public function getIdByCode(string $code)
    {
        if (null === $this->stores) {
            foreach ($this->storeRepository->getList() as $store) {
                $this->stores[$store->getCode()] = $store->getId();
            }
        }

        return $this->stores[$code] ?? null;
    }
}
