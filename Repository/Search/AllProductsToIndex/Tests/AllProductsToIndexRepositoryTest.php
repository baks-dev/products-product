<?php

namespace BaksDev\Products\Product\Repository\Search\AllProductsToIndex\Tests;
use \BaksDev\Search\Repository\DataToIndex\DataToIndexInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group products-search
 */
class AllProductsToIndexRepositoryTest extends KernelTestCase
{
    public function testAllProductsToIndex()
    {
        /** @var DataToIndexInterface $repository */
        $repository = self::getContainer()->get(DataToIndexInterface::class);

        $result = $repository->toArray();

//        dd($result);

        self::assertTrue(true);
    }
}