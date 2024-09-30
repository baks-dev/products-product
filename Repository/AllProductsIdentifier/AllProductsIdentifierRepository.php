<?php

declare(strict_types=1);

namespace BaksDev\Products\Product\Repository\AllProductsIdentifier;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use Generator;

final class AllProductsIdentifierRepository implements AllProductsIdentifierInterface
{
    private ProductUid|false $product = false;

    private ProductOfferConst|false $offerConst = false;

    private ProductVariationConst|false $offerVariation = false;

    private ProductModificationConst|false $offerModification = false;

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    public function forProduct(Product|ProductUid|string $product): self
    {
        if(is_string($product))
        {
            $product = new ProductUid($product);
        }

        if($product instanceof Product)
        {
            $product = $product->getId();
        }

        $this->product = $product;

        return $this;
    }

    public function forOfferConst(ProductOfferConst|string $offerConst): self
    {
        if(is_string($offerConst))
        {
            $offerConst = new ProductOfferConst($offerConst);
        }

        $this->offerConst = $offerConst;

        return $this;
    }

    public function forVariationConst(ProductVariationConst|string $offerVariation): self
    {
        if(is_string($offerVariation))
        {
            $offerVariation = new ProductVariationConst($offerVariation);
        }

        $this->offerVariation = $offerVariation;

        return $this;
    }

    public function forModificationConst(ProductModificationConst|string $offerModification): self
    {
        if(is_string($offerModification))
        {
            $offerModification = new ProductModificationConst($offerModification);
        }

        $this->offerModification = $offerModification;

        return $this;
    }


    /**
     * Метод возвращает все идентификаторы продукции с её торговыми предложениями
     */
    public function findAll(): Generator|false
    {
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->select('product.id AS product_id')
            ->addSelect('product.event AS product_event')
            ->from(Product::class, 'product');

        if($this->product)
        {
            $dbal
                ->where('product.id = :product')
                ->setParameter(
                    'product',
                    $this->product,
                    ProductUid::TYPE
                );
        }


        $dbal
            ->addSelect('offer.id AS offer_id')
            ->addSelect('offer.const AS offer_const');

        if($this->offerConst)
        {
            $dbal->join(
                'product',
                ProductOffer::class,
                'offer',
                'offer.event = product.event AND offer.const = :offer_const'
            )
                ->setParameter(
                    'offer_const',
                    $this->offerConst,
                    ProductOfferConst::TYPE
                );
        }
        else
        {
            $dbal->leftJoin(
                'product',
                ProductOffer::class,
                'offer',
                'offer.event = product.event'
            );
        }


        $dbal
            ->addSelect('variation.id AS variation_id')
            ->addSelect('variation.const AS variation_const');

        if($this->offerVariation)
        {
            $dbal->join(
                'offer',
                ProductVariation::class,
                'variation',
                'variation.offer = offer.id AND variation.const = :variation_const'
            )
                ->setParameter(
                    'variation_const',
                    $this->offerVariation,
                    ProductVariationConst::TYPE
                );
        }
        else
        {
            $dbal
                ->leftJoin(
                    'offer',
                    ProductVariation::class,
                    'variation',
                    'variation.offer = offer.id'
                );
        }

        $dbal
            ->addSelect('modification.id AS modification_id')
            ->addSelect('modification.const AS modification_const');

        if($this->offerModification)
        {
            $dbal
                ->join(
                    'variation',
                    ProductModification::class,
                    'modification',
                    'modification.variation = variation.id AND modification.const = :modification_const'
                )
                ->setParameter(
                    'modification_const',
                    $this->offerModification,
                    ProductModificationConst::TYPE
                );
        }
        else
        {
            $dbal
                ->leftJoin(
                    'variation',
                    ProductModification::class,
                    'modification',
                    'modification.variation = variation.id'
                );
        }

        return $dbal->fetchAllGenerator();
    }
}
