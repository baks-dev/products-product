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

namespace BaksDev\Products\Product\Repository\ProductModificationChoice;

use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Products\Category\Entity as CategoryEntity;
use BaksDev\Products\Product\Entity as ProductEntity;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ProductModificationChoice implements ProductModificationChoiceInterface
{
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;

    public function __construct(
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    )
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * Метод возвращает все постоянные идентификаторы CONST модификаций множественных вариантов торговых предложений продукта
     */
    public function fetchProductModificationConstByVariationConst(ProductVariationConst $const): ?array
    {
        $qb = $this->entityManager->createQueryBuilder();

        $select = sprintf('new %s(
            modification.const, 
            modification.value, 
            trans.name, 
            category_modification.reference
        )', ProductModificationConst::class);

        $qb->select($select);

        $qb->from(ProductEntity\Offers\Variation\ProductVariation::class, 'variation');

        $qb->join(
            ProductEntity\Offers\ProductOffer::class,
            'offer',
            'WITH',
            'offer.id = variation.offer'
        );

        $qb->join(
            ProductEntity\Product::class,
            'product',
            'WITH',
            'product.event = offer.event'
        );

        $qb->join(
            ProductEntity\Offers\Variation\Modification\ProductModification::class,
            'modification',
            'WITH',
            'modification.variation = variation.id'
        );

        // Тип торгового предложения

        $qb->join(
            CategoryEntity\Offers\Variation\Modification\ProductCategoryModification::class,
            'category_modification',
            'WITH',
            'category_modification.id = modification.categoryModification'
        );

        $qb->leftJoin(
            CategoryEntity\Offers\Variation\Modification\Trans\ProductCategoryModificationTrans::class,
            'trans',
            'WITH',
            'trans.modification = category_modification.id AND trans.local = :local'
        );

        $qb->where('variation.const = :const');


        $cacheQueries = new FilesystemAdapter('Product');

        $query = $this->entityManager->createQuery($qb->getDQL());
        $query->setQueryCache($cacheQueries);
        $query->setResultCache($cacheQueries);
        $query->enableResultCache();
        $query->setLifetime(60 * 60 * 24);

        $query->setParameter('const', $const, ProductVariationConst::TYPE);
        $query->setParameter('local', new Locale($this->translator->getLocale()), Locale::TYPE);

        return $query->getResult();
    }


    /**
     * Метод возвращает все идентификаторы модификаций множественных вариантов торговых предложений продукта
     */
    public function fetchProductModificationByVariation(ProductVariationUid $variation): ?array
    {
        $qb = $this->entityManager->createQueryBuilder();

        $select = sprintf('new %s(
            modification.id, 
            modification.value, 
            trans.name, 
            category_modification.reference
        )', ProductModificationUid::class);

        $qb->select($select);

        $qb->from(ProductEntity\Offers\Variation\ProductVariation::class, 'variation');

        $qb->join(
            ProductEntity\Offers\ProductOffer::class,
            'offer',
            'WITH',
            'offer.id = variation.offer'
        );

        $qb->join(
            ProductEntity\Product::class,
            'product',
            'WITH',
            'product.event = offer.event'
        );


        $qb->join(
            ProductEntity\Offers\Variation\Modification\ProductModification::class,
            'modification',
            'WITH',
            'modification.variation = variation.id'
        );

        // Тип торгового предложения

        $qb->join(
            CategoryEntity\Offers\Variation\Modification\ProductCategoryModification::class,
            'category_modification',
            'WITH',
            'category_modification.id = modification.categoryModification'
        );

        $qb->leftJoin(
            CategoryEntity\Offers\Variation\Modification\Trans\ProductCategoryModificationTrans::class,
            'trans',
            'WITH',
            'trans.modification = category_modification.id AND trans.local = :local'
        );

        $qb->where('variation.id = :variation');


        $cacheQueries = new FilesystemAdapter('Product');

        $query = $this->entityManager->createQuery($qb->getDQL());
        $query->setQueryCache($cacheQueries);
        $query->setResultCache($cacheQueries);
        $query->enableResultCache();
        $query->setLifetime(60 * 60 * 24);

        $query->setParameter('variation', $variation, ProductVariationUid::TYPE);
        $query->setParameter('local', new Locale($this->translator->getLocale()), Locale::TYPE);

        return $query->getResult();
    }
}
