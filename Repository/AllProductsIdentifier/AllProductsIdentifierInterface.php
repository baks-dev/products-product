<?php

namespace BaksDev\Products\Product\Repository\AllProductsIdentifier;

use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use Generator;

interface AllProductsIdentifierInterface
{
    /** Применяет фильтр по идентификатору продукта */
    public function forProduct(Product|ProductUid|string $product): self;

    /** Применяет фильтр по константе торгового предложения */
    public function forOfferConst(ProductOfferConst|string $offerConst): self;

    /** Применяет фильтр по константе множественного варианта торгового предложения */
    public function forVariationConst(ProductVariationConst|string $offerVariation): self;

    /** Применяет фильтр по константе модификатора множественного варианта торгового предложения */
    public function forModificationConst(ProductModificationConst|string $offerModification): self;

    /**
     * Метод возвращает все идентификаторы продукции с её торговыми предложениями
     */
    public function findAll(): Generator|false;
}
