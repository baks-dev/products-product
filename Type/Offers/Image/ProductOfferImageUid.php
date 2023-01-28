<?php

namespace BaksDev\Products\Product\Type\Offers\Image;

use BaksDev\Core\Type\UidType\Uid;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;

final class ProductOfferImageUid extends Uid
{
    public const TYPE = 'product_offer_image_uid';
    
}