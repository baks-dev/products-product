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

namespace App\Module\Products\Product\UseCase\Admin\NewEdit\Offers\Offer;

use App\Module\Products\Category\Type\Offers\Id\OffersUid;
use App\Module\Products\Product\Entity\Offers\Offer\OfferInterface;
use App\Module\Products\Product\Entity\Offers\Offer\Quantity\ProductQuantityInterface;
use App\Module\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use App\Module\Products\Product\Type\Offers\Id\ProductOfferUid;
use App\Module\Products\Product\UseCase\Admin\NewEdit\Offers\Offer\Image\ImageCollectionDTO;
use App\Module\Products\Product\UseCase\Admin\NewEdit\Offers\Offer\Price\PriceDTO;
use App\Module\Products\Product\UseCase\Admin\NewEdit\Offers\Offer\Quantity\QuantityDTO;
use Doctrine\Common\Annotations\Annotation\Enum;
use Doctrine\Common\Collections\ArrayCollection;
use ReflectionException;
use ReflectionProperty;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

final class OfferDTO implements OfferInterface
{
    /** ID */
    private ProductOfferUid $id;
    
    /** ID торгового предложения категории */
    private OffersUid $offer;
    
    /** Заполненное значение */
    private ?string $value = null;
    
    /** Артикул */
    private ?string $article = null;
    
    /** Стоимость торгового предложения */
    private ?PriceDTO $price = null;
    
    /** Количественный учет */
    private ?Quantity\QuantityDTO $quantity = null;
    
    
    /** Дополнительные фото торгового предложения */
    private ArrayCollection $images;
    
    /** Постоянный уникальный идентификатор ТП */
    private readonly ProductOfferConst $const;
    
    
    
    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->price = new PriceDTO();
        $this->quantity = new Quantity\QuantityDTO();
    }
    
    /**
     * @param ProductOfferUid $id
     */
    public function setId(ProductOfferUid $id) : void
    {
        $this->id = $id;
    }
    
    public function equals(ProductOfferUid $id) : bool
    {
        return $this->id->equals($id);
    }
    
    
    /* OFFER */
    /**
     * @return OffersUid
     */
    public function getOffer() : OffersUid
    {
        return $this->offer;
    }

    public function setOffer(OffersUid|string $offer) : void
    {
        $this->offer = $offer instanceof OffersUid ? $offer : new OffersUid($offer);
    }
    
    /* IMAGE */
    
    /**
     * @return ArrayCollection
     */
    public function getImages() : ArrayCollection
    {
        return $this->images;
    }
    
    public function addImage(ImageCollectionDTO $image) : void
    {
        if(!$this->images->contains($image))
        {
            $this->images[] = $image;
        }
    }
    
    public function removeImage(ImageCollectionDTO $image) : void
    {
        $this->images->removeElement($image);
    }
    
    /** Метод для инициализации и маппинга сущности на DTO в коллекции  */
    public function getImageClass() : ImageCollectionDTO
    {
        return new ImageCollectionDTO();
    }
    


    
    /* VALUE */
    
    /**
     * @return string|null
     */
    public function getValue() : ?string
    {
        return $this->value;
    }
    
    /**
     * @param string|null $value
     */
    public function setValue(mixed $value) : void
    {
        $this->value = is_string($value) ? $value : $value->value;
    }
    
    /* PRICE */

    public function getPrice() : ?PriceDTO
    {
        return $this->price;
    }
    
    /**
     * @param PriceDTO $price
     */
    public function setPrice(PriceDTO $price) : void
    {
        $this->price = $price;
    }
    
    /** Метод для инициализации и маппинга сущности на DTO в коллекции  */
    public function getPriceClass() : PriceDTO
    {
        return new PriceDTO();
    }
    
    
    
    /* ARTICLE */
    
    /**
     * @return string|null
     */
    public function getArticle() : ?string
    {
        return $this->article;
    }
    
    /**
     * @param string|null $article
     */
    public function setArticle(?string $article) : void
    {
        $this->article = $article;
    }
    
    
    /* CONST */
    
    /**
     * @return ProductOfferConst
     * @throws ReflectionException
     */
    public function getConst() : ProductOfferConst
    {
        (new ReflectionProperty($this, 'const'))->isInitialized($this) ?: $this->const = new ProductOfferConst();
        
        return $this->const;
    }
    
    /**
     * @param ProductOfferConst $const
     */
    public function setConst(ProductOfferConst $const) : void
    {
        $this->const = $const;
    }
    
    
    
    
    /* QUANTITY */
    
    /**
     * @return QuantityDTO|null
     */
    public function getQuantity() : ?Quantity\QuantityDTO
    {
        return $this->quantity;
    }
    
    public function setQuantity(?Quantity\QuantityDTO $quantity) : void
    {
        $this->quantity = $quantity;
    }
    
    /** Метод для инициализации и маппинга сущности на DTO в коллекции  */
    public function getQuantityClass() : ProductQuantityInterface
    {
        return new Quantity\QuantityDTO();
    }
    
}

