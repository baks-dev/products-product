<?php

namespace App\Module\Products\Product\Type\Video;

use App\System\Type\UidType\UidType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\GuidType;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;

final class VideoUidType extends UidType
{
    public function getClassType() : string
    {
        return VideoUid::class;
    }
    
    public function getName() : string
    {
        return VideoUid::TYPE;
    }
}