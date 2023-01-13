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

namespace App\Module\Products\Product\Entity\Offers\Offer;

use App\Module\Products\Category\Type\Offers\Id\OffersUid;
use App\Module\Products\Product\Entity\Offers\Offers;

use App\Module\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use App\Module\Products\Product\Type\Offers\Id\ProductOfferUid;
use App\System\Entity\EntityEvent;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use InvalidArgumentException;

/* Вариант в торговом предложения */

#[ORM\Entity]
#[ORM\Table(name: 'product_offer')]
#[ORM\Index(columns: ['const'])]
#[ORM\Index(columns: ['article'])]
class Offer extends EntityEvent
{
    public const TABLE = 'product_offer';
    
    /** ID торгового предложения */
    #[ORM\Id]
    #[ORM\Column(type: ProductOfferUid::TYPE)]
    protected ProductOfferUid $id;
    
    /** Постоянный уникальный идентификатор ТП */
    #[ORM\Column(type: ProductOfferConst::TYPE)]
    protected ProductOfferConst $const;
    
    /** ID торгового предложения  */
    #[ORM\ManyToOne(targetEntity: Offers::class, cascade: ['persist'], inversedBy: 'offer')]
    #[ORM\JoinColumn(name: 'product_offers_id', referencedColumnName: 'id')]
    protected Offers $productOffer;

    /** ID торгового предложения категории */
    #[ORM\Column(type: OffersUid::TYPE, nullable: true)]
    protected OffersUid $offer;
    
    /** Заполненное значение */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $value = null;
    
    /** Артикул */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    protected ?string $article = null;
    
    /** Стоимость торгового предложения */
    #[ORM\OneToOne(mappedBy: 'offer', targetEntity: Price\Price::class, cascade: ['all'])]
    protected ?Price\Price $price = null;
    
    /** Количественный учет */
    #[ORM\OneToOne(mappedBy: 'offer', targetEntity: Quantity\ProductQuantity::class, cascade: ['all'])]
    protected ?Quantity\ProductQuantity $quantity = null;
    
    /** Дополнительные фото торгового предложения */
    #[ORM\OneToMany(mappedBy: 'offer', targetEntity: Image\Image::class, cascade: ['all'])]
    protected Collection $images;
    
    public function __construct(Offers $productOffer) {
        $this->id = new ProductOfferUid();
        $this->const = new ProductOfferConst();
        $this->price = new Price\Price($this);
        $this->quantity = new Quantity\ProductQuantity($this);
        $this->productOffer = $productOffer;
        $this->images = new ArrayCollection();
    }
    
    public function __clone()
    {
        $this->id = new ProductOfferUid();
    }
    
    /**
     * @return ProductOfferUid
     */
    public function getId() : ProductOfferUid
    {
        return $this->id;
    }
    
    /**
     * @return OffersUid
     */
    public function getOffer() : OffersUid
    {
        return $this->offer;
    }
    
    /**
     * @return string|null
     */
    public function getValue() : ?string
    {
        return $this->value;
    }

    
    
    /**
     * @throws Exception
     */
    public function getDto($dto) : mixed
    {
        if($dto instanceof OfferInterface)
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
        if($dto instanceof OfferInterface)
        {
            return parent::setEntity($dto);
        }
        
        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

}
