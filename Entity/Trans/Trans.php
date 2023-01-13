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

namespace App\Module\Products\Product\Entity\Trans;

use App\Module\Products\Product\Entity\Event\ProductEvent;
use App\System\Entity\EntityEvent;
use App\System\Type\Locale\Locale;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use InvalidArgumentException;

/* Перевод Product */

#[ORM\Entity()]
#[ORM\Table(name: 'product_trans')]
class Trans extends EntityEvent
{
    public const TABLE = 'product_trans';
    
    /** Связь на событие */
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: ProductEvent::class, cascade: ["remove", "persist"], inversedBy: "trans")]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: "id", nullable: false)]
    protected ProductEvent $event;
    
    /** Локаль */
    #[ORM\Id]
    #[ORM\Column(name: 'local', type: Locale::TYPE, length: 2, nullable: false)]
    protected Locale $local;
    
    /** Название */
    #[ORM\Column(name: 'name', type: Types::STRING, length: 100, nullable: false)]
    protected string $name;
    
    /** Краткое опсиание */
    #[ORM\Column(name: 'preview', type: Types::TEXT, nullable: true)]
    protected ?string $preview;
    
    
    /** Краткое опсиание */
    #[ORM\Column(name: 'description', type: Types::TEXT, nullable: true)]
    protected ?string $description;
    

    public function __construct(ProductEvent $event) { $this->event = $event; }
    
    
    /**
     * @throws Exception
     */
    public function getDto($dto) : mixed
    {
        if($dto instanceof TransInterface)
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
        if($dto instanceof TransInterface)
        {
            return parent::setEntity($dto);
        }
        
        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
    
    public function name(Locale $locale) : ?string
    {
        if($this->local->getValue() === $locale->getValue())
        {
            return $this->name;
        }
        
        return null;
    }
    
    /**
     * @param string $name
     */
    public function setName(string $name) : void
    {
        $this->name = $name;
    }
}
