<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

final class ProductEventByArticleRepository implements ProductEventByArticleInterface
{
    private bool $isCard = false;

    private UserProfileUid|false $profile = false;

    public function __construct(private readonly ORMQueryBuilder $ORMQueryBuilder) {}

    /**
     * Метод возвращает событие только корневого артикула
     */
    public function onlyCard(): self
    {
        $this->isCard = true;
        return $this;
    }

    /**
     * Метод возвращает событие любого артикула, в том числе торговых предложений
     */
    public function allOffers(): self
    {
        $this->isCard = false;
        return $this;
    }

    public function forProfile(UserProfile|UserProfileUid|string|false $profile): self
    {
        if(empty($profile))
        {
            $this->profile = false;
            return $this;
        }

        if(is_string($profile))
        {
            $profile = new UserProfileUid($profile);
        }

        if($profile instanceof UserProfile)
        {
            $profile = $profile->getId();
        }

        $this->profile = $profile;

        return $this;
    }

    /**
     * Метод возвращает по артикулу событие продукта
     */
    public function findProductEventByArticle(string $article): ProductEvent|false
    {
        $orm = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $orm
            ->from(ProductInfo::class, 'info')
            ->where('info.article = :article');

        if($this->profile instanceof UserProfileUid)
        {
            $orm
                ->andWhere('info.profile = :profile')
                ->setParameter(
                    'profile',
                    $this->profile,
                    UserProfileUid::TYPE
                );
        }

        $orm->join(
            Product::class,
            'product',
            'WITH',
            'product.id = info.product'
        )
            ->setParameter('article', $article);

        $orm
            ->select('event')
            ->join(
                ProductEvent::class,
                'event',
                'WITH',
                'event.id = product.event'
            );

        if(true === $this->isCard)
        {
            $orm->setMaxResults(1);
        }

        /** @var Product $Product */
        $ProductEvent = $orm->getQuery()->getOneOrNullResult();

        if(true === $this->isCard || $ProductEvent instanceof ProductEvent)
        {
            return $ProductEvent ?: false;
        }


        /**
         * Поиск по артикулу в торговом предложении
         */

        $orm = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $orm
            ->from(ProductOffer::class, 'offer')
            ->where('offer.article = :article')
            ->setParameter(
                key: 'article',
                value: $article
            );

        $orm
            ->select('event')
            ->join(
                ProductEvent::class,
                'event',
                'WITH',
                'event.id = offer.event'
            );

        $orm->join(Product::class, 'product', 'WITH', 'product.event = event.id');

        /** @var ProductEvent $ProductEvent */
        $ProductEvent = $orm->getQuery()->getOneOrNullResult();

        if($ProductEvent instanceof ProductEvent)
        {
            return $ProductEvent;
        }


        /**
         * Поиск по артикулу в множественном варианте торгового предложения
         */

        $orm = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $orm
            ->from(ProductVariation::class, 'variation')
            ->where('variation.article = :article')
            ->setParameter(
                key: 'article',
                value: $article
            );

        $orm->join(ProductOffer::class, 'offer', 'WITH', 'offer.id = variation.offer');

        $orm
            ->select('event')
            ->join(
                ProductEvent::class,
                'event',
                'WITH',
                'event.id = offer.event'
            );

        $orm->join(Product::class, 'product', 'WITH', 'product.event = event.id');

        /** @var ProductEvent $ProductEvent */
        $ProductEvent = $orm->getQuery()->getOneOrNullResult();

        if($ProductEvent instanceof ProductEvent)
        {
            return $ProductEvent;
        }


        /**
         * Поиск по артикулу в множественном варианте торгового предложения
         */

        $orm = $this->ORMQueryBuilder->createQueryBuilder(self::class);


        $orm
            ->from(ProductModification::class, 'modification')
            ->where('modification.article = :article')
            ->setParameter(
                key: 'article',
                value: $article
            );

        $orm->join(ProductVariation::class, 'variation', 'WITH', 'variation.id = modification.variation');

        $orm->join(ProductOffer::class, 'offer', 'WITH', 'offer.id = variation.offer');

        $orm
            ->select('event')
            ->join(
                ProductEvent::class,
                'event',
                'WITH',
                'event.id = offer.event'
            );

        $orm->join(Product::class, 'product', 'WITH', 'product.event = event.id');


        /** @var ProductEvent $ProductEvent */
        $ProductEvent = $orm->getQuery()->getOneOrNullResult();

        return $ProductEvent instanceof ProductEvent ? $ProductEvent : false;
    }
}
