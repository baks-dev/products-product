<?php

declare(strict_types=1);

namespace BaksDev\Products\Product\Repository\AllProductsIdentifier\Tests;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group products-product
 */
#[When(env: 'test')]
class AllProductsIdentifierRepositoryTest extends KernelTestCase
{
    private static array|false $data;

    public static function setUpBeforeClass(): void
    {
        /** @var AllProductsIdentifierInterface $AllProductsIdentifier */
        $AllProductsIdentifier = self::getContainer()->get(AllProductsIdentifierInterface::class);
        $result = $AllProductsIdentifier->findAll();

        foreach($result as $data)
        {
            self::assertTrue(isset($data['product_id']));
            self::assertTrue(isset($data['product_event']));
            self::assertTrue(isset($data['offer_id']));
            self::assertTrue(isset($data['offer_const']));
            self::assertTrue(isset($data['variation_id']));
            self::assertTrue(isset($data['variation_const']));
            self::assertTrue(isset($data['modification_id']));
            self::assertTrue(isset($data['modification_const']));

            self::$data = $data;

            break;
        }


        self::assertTrue(true);
    }

    public function testProductCase(): void
    {
        /** @var AllProductsIdentifierInterface $AllProductsIdentifier */
        $AllProductsIdentifier = self::getContainer()->get(AllProductsIdentifierInterface::class);
        $result = $AllProductsIdentifier
            ->forProduct(self::$data['product_id'])
            ->findAll();

        foreach($result as $data)
        {
            self::assertTrue(isset($data['product_id']));
            self::assertEquals(self::$data['product_id'], $data['product_id']);

        }

        self::assertTrue(true);
    }

    public function testOfferCase(): void
    {

        /** @var AllProductsIdentifierInterface $AllProductsIdentifier */
        $AllProductsIdentifier = self::getContainer()->get(AllProductsIdentifierInterface::class);
        $result = $AllProductsIdentifier
            ->forOfferConst(self::$data['offer_const'])
            ->findAll();

        foreach($result as $data)
        {
            self::assertTrue(isset($data['product_id']));
            self::assertEquals(self::$data['product_id'], $data['product_id']);

            self::assertTrue(isset($data['offer_const']));
            self::assertEquals(self::$data['offer_const'], $data['offer_const']);
        }


        self::assertTrue(true);
    }


    public function testVariationCase(): void
    {

        /** @var AllProductsIdentifierInterface $AllProductsIdentifier */
        $AllProductsIdentifier = self::getContainer()->get(AllProductsIdentifierInterface::class);
        $result = $AllProductsIdentifier
            ->forVariationConst(self::$data['variation_const'])
            ->findAll();

        foreach($result as $data)
        {
            self::assertEquals(self::$data['product_id'], $data['product_id']);
            self::assertEquals(self::$data['offer_const'], $data['offer_const']);
            self::assertEquals(self::$data['variation_const'], $data['variation_const']);
        }


        self::assertTrue(true);
    }


    public function testModificationCase(): void
    {

        /** @var AllProductsIdentifierInterface $AllProductsIdentifier */
        $AllProductsIdentifier = self::getContainer()->get(AllProductsIdentifierInterface::class);
        $result = $AllProductsIdentifier
            ->forModificationConst(self::$data['modification_const'])
            ->findAll();

        foreach($result as $data)
        {
            self::assertEquals(self::$data['product_id'], $data['product_id']);
            self::assertEquals(self::$data['offer_const'], $data['offer_const']);
            self::assertEquals(self::$data['variation_const'], $data['variation_const']);
            self::assertEquals(self::$data['modification_const'], $data['modification_const']);
        }


        self::assertTrue(true);
    }

}
