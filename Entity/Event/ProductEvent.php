<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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
use BaksDev\Core\Type\Modify\ModifyAction;
use BaksDev\Products\Product\Entity\Active\ProductActive;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Files\ProductFiles;
use BaksDev\Products\Product\Entity\Modify\ProductModify;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Photo\ProductPhoto;
use BaksDev\Products\Product\Entity\Price\ProductPrice;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\Property\ProductProperty;
use BaksDev\Products\Product\Entity\Seo\ProductSeo;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Products\Product\Entity\Video\ProductVideo;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/* События Product */


#[ORM\Entity]
#[ORM\Table(name: 'product_event')]
#[ORM\Index(columns: ['product'])]
class ProductEvent extends EntityEvent
{
    public const TABLE = 'product_event';

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
    private ?ProductUid $product = null;

    /** Категории */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: ProductCategory::class, cascade: ['all'])]
    private Collection $category;

    /** Статусы активности продукта */
    #[Assert\Valid]
    #[ORM\OneToOne(mappedBy: 'event', targetEntity: ProductActive::class, cascade: ['all'])]
    private ProductActive $active;

    /** Базовые Стоимость и наличие */
    #[Assert\Valid]
    #[ORM\OneToOne(mappedBy: 'event', targetEntity: ProductPrice::class, cascade: ['all'])]
    private ?ProductPrice $price = null;

    /** Модификатор */
    #[ORM\OneToOne(mappedBy: 'event', targetEntity: ProductModify::class, cascade: ['all'])]
    private ProductModify $modify;

    /** Перевод */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: ProductTrans::class, cascade: ['all'])]
    private Collection $translate;

    /** Фото продукта */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: ProductPhoto::class, cascade: ['all'])]
    private Collection $photo;

    /** Файлы (документы) продукта */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: ProductFiles::class, cascade: ['all'])]
    private Collection $file;

    /** Видео */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: ProductVideo::class, cascade: ['all'])]
    private Collection $video;

    /**  Настройки SEO информации */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: ProductSeo::class, cascade: ['all'])]
    private Collection $seo;

    /** Тоговые предложения */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: ProductOffer::class, cascade: ['all'])]
    private Collection $offer;

    /** Свойства продукта */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: ProductProperty::class, cascade: ['all'])]
    private Collection $property;


    public function __construct()
    {
        $this->id = new ProductEventUid();
        //$this->active = new Active($this);
        //$this->price = new Price($this);
        $this->modify = new ProductModify($this);
    }


    public function __clone()
    {
        $this->id = new ProductEventUid();
    }


    /**
     * @return ProductEventUid
     */
    public function getId(): ProductEventUid
    {
        return $this->id;
    }


    /**
     * @return ProductUid|null
     */
    public function getProduct(): ?ProductUid
    {
        return $this->product;
    }


    public function setProduct(ProductUid|Product $product): void
    {
        $this->product = $product instanceof Product ? $product->getId() : $product;
    }


    //    public function isModifyActionEquals(ModifyActionEnum $action) : bool
    //    {
    //        return $this->modify->equals($action);
    //    }

    /**
     * @throws Exception
     */
    public function getDto($dto): mixed
    {
        if ($dto instanceof ProductEventInterface) {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    /**
     * @throws Exception
     */
    public function setEntity($dto): mixed
    {
        if ($dto instanceof ProductEventInterface) {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    public function getNameByLocale(Locale $locale): ?string
    {
        $name = null;

        /** @var ProductTrans $trans */
        foreach ($this->translate as $trans) {
            if ($name = $trans->getNameByLocal($locale)) {
                break;
            }
        }

        return $name;
    }


    /**
     * @return Collection
     */
    public function getCategory(): Collection
    {
        return $this->category;
    }
}
