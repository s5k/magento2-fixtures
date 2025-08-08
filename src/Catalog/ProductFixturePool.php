<?php

declare(strict_types=1);

namespace TddWizard\Fixtures\Catalog;

use Magento\Catalog\Api\Data\ProductInterface;
use function array_values as values;

class ProductFixturePool
{

    /**
     * @var ProductFixture[]
     */
    private $productFixtures = [];

    /**
     * Adds product fixture to the pool
     * 
     * @param ProductInterface $product 
     * @param null|string $key 
     * @return void 
     */
    public function add(ProductInterface $product, ?string $key = null): void
    {
        if ($key === null) {
            $this->productFixtures[] = new ProductFixture($product);
        } else {
            $this->productFixtures[$key] = new ProductFixture($product);
        }
    }

    /**
     * Returns product fixture by key, or last added if key not specified
     *
     * @param int|string|null $key
     * @return ProductFixture
     */
    public function get($key = null): ProductFixture
    {
        if ($key === null) {
            $key = \array_key_last($this->productFixtures);
        }
        if ($key === null || !array_key_exists($key, $this->productFixtures)) {
            throw new \OutOfBoundsException('No matching product found in fixture pool');
        }
        return $this->productFixtures[$key];
    }

    /**
     * Returns all product fixtures
     *
     * @return ProductFixture[]
     */
    public function getItems(): array
    {
        return $this->productFixtures;
    }

    /**
     * Rollback all product fixtures.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function rollback(): void
    {
        ProductFixtureRollback::create()->execute(...values($this->productFixtures));
        $this->productFixtures = [];
    }
}
