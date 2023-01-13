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

namespace App\Module\Products\Product\Entity\Property;


use App\Module\Products\Category\Type\Section\Field\Id\FieldUid;
use App\Module\Products\Product\Entity\Event\ProductEvent;
use App\System\Entity\EntityEvent;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/* Свойства продукта */

#[ORM\Entity()]
#[ORM\Table(name: 'product_property')]

class Property extends EntityEvent
{
    public const TABLE = 'product_property';
    
    /** ID события */
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: ProductEvent::class, cascade: ['persist'], inversedBy: 'property')]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id', nullable: false)]
    protected ProductEvent $event;
    
    /** Связь на поле */
    #[ORM\Id]
    #[ORM\Column(name: 'field_id', type: FieldUid::TYPE, nullable: false)]
    protected FieldUid $field;
    
    /** Заполненное значение */
    #[ORM\Column(name: 'value', type: Types::TEXT, nullable: true)]
    protected ?string $value = null;
    
    /**
     * @param ProductEvent $event
     */
    public function __construct(ProductEvent $event) { $this->event = $event; }

    public function getDto($dto) : mixed
    {
        if($dto instanceof PropertyInterface)
        {
            return parent::getDto($dto);
        }
        
        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
    
    public function setEntity($dto) : mixed
    {
        if($dto instanceof PropertyInterface)
        {
            return parent::setEntity($dto);
        }
        
        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
    
    
    
//    /**
//     * @param Event $event
//     */
//    public function setEvent(?Event $event) : void
//    {
//        $this->event = $event;
//    }
//
//    /**
//     * @return Event|null
//     */
//    public function getEvent() : ?Event
//    {
//        return $this->event;
//    }
//
//    /**
//     * @return FieldUid|null
//     */
//    public function getField() : ?FieldUid
//    {
//        return $this->field;
//    }
//
//    /**
//     * @param FieldUid|null $field
//     */
//    public function setField(?FieldUid $field) : void
//    {
//        $this->field = $field;
//    }
//
//
//
//    /**
//     * @return string|null
//     */
//    public function getValue() : ?string
//    {
//        return $this->value;
//    }
//
//    /**
//     * @param string|null $value
//     */
//    public function setValue(?string $value) : void
//    {
//        $this->value = $value;
//    }
    

    
}
