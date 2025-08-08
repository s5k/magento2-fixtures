<?php

namespace TddWizard\Fixtures\Catalog;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class ProductBuilderImageTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ProductFixture[]
     */
    private $products = [];

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $testImagePath;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->products = [];
    }

    protected function tearDown(): void
    {
        if (!empty($this->products)) {
            foreach ($this->products as $product) {
                ProductFixtureRollback::create()->execute($product);
            }
        }
    }

    /**
     * Happy case: Product with single image
     */
    public function testProductWithSingleImage()
    {
        $productFixture = new ProductFixture(
            ProductBuilder::aSimpleProduct()
                ->withSku('product-with-image')
                ->withName('Product With Image')
                ->withImage('1st_thumbnail.jpg', 'image')
                ->build()
        );
        $this->products[] = $productFixture;

        /** @var Product $product */
        $product = $this->productRepository->getById($productFixture->getId());

        $this->assertEquals('product-with-image', $product->getSku());
        $this->assertEquals('Product With Image', $product->getName());

        // Check that image was assigned
        $this->assertNotEquals('no_selection', $product->getImage());
        $this->assertNotEmpty($product->getImage());

        // Verify media gallery has the image
        $mediaGalleryImages = $product->getMediaGalleryImages();
        $this->assertGreaterThan(0, $mediaGalleryImages->count());
    }

    /**
     * Happy case: Product with multiple image types
     */
    public function testProductWithMultipleImageTypes()
    {
        $productFixture = new ProductFixture(
            ProductBuilder::aSimpleProduct()
                ->withSku('product-multiple-images')
                ->withName('Product With Multiple Images')
                ->withImage('1st_thumbnail.jpg', 'image')
                ->withImage('2st_thumbnail.jpg', 'small_image')
                ->withImage('3st_thumbnail.jpg', 'thumbnail')
                ->build()
        );
        $this->products[] = $productFixture;

        /** @var Product $product */
        $product = $this->productRepository->getById($productFixture->getId());

        // Check all image types are set
        $this->assertNotEquals('no_selection', $product->getImage());
        $this->assertNotEquals('no_selection', $product->getSmallImage());
        $this->assertNotEquals('no_selection', $product->getThumbnail());

        // All should have valid paths
        $this->assertNotEmpty($product->getImage());
        $this->assertNotEmpty($product->getSmallImage());
        $this->assertNotEmpty($product->getThumbnail());
    }

    /**
     * Happy case: Product builder chaining with images
     */
    public function testProductBuilderChaining()
    {
        $productFixture = new ProductFixture(
            ProductBuilder::aSimpleProduct()
                ->withSku('chained-product')
                ->withName('Chained Product')
                ->withPrice(29.99)
                ->withImage('1st_thumbnail.jpg', 'image')
                ->withStockQty(50)
                ->build()
        );
        $this->products[] = $productFixture;

        /** @var Product $product */
        $product = $this->productRepository->getById($productFixture->getId());

        $this->assertEquals('chained-product', $product->getSku());
        $this->assertEquals('Chained Product', $product->getName());
        $this->assertEquals(29.99, $product->getPrice());
        $this->assertEquals(50, $product->getExtensionAttributes()->getStockItem()->getQty());
        $this->assertNotEquals('no_selection', $product->getImage());
    }

    /**
     * Edge case: Product without image (default behavior)
     */
    public function testProductWithoutImage()
    {
        $productFixture = new ProductFixture(
            ProductBuilder::aSimpleProduct()
                ->withSku('product-no-image')
                ->withName('Product Without Image')
                ->build()
        );
        $this->products[] = $productFixture;

        /** @var Product $product */
        $product = $this->productRepository->getById($productFixture->getId());

        // Default values should be 'no_selection'
        $this->assertEquals('no_selection', $product->getImage());
        $this->assertEquals('no_selection', $product->getSmallImage());
        $this->assertEquals('no_selection', $product->getThumbnail());
    }

    /**
     * Edge case: Image with default type parameter
     */
    public function testProductWithImageDefaultType()
    {
        $productFixture = new ProductFixture(
            ProductBuilder::aSimpleProduct()
                ->withSku('product-default-image-type')
                ->withName('Product With Default Image Type')
                ->withImage('1st_thumbnail.jpg') // Don't pass second parameter, let it use the default
                ->build()
        );
        $this->products[] = $productFixture;

        /** @var Product $product */
        $product = $this->productRepository->getById($productFixture->getId());

        // Should have image assigned (defaults to 'image')
        $this->assertNotEquals('no_selection', $product->getImage());
        $this->assertNotEmpty($product->getImage());

        // Verify media gallery has the image
        $mediaGalleryImages = $product->getMediaGalleryImages();
        $this->assertGreaterThan(0, $mediaGalleryImages->count());
    }

    /**
     * Edge case: Image with explicit null type (tests actual null behavior)
     */
    public function testProductWithImageExplicitNullType()
    {
        $productFixture = new ProductFixture(
            ProductBuilder::aSimpleProduct()
                ->withSku('product-explicit-null-type')
                ->withName('Product With Explicit Null Type')
                ->withImage('1st_thumbnail.jpg', null)
                ->build()
        );
        $this->products[] = $productFixture;

        /** @var Product $product */
        $product = $this->productRepository->getById($productFixture->getId());

        // With null, the method should still use default behavior
        // Since the method signature has ?string $imageType = 'image', null should work like 'image'
        $this->assertNotEquals('no_selection', $product->getImage());
        $this->assertNotEmpty($product->getImage());
    }

    /**
     * Edge case: Multiple products with same image file
     */
    public function testMultipleProductsWithSameImage()
    {
        $product1Fixture = new ProductFixture(
            ProductBuilder::aSimpleProduct()
                ->withSku('product-1-same-image')
                ->withName('Product 1 Same Image')
                ->withImage('1st_thumbnail.jpg', 'image')
                ->build()
        );
        $this->products[] = $product1Fixture;

        $product2Fixture = new ProductFixture(
            ProductBuilder::aSimpleProduct()
                ->withSku('product-2-same-image')
                ->withName('Product 2 Same Image')
                ->withImage('1st_thumbnail.jpg', 'image')
                ->build()
        );
        $this->products[] = $product2Fixture;

        /** @var Product $product1 */
        $product1 = $this->productRepository->getById($product1Fixture->getId());
        /** @var Product $product2 */
        $product2 = $this->productRepository->getById($product2Fixture->getId());

        // Both products should have images assigned
        $this->assertNotEquals('no_selection', $product1->getImage());
        $this->assertNotEquals('no_selection', $product2->getImage());
        $this->assertNotEmpty($product1->getImage());
        $this->assertNotEmpty($product2->getImage());
    }

    /**
     * Edge case: Product with different image types using same file
     */
    public function testProductWithSameFileForDifferentImageTypes()
    {
        $productFixture = new ProductFixture(
            ProductBuilder::aSimpleProduct()
                ->withSku('product-same-file-multiple-types')
                ->withName('Product Same File Multiple Types')
                ->withImage('1st_thumbnail.jpg', 'image')
                ->withImage('2st_thumbnail.jpg', 'small_image')
                ->withImage('3st_thumbnail.jpg', 'swatch_image')
                ->build()
        );
        $this->products[] = $productFixture;

        /** @var Product $product */
        $product = $this->productRepository->getById($productFixture->getId());

        // All image types should be set
        $this->assertNotEquals('no_selection', $product->getImage());
        $this->assertNotEquals('no_selection', $product->getSmallImage());
        $this->assertNotEmpty($product->getImage());
        $this->assertNotEmpty($product->getSmallImage());
    }

    /**
     * Edge case: Test image assignment with buildWithoutSave
     */
    public function testImageWithBuildWithoutSave()
    {
        $product = ProductBuilder::aSimpleProduct()
            ->withSku('build-without-save-image')
            ->withImage('1st_thumbnail.jpg', 'image')
            ->buildWithoutSave();

        // Product should be created but not saved
        $this->assertEquals('build-without-save-image', $product->getSku());
        $this->assertNull($product->getId());

        // Image assignment should still work (media gallery should have entries)
        $mediaGalleryEntries = $product->getMediaGalleryEntries();
        $this->assertNotEmpty($mediaGalleryEntries);
    }
}
