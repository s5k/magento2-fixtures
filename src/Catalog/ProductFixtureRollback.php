<?php

declare(strict_types=1);

namespace TddWizard\Fixtures\Catalog;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Registry;
use Magento\Framework\Filesystem;
use Magento\TestFramework\Helper\Bootstrap;
use RuntimeException;

/**
 * @internal Use ProductFixture::rollback() or ProductFixturePool::rollback() instead
 */
class ProductFixtureRollback
{
    /**
     * @var Registry
     */
    private Registry $registry;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @param Registry $registry 
     * @param ProductRepositoryInterface $productRepository 
     * @param Filesystem $filesystem 
     */
    public function __construct(
        Registry $registry,
        ProductRepositoryInterface $productRepository,
        Filesystem $filesystem
    ) {
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        $this->filesystem = $filesystem;
    }

    /**
     * Factory method to create an instance of ProductFixtureRollback
     * @return ProductFixtureRollback 
     */
    public static function create(): ProductFixtureRollback
    {
        $objectManager = Bootstrap::getObjectManager();
        return new self(
            $objectManager->get(Registry::class),
            $objectManager->get(ProductRepositoryInterface::class),
            $objectManager->get(Filesystem::class)
        );
    }

    /**
     * Rollback method to delete products and their media gallery images.
     * 
     * @param ProductFixture ...$productFixtures 
     * @return void 
     * @throws RuntimeException 
     * @throws FileSystemException 
     */
    public function execute(ProductFixture ...$productFixtures): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        $mediaDir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);

        foreach ($productFixtures as $productFixture) {
            try {
                $product = $productFixture->getProduct();

                // Delete product's media gallery images from filesystem
                $mediaGalleryEntries = $product->getMediaGalleryEntries();
                if ($mediaGalleryEntries) {
                    foreach ($mediaGalleryEntries as $entry) {
                        $file = ltrim($entry->getFile(), '/');
                        $path = 'catalog/product/' . $file;
                        if ($mediaDir->isExist($path)) {
                            $mediaDir->delete($path);
                        }
                    }
                }

                $this->productRepository->deleteById($productFixture->getSku());
            } catch (\Exception) {
                // this is fine, products has already been removed
            }
        }

        $this->registry->unregister('isSecureArea');
    }
}
