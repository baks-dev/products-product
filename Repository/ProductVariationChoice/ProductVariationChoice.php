<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Products\Product\Repository\ProductVariationChoice;

use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Products\Category\Entity as CategoryEntity;
use BaksDev\Products\Product\Entity as ProductEntity;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ProductVariationChoice implements ProductVariationChoiceInterface
{
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;

    public function __construct(
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ) {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * Метод возвращает все постоянные идентификаторы CONST множественных вариантов торговых предложений продукта
     */
    public function fetchProductVariationByOfferConst(ProductOfferConst $const): ?array
    {
        $qb = $this->entityManager->createQueryBuilder();

        $select = sprintf('new %s(
            variation.const, 
            variation.value, 
            trans.name, 
            category_variation.reference
        )', ProductVariationConst::class);

        $qb->select($select);

        $qb->from(ProductEntity\Offers\ProductOffer::class, 'offer');


        $qb->join(
            ProductEntity\Product::class,
            'product',
            'WITH',
            'product.event = offer.event'
        );



        $qb->join(
            ProductEntity\Offers\Variation\ProductOfferVariation::class,
            'variation',
            'WITH',
            'variation.offer = offer.id'
        );

        // Тип торгового предложения

        $qb->join(
            CategoryEntity\Offers\Variation\ProductCategoryOffersVariation::class,
            'category_variation',
            'WITH',
            'category_variation.id = variation.categoryVariation'
        );

        $qb->leftJoin(
            CategoryEntity\Offers\Variation\Trans\ProductCategoryOffersVariationTrans::class,
            'trans',
            'WITH',
            'trans.variation = category_variation.id AND trans.local = :local'
        );

        $qb->where('offer.const = :const');

//        $qb->setParameter('product', $product, ProductUid::TYPE);
//        $qb->setParameter('local', new Locale($this->translator->getLocale()), Locale::TYPE);
//
//        dd($qb->getQuery()->getResult());
//

        $cacheQueries = new FilesystemAdapter('Product');

        $query = $this->entityManager->createQuery($qb->getDQL());
        $query->setQueryCache($cacheQueries);
        $query->setResultCache($cacheQueries);
        $query->enableResultCache();
        $query->setLifetime(60 * 60 * 24);

        $query->setParameter('const', $const, ProductOfferConst::TYPE);
        $query->setParameter('local', new Locale($this->translator->getLocale()), Locale::TYPE);

        return $query->getResult();
    }


    public function fetchProductVariationByOffer(ProductOfferUid $offer): ?array
    {
        $qb = $this->entityManager->createQueryBuilder();

        $select = sprintf('new %s(
            variation.id, 
            variation.value, 
            trans.name, 
            category_variation.reference
        )', ProductVariationUid::class);

        $qb->select($select);

        $qb->from(ProductEntity\Offers\ProductOffer::class, 'offer');


        $qb->join(
            ProductEntity\Product::class,
            'product',
            'WITH',
            'product.event = offer.event'
        );



        $qb->join(
            ProductEntity\Offers\Variation\ProductOfferVariation::class,
            'variation',
            'WITH',
            'variation.offer = offer.id'
        );

        // Тип торгового предложения

        $qb->join(
            CategoryEntity\Offers\Variation\ProductCategoryOffersVariation::class,
            'category_variation',
            'WITH',
            'category_variation.id = variation.categoryVariation'
        );

        $qb->leftJoin(
            CategoryEntity\Offers\Variation\Trans\ProductCategoryOffersVariationTrans::class,
            'trans',
            'WITH',
            'trans.variation = category_variation.id AND trans.local = :local'
        );

        $qb->where('offer.id = :offer');

        //        $qb->setParameter('product', $product, ProductUid::TYPE);
        //        $qb->setParameter('local', new Locale($this->translator->getLocale()), Locale::TYPE);
        //
        //        dd($qb->getQuery()->getResult());
        //

        $cacheQueries = new FilesystemAdapter('Product');

        $query = $this->entityManager->createQuery($qb->getDQL());
        $query->setQueryCache($cacheQueries);
        $query->setResultCache($cacheQueries);
        $query->enableResultCache();
        $query->setLifetime(60 * 60 * 24);

        $query->setParameter('offer', $offer, ProductOfferUid::TYPE);
        $query->setParameter('local', new Locale($this->translator->getLocale()), Locale::TYPE);

        return $query->getResult();
    }

}
