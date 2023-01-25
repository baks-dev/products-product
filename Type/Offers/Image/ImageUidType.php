<?php

namespace BaksDev\Products\Product\Type\Offers\Image;

use App\System\Type\UidType\UidType;

final class ImageUidType extends UidType
{
    public function getClassType() : string
    {
        return ImageUid::class;
    }
    
    public function getName() : string
    {
        return ImageUid::TYPE;
    }
}