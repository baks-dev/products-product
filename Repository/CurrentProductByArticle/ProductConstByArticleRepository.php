<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Products\Product\Repository\CurrentProductByArticle;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Product;

final readonly class ProductConstByArticleRepository implements ProductConstByArticleInterface
{
    public function __construct(private DBALQueryBuilder $DBALQueryBuilder) {}

    /**
     * Метод возвращает активные идентификаторы продукции
     */
    public function find(string $article): CurrentProductDTO|false
    {

        /** Поиск артикула INFO */

        $dbalInfo = $this->DBALQueryBuilder->createQueryBuilder(self::class);


        $dbalInfo->select('product.id AS product');
        $dbalInfo->addSelect('product.event');
        $dbalInfo->addSelect('NULL::uuid  AS offer');
        $dbalInfo->addSelect('NULL::uuid  AS offer_const');
        $dbalInfo->addSelect('NULL::uuid  AS variation');
        $dbalInfo->addSelect('NULL::uuid  AS variation_const');
        $dbalInfo->addSelect('NULL::uuid  AS modification');
        $dbalInfo->addSelect('NULL::uuid  AS modification_const');

        $dbalInfo->from(ProductInfo::class, 'info');

        $dbalInfo->join(
            'info',
            Product::class, 'product',
            'product.id = info.product'
        );

        $dbalInfo->where('info.article = :article');


        /** Поиск артикула OFFER */

        $dbalOffer = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbalOffer->select('product.id AS product');
        $dbalOffer->addSelect('product.event');
        $dbalOffer->addSelect('offer.id AS offer');
        $dbalOffer->addSelect('offer.const AS offer_const');
        $dbalOffer->addSelect('NULL::uuid  AS variation');
        $dbalOffer->addSelect('NULL::uuid  AS variation_const');
        $dbalOffer->addSelect('NULL::uuid  AS modification');
        $dbalOffer->addSelect('NULL::uuid  AS modification_const');

        $dbalOffer
            ->from(ProductOffer::class, 'offer')
            ->where('offer.article = :article');

        $dbalOffer->join(
            'offer',
            Product::class, 'product',
            'product.event = offer.event'
        );


        /** Поиск артикула VARIATION */

        $dbalVariation = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbalVariation->select('product.id AS product');
        $dbalVariation->addSelect('product.event');
        $dbalVariation->addSelect('offer.id AS offer');
        $dbalVariation->addSelect('offer.const AS offer_const');
        $dbalVariation->addSelect('variation.id AS variation');
        $dbalVariation->addSelect('variation.const AS variation_const');
        $dbalVariation->addSelect('NULL::uuid AS modification');
        $dbalVariation->addSelect('NULL::uuid AS modification_const');

        $dbalVariation
            ->from(ProductVariation::class, 'variation')
            ->where('variation.article = :article');

        $dbalVariation
            ->join(
                'variation',
                ProductOffer::class, 'offer',
                'offer.id = variation.offer'
            );

        $dbalVariation
            ->join(
                'offer',
                Product::class, 'product',
                'product.event = offer.event'
            );


        /** Поиск артикула MODIFICATION */

        $dbalModification = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbalModification->select('product.id AS product');
        $dbalModification->addSelect('product.event');
        $dbalModification->addSelect('offer.id AS offer');
        $dbalModification->addSelect('offer.const AS offer_const');
        $dbalModification->addSelect('variation.id AS variation');
        $dbalModification->addSelect('variation.const AS variation_const');
        $dbalModification->addSelect('modification.id  AS modification');
        $dbalModification->addSelect('modification.const  AS modification_const');


        $dbalModification
            ->from(ProductModification::class, 'modification')
            ->where('modification.article = :article');


        $dbalModification
            ->join(
                'modification',
                ProductVariation::class, 'variation',
                'variation.id = modification.variation'
            );


        $dbalModification
            ->join(
                'variation',
                ProductOffer::class, 'offer',
                'offer.id = variation.offer'
            );

        $dbalModification
            ->join(
                'offer',
                Product::class, 'product',
                'product.event = offer.event'
            );


        /** UNION */

        $union = [
            str_replace('SELECT', '', $dbalInfo->getSQL()),
            $dbalOffer->getSQL(),
            $dbalVariation->getSQL(),
            $dbalModification->getSQL()
        ];

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);
        $dbal->select(implode(' UNION ', $union));
        $dbal->setParameter('article', $article);

        return $dbal
            ->enableCache('products-product', 86400)
            ->fetchHydrate(CurrentProductDTO::class);

    }


    public function oldFind(string $article): CurrentProductDTO|false
    {
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal->from(Product::class, 'product');

        $dbal->leftJoin(
            'product',
            ProductInfo::class,
            'info',
            'info.product = product'
        );

        $dbal->leftJoin(
            'product',
            ProductOffer::class,
            'offer',
            'offer.event = product.event'
        );

        $dbal->leftJoin(
            'offer',
            ProductVariation::class,
            'variation',
            'variation.offer = offer.id'
        );

        $dbal->leftJoin(
            'variation',
            ProductModification::class,
            'modification',
            'modification.variation = variation.id'
        );

        $dbal->where('info.article = :article');
        $dbal->orWhere('offer.article = :article');
        $dbal->orWhere('variation.article = :article');
        $dbal->orWhere('modification.article = :article');

        $dbal->setParameter('article', $article);


        $dbal->select('product.id AS product');
        $dbal->addSelect('product.event');

        /** Торговое предложение */
        $dbal->addSelect('offer.id AS offer');
        $dbal->addSelect('offer.const AS offer_const');

        /** Множественный вариант торгового предложения */
        $dbal->addSelect('variation.id AS variation');
        $dbal->addSelect('variation.const AS variation_const');

        /** Модификация множественного варианта торгового предложения */
        $dbal->addSelect('modification.id AS modification');
        $dbal->addSelect('modification.const AS modification_const');


        return $dbal
            //->enableCache('products-product', 86400)
            ->fetchHydrate(CurrentProductDTO::class);
    }
}
