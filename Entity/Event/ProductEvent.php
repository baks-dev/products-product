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

namespace BaksDev\Products\Product\Entity\Event;


use BaksDev\Products\Product\Entity\Active\Active;
use BaksDev\Products\Product\Entity\Category\Category;
use BaksDev\Products\Product\Entity\Files\Files;
use BaksDev\Products\Product\Entity\Info\Info;
use BaksDev\Products\Product\Entity\Modify\Modify;
use BaksDev\Products\Product\Entity\Offers\Offers;
use BaksDev\Products\Product\Entity\Photo\Photo;
use BaksDev\Products\Product\Entity\Price\Price;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\Property\Property;
use BaksDev\Products\Product\Entity\Seo\Seo;
use BaksDev\Products\Product\Entity\Trans\Trans;
use BaksDev\Products\Product\Entity\Video\Video;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use App\System\Entity\EntityEvent;
use BaksDev\Core\Type\Locale\Locale;
use App\System\Type\Modify\ModifyAction;
use App\System\Type\Modify\ModifyActionEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use InvalidArgumentException;

/* События Product */

#[ORM\Entity]
#[ORM\Table(name: 'product_event')]
#[ORM\Index(columns: ['product'])]
class ProductEvent extends EntityEvent
{
    public const TABLE = 'product_event';
    
    /** ID */
    #[ORM\Id]
    #[ORM\Column(type: ProductEventUid::TYPE)]
    protected ProductEventUid $id;
    
    /** ID Product */
    #[ORM\Column(type: ProductUid::TYPE, nullable: false)]
    protected ?ProductUid $product = null;
    
    /** Категории */
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Category::class, cascade: ['all'])]
    protected Collection $category;

    /** Статусы активности продукта */
    #[ORM\OneToOne(mappedBy: 'event', targetEntity: Active::class, cascade: ['all'])]
    protected Active $active;
    
    /** Базовые Стоимость и наличие */
    #[ORM\OneToOne(mappedBy: 'event', targetEntity: Price::class, cascade: ['all'])]
    protected Price $price;
    
    /** Модификатор */
    #[ORM\OneToOne(mappedBy: 'event', targetEntity: Modify::class, cascade: ['all'])]
    protected Modify $modify;

    /** Перевод */
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Trans::class, cascade: ['all'])]
    protected Collection $trans;
    
    /** Фото продукта */
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Photo::class, cascade: ['all'])]
    protected Collection $photos;
    
    /** Файлы (документы) продукта */
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Files::class, cascade: ['all'])]
    protected Collection $files;
    
    /** Видео */
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Video::class, cascade: ['all'])]
    protected Collection $videos;
    
    /**  Настройки SEO информации */
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Seo::class, cascade: ['all'])]
    protected Collection $seo;
    
    /** Тоговые предложения */
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Offers::class, cascade: ['all'])]
    protected Collection $offers;
    
    /** Свойства продукта */
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Property::class, cascade: ['all'])]
    protected Collection $property;

    public function __construct() {
        
        $this->id = new ProductEventUid();
        $this->active = new Active($this);
        $this->price = new Price($this);
        $this->modify = new Modify($this, new ModifyAction(ModifyActionEnum::NEW));
		
    }
    
    public function __clone()
    {
        $this->id = new ProductEventUid();
    }
    
    /**
     * @return ProductEventUid
     */
    public function getId() : ProductEventUid
    {
        return $this->id;
    }
    
    /**
     * @return ProductUid|null
     */
    public function getProduct() : ?ProductUid
    {
        return $this->product;
    }

    public function setProduct(ProductUid|Product $product) : void
    {
        $this->product = $product instanceof Product ? $product->getId() : $product;
    }
    
    
    public function isModifyActionEquals(ModifyActionEnum $action) : bool
    {
        return $this->modify->equals($action);
    }
    
    /**
     * @throws Exception
     */
    public function getDto($dto) : mixed
    {
        if($dto instanceof ProductEventInterface)
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
        if($dto instanceof ProductEventInterface)
        {
            return parent::setEntity($dto);
        }
        
        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
    
    
    public function getNameByLocale(Locale $locale) : ?string
    {
        $name = null;
        
        /** @var Trans $trans */
        foreach($this->trans as $trans)
        {
            if($name = $trans->name($locale))
            {
                break;
            }
        }
        
        return $name;
    }
    
}