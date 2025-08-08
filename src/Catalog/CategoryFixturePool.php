<?php

declare(strict_types=1);

namespace TddWizard\Fixtures\Catalog;

use Magento\Catalog\Api\Data\CategoryInterface;
use function array_values as values;

class CategoryFixturePool
{

    /**
     * @var CategoryFixture[]
     */
    private $categoryFixtures = [];

    /**
     * Adds category fixture to the pool 
     * 
     * @param CategoryInterface $category 
     * @param null|string $key 
     * @return void 
     */
    public function add(CategoryInterface $category, ?string $key = null): void
    {
        if ($key === null) {
            $this->categoryFixtures[] = new CategoryFixture($category);
        } else {
            $this->categoryFixtures[$key] = new CategoryFixture($category);
        }
    }

    /**
     * Returns category fixture by key, or last added if key not specified
     *
     * @param int|string|null $key
     * @return CategoryFixture
     */
    public function get($key = null): CategoryFixture
    {
        if ($key === null) {
            $key = \array_key_last($this->categoryFixtures);
        }
        if ($key === null || !array_key_exists($key, $this->categoryFixtures)) {
            throw new \OutOfBoundsException('No matching category found in fixture pool');
        }
        return $this->categoryFixtures[$key];
    }

    /**
     * Returns all category fixtures
     *
     * @return CategoryFixture[]
     */
    public function getItems(): array
    {
        return $this->categoryFixtures;
    }

    /**
     * Rollback all category fixtures.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function rollback(): void
    {
        CategoryFixtureRollback::create()->execute(...values($this->categoryFixtures));
        $this->categoryFixtures = [];
    }
}
