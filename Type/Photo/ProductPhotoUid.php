<?php

namespace BaksDev\Products\Product\Type\Photo;

use BaksDev\Core\Type\UidType\Uid;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;

final class ProductPhotoUid extends Uid
{
	public const TYPE = 'product_photo_uid';
	
}