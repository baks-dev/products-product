<?php
/*
*  Copyright Baks.dev <admin@baks.dev>
*
*  Licensed under the Apache License, Version 2.0 (the "License");
*  you may not use this file except in compliance with the License.
*  You may obtain a copy of the License at
*
*  http://www.apache.org/licenses/LICENSE-2.0
*
*  Unless required by applicable law or agreed to in writing, software
*  distributed under the License is distributed on an "AS IS" BASIS,
*  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
*  See the License for the specific language governing permissions and
*   limitations under the License.
*
*/

namespace BaksDev\Products\Product\Type\Id;

use App\System\Type\UidType\Uid;
use Symfony\Component\Uid\AbstractUid;

final class ProductUid extends Uid
{
	
	/*
	$doctrine->dbal()->type(ProductUid::TYPE)->class(ProductUidType::class);
	$container->services()->set(ProductUid::class)
		->tag('controller.argument_value_resolver', ['priority' => 100])
	;
	*/
	
    public const TYPE = 'product_id';

}