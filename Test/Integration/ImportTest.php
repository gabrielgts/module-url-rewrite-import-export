<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\UrlRewriteImportExport\Test\Integration;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Gtstudio\UrlRewriteImportExport\Controller\Adminhtml\Url\Rewrite\FileUploader\Save;
use Gtstudio\UrlRewriteImportExport\Controller\Adminhtml\Url\Rewrite\Import\Run;
use Gtstudio\UrlRewriteImportExport\Model\File\ImportDirectory;
use Gtstudio\UrlRewriteImportExport\Model\Import;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewriteApi;
use Gtstudio\UrlRewriteImportExport\Model\StoreViewResolver;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection;
use Magento\MessageQueue\Console\StartConsumerCommand;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class ImportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->fileSystem = $this->objectManager->get(Filesystem::class);

        parent::setUp();
    }

    /**
     * Test import flow
     */
    public function testImportFlow()
    {
        // Test adding new url rewrites
        $this->initFiles('add_delete.csv');
        $file = $this->saveFileToImport();
        $this->scheduleBulkOperations($file, Import::BEHAVIOR_ADD_UPDATE);
        $this->startConsumer();

        $collection = $this->getCollection();

        $this->assertEquals(3, $collection->count());

        //Test updating url rewrites
        $oldUrlRewrites = [];
        foreach ($collection->getData() as $data) {
            $oldUrlRewrites[$data[UrlRewriteApi::REQUEST_PATH]] = $data;
        }

        $this->initFiles('update.csv');
        $file = $this->saveFileToImport();
        $this->scheduleBulkOperations($file, Import::BEHAVIOR_ADD_UPDATE);
        $this->startConsumer();
        $collection = $this->getCollection();
        $this->assertEquals(3, $collection->count());

        $newUrlRewrites = [];
        foreach ($collection->getData() as $data) {
            $newUrlRewrites[$data[UrlRewriteApi::REQUEST_PATH]] = $data;
        }

        $this->assertNotEquals($oldUrlRewrites['request-first'], $newUrlRewrites['request-first']);
        $this->assertEquals($oldUrlRewrites['request-second'], $newUrlRewrites['request-second']);
        $this->assertEquals($oldUrlRewrites['request-third'], $newUrlRewrites['request-third']);

        // Test deleting url rewrites
        $this->initFiles('add_delete.csv');
        $file = $this->saveFileToImport();
        $this->scheduleBulkOperations($file, Import::BEHAVIOR_DELETE);
        $this->startConsumer();
        $collection = $this->getCollection();
        $this->assertEquals(0, $collection->count());
    }

    /**
     * Get UrlRewriteCollection
     *
     * @return UrlRewriteCollection
     */
    private function getCollection(): UrlRewriteCollection
    {
        /** @var StoreViewResolver $storeViewResolver */
        $storeViewResolver = $this->objectManager->create(StoreViewResolver::class);
        /** @var UrlRewriteCollection $collection */
        $collection = $this->objectManager->create(UrlRewriteCollection::class);
        $collection->addFilter(UrlRewriteApi::ENTITY_TYPE, 'custom')
            ->addFilter(UrlRewriteApi::STORE_ID, $storeViewResolver->getIdByCode('default'));

        return $collection;
    }

    /**
     * Run consumer to import url rewrites
     * Test @see \Gtstudio\UrlRewriteImportExport\Model\Consumer
     * @return void
     */
    private function startConsumer()
    {
        $command = $this->objectManager->create(StartConsumerCommand::class);
        $commandTester = new \Symfony\Component\Console\Tester\CommandTester($command);
        $result = $commandTester->execute([
            'consumer' => 'urlRewriteImport',
            '--' . StartConsumerCommand::OPTION_NUMBER_OF_MESSAGES => '1',
        ]);

        $this->assertEquals(0, $result);
    }

    /**
     * Schedule a bulk operation
     * Test @see Run controller
     *
     * @param string $fileName
     * @param string $behavior
     * @return void
     */
    private function scheduleBulkOperations($fileName, $behavior)
    {
        /** @var RequestInterface|MockObject $requestMock */
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['import_file', null, [['file'=>$fileName]]],
                ['behavior', null, $behavior],
            ]);

        /** @var ManagerInterface|MockObject $messageManager */
        $messageManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $messageManager->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('Operations of import was scheduled...'));
        $messageManager->expects($this->never())
            ->method('addErrorMessage');
        $context = $this->objectManager->create(
            \Magento\Backend\App\Action\Context::class,
            [
                'request' => $requestMock,
                'messageManager' => $messageManager,
            ]
        );
        /** @var Run $runController */
        $runController = $this->objectManager->create(Run::class, ['context' => $context]);
        $runController->execute();
    }

    /**
     * Save file from tmp dir to the destination
     * Test @see Save controller
     *
     * @return string The file name
     */
    private function saveFileToImport(): string
    {
        /** @var Save $saveController */
        $saveController = $this->objectManager->create(Save::class);
        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->objectManager->create(\Magento\Framework\App\Response\Http::class);
        $saveController->execute()
            ->renderResult($response);
        $resultArray = json_decode($response->getBody(), true);

        $this->assertArrayHasKey('file', $resultArray);
        $this->assertTrue(
            $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)
                ->isExist(ImportDirectory::URL_REWRITE_IMPORT_DIR . '/' . $resultArray['file'])
        );

        return $resultArray['file'];
    }

    /**
     * Init global array $_FILES with data to test
     *
     * @param string $fileName
     * @return void
     */
    private function initFiles($fileName)
    {
        $tmp = sys_get_temp_dir();
        $filePath = $tmp . '/' . $fileName;
        copy(__DIR__ . '/_files/' . $fileName, $filePath);

        $_FILES = [
            'import_file' => [
                'name' => $fileName,
                'type' => 'text/csv',
                'tmp_name' => $filePath,
                'error' => 0,
                'size' => filesize($filePath),
            ],
        ];
    }
}
