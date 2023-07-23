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

namespace BaksDev\Products\Product\Repository\ProductByArticle;

use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

final class ProductEventByArticle implements ProductEventByArticleInterface
{

    private EntityManagerInterface $entityManager;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * Метод возвращает по артикулу событие продукта
     */
    public function findProductEventByArticle(string $article): ?ProductEvent
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('product');
        $qb->from(ProductInfo::class, 'info');
        $qb->where('info.article = :article');
        $qb->join(Product::class, 'product', 'WITH', 'product.id = info.product');
        $qb->setParameter('article', $article);

        /** @var Product $Product */
        $Product = $qb->getQuery()->getOneOrNullResult();



        if($Product)
        {
            return $this->entityManager->getRepository(ProductEvent::class)->find($Product->getEvent());
        }



        /** Поиск по артикулу в торговом предложении */
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('event');
        $qb->from(ProductOffer::class, 'offer');
        $qb->where('offer.article = :article');
        $qb->setParameter('article', $article);

        $qb->join(ProductEvent::class, 'event', 'WITH', 'event.id = offer.event');

        $qb->join(Product::class, 'product', 'WITH', 'product.event = event.id');

        /** @var ProductEvent $ProductEvent */
        $ProductEvent = $qb->getQuery()->getOneOrNullResult();

        if($ProductEvent)
        {
            return $ProductEvent;
        }

        /** Поиск по артикулу в множественном варианте торгового предложения */
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('event');
        $qb->from(ProductVariation::class, 'variation');
        $qb->where('variation.article = :article');
        $qb->setParameter('article', $article);

        $qb->join(ProductOffer::class, 'offer', 'WITH', 'offer.id = variation.offer');

        $qb->join(ProductEvent::class, 'event', 'WITH', 'event.id = offer.event');

        $qb->join(Product::class, 'product', 'WITH', 'product.event = event.id');

        /** @var ProductEvent $ProductEvent */
        $ProductEvent = $qb->getQuery()->getOneOrNullResult();

        if($ProductEvent)
        {
            return $ProductEvent;
        }

        /** Поиск по артикулу в множественном варианте торгового предложения */
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('event');

        $qb->from(ProductModification::class, 'modification');
        $qb->where('modification.article = :article');
        $qb->setParameter('article', $article);

        $qb->join(ProductVariation::class, 'variation', 'WITH', 'variation.id = modification.variation');
        $qb->join(ProductOffer::class, 'offer', 'WITH', 'offer.id = variation.offer');

        $qb->join(ProductEvent::class, 'event', 'WITH', 'event.id = offer.event');

        $qb->join(Product::class, 'product', 'WITH', 'product.event = event.id');

        return $qb->getQuery()->getOneOrNullResult();
    }
}