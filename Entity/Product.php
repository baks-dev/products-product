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

namespace BaksDev\Products\Product\Entity;

use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use Doctrine\ORM\Mapping as ORM;

/* Product */

#[ORM\Entity]
#[ORM\Table(name: 'product')]
class Product //extends Event
{
    
    public const TABLE = 'product';
    
    /** ID */
    #[ORM\Id]
    #[ORM\Column(type: ProductUid::TYPE)]
    private ProductUid $id;
    
    /** ID События */
    #[ORM\Column(type: ProductEventUid::TYPE, unique: true, nullable: false)]
    private ProductEventUid $event;
    
    
    public function __construct() { $this->id = new ProductUid(); }
    

    public function getId() : ProductUid
    {
        return $this->id;
    }
    

    public function restore(ProductUid $id) : void
    {
        $this->id = $id;
    }
    

    public function getEvent() : ProductEventUid
    {
        return $this->event;
    }

    public function setEvent(ProductEventUid|Event\ProductEvent $event) : void
    {
        $this->event = $event instanceof Event\ProductEvent ? $event->getId() : $event;
    }
}