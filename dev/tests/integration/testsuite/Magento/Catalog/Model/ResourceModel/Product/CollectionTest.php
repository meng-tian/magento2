<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $processor;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class
        );

        $this->processor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Indexer\Product\Price\Processor::class
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoAppIsolation enabled
     */
    public function testAddPriceDataOnSchedule()
    {
        $this->processor->getIndexer()->setScheduled(true);
        $this->assertTrue($this->processor->getIndexer()->isScheduled());
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $productRepository->get('simple');
        $this->assertEquals(10, $product->getPrice());
        $product->setPrice(15);
        $productRepository->save($product);
        $this->collection->addPriceData(0, 1);
        $this->collection->load();
        /** @var \Magento\Catalog\Api\Data\ProductInterface[] $product */
        $items = $this->collection->getItems();
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = reset($items);
        $this->assertCount(2, $items);
        $this->assertEquals(10, $product->getPrice());

        //reindexing
        $this->processor->getIndexer()->reindexList([1]);

        $this->collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class
        );
        $this->collection->addPriceData(0, 1);
        $this->collection->load();

        /** @var \Magento\Catalog\Api\Data\ProductInterface[] $product */
        $items = $this->collection->getItems();
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = reset($items);
        $this->assertCount(2, $items);
        $this->assertEquals(15, $product->getPrice());
        $this->processor->getIndexer()->reindexList([1]);

        $this->processor->getIndexer()->setScheduled(false);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoAppIsolation enabled
     */
    public function testAddPriceDataOnSave()
    {
        $this->processor->getIndexer()->setScheduled(false);
        $this->assertFalse($this->processor->getIndexer()->isScheduled());
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $productRepository->get('simple');
        $this->assertNotEquals(15, $product->getPrice());
        $product->setPrice(15);
        $productRepository->save($product);
        $this->collection->addPriceData(0, 1);
        $this->collection->load();
        /** @var \Magento\Catalog\Api\Data\ProductInterface[] $product */
        $items = $this->collection->getItems();
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = reset($items);
        $this->assertCount(2, $items);
        $this->assertEquals(15, $product->getPrice());
    }

    public function testAddWebsitesFilter()
    {
        $this->collection->addWebsitesFilter(['in' => '1', 'eq' => '2']);
        $whereParts = $this->collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);
        self::assertCount(1, $whereParts);
        $websiteSelect = '/e.entity_id IN\s*\(+SELECT\s+`web`\.`product_id`\s+FROM\s+`catalog_product_website`\s+AS\s+`web`\s+WHERE/';
        self::assertRegExp($websiteSelect, $whereParts[0]);
        self::assertRegExp('/web\.website_id\s+IN\s?\(\'1\'\)+\s+OR\s+\(web\.website_id\s+=\s+\'2\'\)+/', $whereParts[0]);

        $this->collection->addWebsitesFilter(['nin' => '3,4']);
        $whereParts = $this->collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);
        self::assertCount(2, $whereParts);
        $andWhere = '/AND\s+\(+' . substr($websiteSelect, 1);
        self::assertRegExp($andWhere, $whereParts[1]);
        self::assertRegExp('/web\.website_id\s+NOT\sIN\s*\(\'3\',\s*\'4\'\)+/', $whereParts[1]);
    }
}
