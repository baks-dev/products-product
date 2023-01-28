<?php

namespace BaksDev\Products\Product\Type\Photo;

use BaksDev\Core\Type\UidType\UidType;

final class ProductPhotoType extends UidType
{
    public function getClassType() : string
    {
        return ProductPhotoUid::class;
    }
    
    public function getName() : string
    {
        return ProductPhotoUid::TYPE;
    }
}