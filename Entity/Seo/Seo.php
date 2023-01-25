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

namespace BaksDev\Products\Product\Entity\Seo;

use BaksDev\Products\Product\Entity\Event\ProductEvent;
use App\System\Entity\EntityEvent;
use BaksDev\Core\Type\Locale\Locale;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

#[ORM\Entity()]
#[ORM\Table(name: 'product_seo')]
class Seo extends EntityEvent
{
    public const TABLE = "`product_seo`";

    /** Связь на событие */
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: ProductEvent::class, cascade: ["remove", "persist"], inversedBy: "seo")]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: "id")]
    protected ProductEvent $event;
    
    /** Локаль */
    #[ORM\Id]
    #[ORM\Column(name: 'local', type: Locale::TYPE, length: 2, nullable: false)]
    protected Locale $local;
    
    /** Шаблон META TITLE */
    #[ORM\Column(name: 'title', type: Types::TEXT, nullable: true)]
    protected ?string $title;
    
    /** Шаблон META KEYWORDS */
    #[ORM\Column(name: 'keywords', type: Types::TEXT, nullable: true)]
    protected ?string $keywords;
    
    /** Шаблон META DESCRIPTION */
    #[ORM\Column(name: 'description', type: Types::TEXT, nullable: true)]
    protected ?string $description;
    
    /**
     * @param ProductEvent $event
     */
    public function __construct(ProductEvent $event) { $this->event = $event; }

    public function getDto($dto) : mixed
    {
        if($dto instanceof SeoInterface)
        {
            return parent::getDto($dto);
        }
        
        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
    

    public function setEntity($dto) : mixed
    {
    
        if(empty($dto->getTitle()) && empty($dto->getDescription()) && empty($dto->getKeywords()))
        {
            return false;
        }
        
        
        if($dto instanceof SeoInterface)
        {
            return parent::setEntity($dto);
        }
        
        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
    
    
    //    /**
//     * @return Event
//     */
//    public function getEvent() : Event
//    {
//        return $this->event;
//    }
//
//    /**
//     * @param Event $event
//     */
//    public function setEvent(Event $event) : void
//    {
//        $this->event = $event;
//    }
//
//
//    /**
//     * @return string
//     */
//    public function getTitle() : string
//    {
//        return $this->title;
//    }
//
//    /**
//     * @param string $title
//     */
//    public function setTitle(string $title) : void
//    {
//        $this->title = $title;
//    }
//
//    /**
//     * @return string
//     */
//    public function getKeywords() : string
//    {
//        return $this->keywords;
//    }
//
//    /**
//     * @param string $keywords
//     */
//    public function setKeywords(string $keywords) : void
//    {
//        $this->keywords = $keywords;
//    }
//
//    /**
//     * @return string
//     */
//    public function getDescription() : string
//    {
//        return $this->description;
//    }
//
//    /**
//     * @param string $description
//     */
//    public function setDescription(string $description) : void
//    {
//        $this->description = $description;
//    }
//
//    /**
//     * @return string
//     */
//    public function getLocal() : string
//    {
//        return $this->local->getValue();
//    }
//
//    /**
//     * @param Locale $local
//     */
//    public function setLocal(Locale $local) : void
//    {
//        $this->local = $local;
//    }

}