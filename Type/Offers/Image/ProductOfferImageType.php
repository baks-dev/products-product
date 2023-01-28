<?php

namespace BaksDev\Products\Product\Type\Offers\Image;

use BaksDev\Core\Type\UidType\UidType;

final class ProductOfferImageType extends UidType
{
    public function getClassType() : string
    {
        return ProductOfferImageUid::class;
    }
    
    public function getName() : string
    {
        return ProductOfferImageUid::TYPE;
    }
}