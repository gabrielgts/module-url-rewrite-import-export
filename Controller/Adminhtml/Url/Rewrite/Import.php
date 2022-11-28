<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Controller\Adminhtml\Url\Rewrite;

/**
 * The main controller to render the import form
 */
class Import extends \Gtstudio\UrlRewriteImportExport\Controller\Adminhtml\Url\Rewrite
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_UrlRewrite::urlrewrite');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Import URL Rewrites'));
        $this->_view->renderLayout();
    }
}
