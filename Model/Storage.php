<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Model;

use Magento\UrlRewrite\Model\StorageInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewriteApi;
use Magento\UrlRewrite\Model\UrlRewrite;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewrite as UrlRewriteResourceModel;
use Magento\Framework\Exception\LocalizedException;

/**
 * The storage of url rewrites
 */
class Storage
{
    /**
     * The storage url rewrites from UrlRewrite module
     *
     * @var StorageInterface
     */
    private $storage;

    /**
     * The factory for @see UrlRewrite
     *
     * @var UrlRewriteFactory
     */
    private $urlRewriteFactory;

    /**
     * The UrlRewrite Resource Model
     *
     * @var UrlRewriteResourceModel
     */
    private $urlRewriteResourceModel;

    /**
     * @param StorageInterface $storage The storage url rewrites from UrlRewrite module
     * @param UrlRewriteFactory $urlRewriteFactory The factory for @see UrlRewrite
     * @param UrlRewriteResourceModel $urlRewriteResourceModel The UrlRewrite Resource Model
     */
    public function __construct(
        StorageInterface $storage,
        UrlRewriteFactory $urlRewriteFactory,
        UrlRewriteResourceModel $urlRewriteResourceModel
    ) {
        $this->storage = $storage;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->urlRewriteResourceModel = $urlRewriteResourceModel;
    }

    /**
     * Delete url rewrites
     *
     * @param array $data The list of rows to delete
     * @return void
     */
    public function delete(array $data)
    {
        $this->storage->deleteByData($data);
    }

    /**
     * Insert or update url rewrites
     *
     * @param array $data The list of rows to insert or update
     * @return void
     * @throws LocalizedException The exception that is thrown if there is some problem during insert/update data
     */
    public function insertUpdate(array $data)
    {
        /** @var UrlRewrite $urlRewriteModel */
        $urlRewriteModel = $this->urlRewriteFactory->create();
        $urlRewriteModel->setData($data);

        /** @var UrlRewriteApi $existedUrlRewrite */
        $existedUrlRewrite = $this->storage->findOneByData([
            UrlRewriteApi::REQUEST_PATH => $data[UrlRewriteApi::REQUEST_PATH],
            UrlRewriteApi::STORE_ID => $data[UrlRewriteApi::STORE_ID],
        ]);

        if ($existedUrlRewrite) {
            $urlRewriteModel->setId($existedUrlRewrite->getUrlRewriteId());
        }

        try {
            $this->urlRewriteResourceModel->save($urlRewriteModel);
        } catch (\Exception $e) {
            throw new LocalizedException(__('Cannot insert or update an URL rewrite'), $e);
        }
    }
}
