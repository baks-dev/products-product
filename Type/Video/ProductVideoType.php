<?php

namespace BaksDev\Products\Product\Type\Video;

use BaksDev\Core\Type\UidType\UidType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\GuidType;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;

final class ProductVideoType extends UidType
{
    public function getClassType(): string
    {
        return ProductVideoUid::class;
    }

    public function getName(): string
    {
        return ProductVideoUid::TYPE;
    }

}
