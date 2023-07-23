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

namespace BaksDev\Products\Product\Repository\ProductChoice;

use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Products\Product\Entity\Active\ProductActive;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ProductChoice implements ProductChoiceInterface
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
     * Метод возвращает все идентификаторы продуктов с названием
     */

    public function fetchAllProduct(): ?array
    {
        $qb = $this->entityManager->createQueryBuilder();

        $select = sprintf('new %s(product.id, trans.name)', ProductUid::class);

        $qb->select($select);

        $qb->from(Product::class, 'product');

        /*$qb->join(
            ProductEntity\Event\ProductEvent::class,
            'event',
            'WITH',
            'event.id = product.event'
        );*/

        $qb->join(
            ProductActive::class,
            'active',
            'WITH',
            '
            active.event = product.event AND
            active.active = true AND
            active.activeFrom < CURRENT_TIMESTAMP() AND
            (active.activeTo IS NULL OR active.activeTo > CURRENT_TIMESTAMP())
		'
        );

        $qb->join(
            ProductTrans::class,
            'trans',
            'WITH',
            'trans.event = product.event AND trans.local = :local'
        );

        $cacheQueries = new FilesystemAdapter('Product');

        $query = $this->entityManager->createQuery($qb->getDQL());
        $query->setQueryCache($cacheQueries);
        $query->setResultCache($cacheQueries);
        $query->enableResultCache();
        $query->setLifetime(60 * 60 * 24);

        $query->setParameter('local', new Locale($this->translator->getLocale()), Locale::TYPE);

        return $query->getResult();
    }


    /**
     * Метод возвращает активные идентификаторы событий продукции
     */
    public function fetchAllProductEvent(): ?array
    {
        $qb = $this->entityManager->createQueryBuilder();

        $select = sprintf('new %s(product.event, trans.name, info.article)', ProductEventUid::class);

        $qb->select($select);

        $qb->from(Product::class, 'product');

        $qb->join(
            ProductInfo::class,
            'info',
            'WITH',
            'info.product = product.id'
        );

        $qb->join(
            ProductActive::class,
            'active',
            'WITH',
            '
            active.event = product.event AND
            active.active = true AND
            active.activeFrom < CURRENT_TIMESTAMP() AND
            (active.activeTo IS NULL OR active.activeTo > CURRENT_TIMESTAMP())
		');

        $qb->join(
            ProductTrans::class,
            'trans',
            'WITH',
            'trans.event = product.event AND trans.local = :local'
        );

        $cacheQueries = new FilesystemAdapter('Product');

        $query = $this->entityManager->createQuery($qb->getDQL());
        $query->setQueryCache($cacheQueries);
        $query->setResultCache($cacheQueries);
        $query->enableResultCache();
        $query->setLifetime(60 * 60 * 24);

        $query->setParameter('local', new Locale($this->translator->getLocale()), Locale::TYPE);

        return $query->getResult();
    }

}
