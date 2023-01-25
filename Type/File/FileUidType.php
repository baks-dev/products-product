<?php

namespace BaksDev\Products\Product\Type\File;

use App\System\Type\UidType\UidType;

final class FileUidType extends UidType
{
    
    public function getClassType() : string
    {
        return FileUid::class;
    }
    
    public function getName() : string
    {
        return FileUid::TYPE;
    }
}