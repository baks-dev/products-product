<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
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

namespace App\Module\Products\Product\Entity\Info;

use App\Module\Products\Product\Entity\Event\ProductEvent;
use App\Module\Products\Product\Entity\Product;
use App\Module\Products\Product\Type\Id\ProductUid;
use App\Module\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use App\System\Entity\EntityEvent;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use InvalidArgumentException;

/* Неизменяемые данные Продукта */

#[ORM\Entity()]
#[ORM\Table(name: 'product_info')]
class Info extends EntityEvent
{
    public const TABLE = 'product_info';
    
    /** ID Product */
    #[ORM\Id]
    #[ORM\Column(type: ProductUid::TYPE)]
    protected ProductUid $product;
    
    /** Семантическая ссылка на товар */
    #[ORM\Column(type: Types::STRING, unique: true)]
    protected string $url;
    
    /** Артикул товара */
    #[ORM\Column(type: Types::STRING)]
    protected string $article;
    
    /** Профиль пользователя, которому принадлежит товар */
    #[ORM\Column(type: UserProfileUid::TYPE, nullable: true)]
    protected UserProfileUid $profile;

    public function __construct(Product|ProductUid $product)
    {
        $this->product = $product instanceof Product ? $product->getId() : $product;
    }
    
    /**
     * @throws Exception
     */
    public function getDto($dto) : mixed
    {
        if($dto instanceof InfoInterface)
        {
            return parent::getDto($dto);
        }
        
        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
    
    /**
     * @throws Exception
     */
    public function setEntity($dto) : mixed
    {
        if($dto instanceof InfoInterface)
        {
            return parent::setEntity($dto);
        }
        
        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
    
}
