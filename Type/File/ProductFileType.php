<?php

namespace BaksDev\Products\Product\Type\File;

use BaksDev\Core\Type\UidType\UidType;

final class ProductFileType extends UidType
{
	
	public function getClassType(): string
	{
		return ProductFileUid::class;
	}
	
	
	public function getName(): string
	{
		return ProductFileUid::TYPE;
	}
	
}