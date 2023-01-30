<?php

namespace BaksDev\Products\Product\Type\File;

use BaksDev\Core\Type\UidType\Uid;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;

final class ProductFileUid extends Uid //
{
	public const TYPE = 'product_file_uid';
	
}