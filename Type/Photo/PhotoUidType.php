<?php

namespace BaksDev\Products\Product\Type\Photo;

use App\System\Type\UidType\UidType;

final class PhotoUidType extends UidType
{
    public function getClassType() : string
    {
        return PhotoUid::class;
    }
    
    public function getName() : string
    {
        return PhotoUid::TYPE;
    }
}