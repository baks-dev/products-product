<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

namespace BaksDev\Products\Product\Entity\Event;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Entity\Active\ProductActive;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Description\ProductDescription;
use BaksDev\Products\Product\Entity\Files\ProductFiles;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Material\ProductMaterial;
use BaksDev\Products\Product\Entity\Modify\ProductModify;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Photo\ProductPhoto;
use BaksDev\Products\Product\Entity\Price\Cost\ProductPriceCost;
use BaksDev\Products\Product\Entity\Price\Opt\ProductPriceOpt;
use BaksDev\Products\Product\Entity\Price\ProductPrice;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\Property\ProductProperty;
use BaksDev\Products\Product\Entity\Seo\ProductSeo;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Products\Product\Entity\Video\ProductVideo;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

// События Product

#[ORM\Entity]
#[ORM\Table(name: 'product_event')]
#[ORM\Index(columns: ['main'])]
class ProductEvent extends EntityEvent
{
    /** ID */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: ProductEventUid::TYPE)]
    private ProductEventUid $id;

    /** ID Product */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: ProductUid::TYPE, nullable: false)]
    private ?ProductUid $main = null;

    /** Категории */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: ProductCategory::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private Collection $category;

    /**
     * Информация о продукции
     */
    #[ORM\OneToOne(targetEntity: ProductInfo::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private ?ProductInfo $info = null;

    /** Статусы активности продукта */
    #[Assert\Valid]
    #[ORM\OneToOne(targetEntity: ProductActive::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private ProductActive $active;

    /** Базовые Стоимость и наличие */
    #[Assert\Valid]
    #[ORM\OneToOne(targetEntity: ProductPrice::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private ?ProductPrice $price = null;

    /** Себестоимость по курсу */
    #[ORM\OneToOne(targetEntity: ProductPriceCost::class, mappedBy: 'event', cascade: ['all'])]
    private ?ProductPriceCost $cost = null;

    /** Себестоимость по курсу */
    #[ORM\OneToOne(targetEntity: ProductPriceOpt::class, mappedBy: 'event', cascade: ['all'])]
    private ?ProductPriceOpt $opt = null;



    /** Модификатор */
    #[ORM\OneToOne(targetEntity: ProductModify::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private ProductModify $modify;

    /** Перевод */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: ProductTrans::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private Collection $translate;


    /** Описание */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: ProductDescription::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private Collection $description;

    /** Фото продукта */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: ProductPhoto::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    #[ORM\OrderBy(['root' => 'DESC'])]
    private Collection $photo;

    /** Файлы (документы) продукта */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: ProductFiles::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private Collection $file;

    /** Видео */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: ProductVideo::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private Collection $video;

    /**  Настройки SEO информации */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: ProductSeo::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private Collection $seo;

    /** Тоговые предложения */
    #[Assert\Valid]
    #[ORM\OrderBy(['value' => 'ASC'])]
    #[ORM\OneToMany(targetEntity: ProductOffer::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private Collection $offer;

    /** Свойства продукта */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: ProductProperty::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private Collection $property;

    /** Сырье */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: ProductMaterial::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private Collection $material;


    public function __construct()
    {
        $this->id = new ProductEventUid();
        $this->modify = new ProductModify($this);
    }

    public function __clone()
    {
        $this->id = clone $this->id;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getMain(): ?ProductUid
    {
        return $this->main;
    }

    public function setMain(ProductUid|Product $main): void
    {
        $this->main = $main instanceof Product ? $main->getId() : $main;
    }

    public function getId(): ProductEventUid
    {
        return $this->id;
    }

    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof ProductEventInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if($dto instanceof ProductEventInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function getNameByLocale(Locale $locale): ?string
    {
        $name = null;

        /** @var ProductTrans $trans */
        foreach($this->translate as $trans)
        {
            if($name = $trans->getNameByLocal($locale))
            {
                break;
            }
        }

        return $name;
    }


    /**
     * Метод возвращает идентификатор корневой категории продукта
     */
    public function getRootCategory(): ?CategoryProductUid
    {
        $filter = $this->category->filter(function(ProductCategory $category) {
            return $category->isRoot();

        });

        if($filter->isEmpty())
        {
            return null;
        }

        return $filter->current()->getCategory();
    }

    public function getCategory(): Collection
    {
        return $this->category;
    }

    public function getOffer(): Collection
    {
        return $this->offer;
    }

    /**
     * Photo
     */
    public function getPhoto(): Collection
    {
        return $this->photo;
    }

    /**
     * File
     */
    public function getFile(): Collection
    {
        return $this->file;
    }

    /**
     * Video
     */
    public function getVideo(): Collection
    {
        return $this->video;
    }

    /**
     * Info
     */
    public function getInfo(): ProductInfo
    {
        return $this->info;
    }
}
