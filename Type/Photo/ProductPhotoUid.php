<?php

namespace BaksDev\Products\Product\Type\Photo;

use App\Kernel;
use BaksDev\Core\Type\UidType\Uid;
use Symfony\Component\Uid\AbstractUid;

final class ProductPhotoUid extends Uid
{
    public const TEST = '0188a9a0-7667-73c5-9de0-fb018d7e614c';

	public const TYPE = 'product_photo';

}