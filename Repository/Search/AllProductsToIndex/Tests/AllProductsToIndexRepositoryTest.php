<?php

namespace BaksDev\Products\Product\Repository\Search\AllProductsToIndex\Tests;
use BaksDev\Article\Repository\AllArticleToIndex\AllArticleToIndexInterface;
use BaksDev\Products\Product\Repository\Search\AllProductsToIndex\AllProductsToIndexInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group products-search
 */
class AllProductsToIndexRepositoryTest extends KernelTestCase
{
    public function testAllProductsToIndex()
    {
        /** @var AllProductsToIndexInterface $repository */
        $repository = self::getContainer()->get(AllProductsToIndexInterface::class);

        $result = $repository->toArray();

//        dd($result);

        self::assertTrue(true);
    }
}