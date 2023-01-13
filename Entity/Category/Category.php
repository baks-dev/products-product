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

namespace App\Module\Products\Product\Entity\Category;

use App\Module\Products\Category\Type\Id\CategoryUid;
use App\Module\Products\Product\Entity\Event\ProductEvent;
use App\System\Entity\EntityEvent;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

#[ORM\Entity]
#[ORM\Table(name: 'product_categories_product')]

class Category extends EntityEvent
{
    public const TABLE = "product_categories_product";
    
    /** ID события */
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: ProductEvent::class, cascade: ["remove", "persist"], inversedBy: "category")]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: "id")]
    protected ProductEvent $event;
    
    /** ID Category */
    #[ORM\Id]
    #[ORM\Column(type: CategoryUid::TYPE, nullable: false)]
    protected CategoryUid $category;
    
    /** Корневая категория */
    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    protected bool $root = false;
    
    
    public function __construct(ProductEvent $event)
    {
        $this->event = $event;
    }
    

    public function getDto($dto) : mixed
    {
        if($dto instanceof CategoryInterface)
        {
            return parent::getDto($dto);
        }
        
        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
    

    public function setEntity($dto) : mixed
    {
        if($dto instanceof CategoryInterface)
        {
            return parent::setEntity($dto);
        }
        
        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
    

}