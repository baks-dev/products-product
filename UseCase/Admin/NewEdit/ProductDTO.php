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

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit;

use BaksDev\Products\Product\Entity\Active\ActiveInterface;
use BaksDev\Products\Product\Entity\Category\CategoryInterface;
use BaksDev\Products\Product\Entity\Event\ProductEventInterface;
use BaksDev\Products\Product\Entity\Files\FilesInterface;
use BaksDev\Products\Product\Entity\Offers\OffersInterface;
use BaksDev\Products\Product\Entity\Photo\PhotoInterface;
use BaksDev\Products\Product\Entity\Price\PriceInterface;
use BaksDev\Products\Product\Entity\Property\PropertyInterface;
use BaksDev\Products\Product\Entity\Seo\SeoInterface;
use BaksDev\Products\Product\Entity\Trans\TransInterface;
use BaksDev\Products\Product\Entity\Video\VideoInterface;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Active\ActiveDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Category\CategoryCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Files\FilesCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Info\InfoDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\OffersCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Photo\PhotoCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Price\PriceDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Property\PropertyCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Seo\SeoCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Video\VideoCollectionDTO;
use BaksDev\Core\Type\Locale\Locale;
use ArrayIterator;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;

final class ProductDTO implements ProductEventInterface
{
    /** Идентификатор */
    #[Assert\Uuid]
    private ?ProductEventUid $id = null;
    
    #[Assert\Valid]
    private InfoDTO $info;
    
    #[Assert\Valid]
    private ActiveDTO $active;
    
    #[Assert\Valid]
    private PriceDTO $price;
    
    #[Assert\Valid]
    private ArrayCollection $category;
    
    #[Assert\Valid]
    private ArrayCollection $files;
    
    #[Assert\Valid]
    private ArrayCollection $videos;
    
    #[Assert\Valid]
    private ArrayCollection $offers;
    
    #[Assert\Valid]
    private ArrayCollection $photos;
    
    #[Assert\Valid]
    private ArrayCollection $property;

    #[Assert\Valid]
    private ArrayCollection $seo;
    
    #[Assert\Valid]
    private ArrayCollection $trans;
    
    
    private ArrayCollection $collectionProperty;
    
    public function __construct() {
        $this->info = new InfoDTO();
        $this->active = new ActiveDTO();
        $this->price = new PriceDTO();
        
        $this->category = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->offers = new ArrayCollection();
        $this->photos = new ArrayCollection();
        $this->property = new ArrayCollection();
        $this->seo = new ArrayCollection();
        $this->trans = new ArrayCollection();
        $this->videos = new ArrayCollection();
    }
    
    public function getEvent() : ?ProductEventUid
    {
        return $this->id;
    }
    
    public function setId(ProductEventUid $id) : void
    {
        $this->id = $id;
    }
    
    
    /* INFO  */
    
    /**
     * @return InfoDTO
     */
    public function getInfo() : InfoDTO
    {
        return $this->info;
    }
    
    /**
     * @param InfoDTO $info
     */
    public function setInfo(InfoDTO $info) : void
    {
        $this->info = $info;
    }
    
    

    
    
    /* CATEGORIES */
    
    public function addCategory(CategoryCollectionDTO $category) : void
    {
        if(!$this->category->contains($category))
        {
            $this->category[] = $category;
        }
    }

    public function removeCategory(CategoryCollectionDTO $category) : void
    {
        $this->category->removeElement($category);
    }
    
    /**
     * @return ArrayCollection
     */
    public function getCategory() : ArrayCollection
    {
        if($this->category->isEmpty())
        {
            $CategoryCollectionDTO = new CategoryCollectionDTO();
            $this->addCategory($CategoryCollectionDTO);
        }

        return $this->category;
    }
    
    /** Метод для инициализации и маппинга сущности на DTO в коллекции  */
    public function getCategoryClass() : CategoryInterface
    {
        return new CategoryCollectionDTO();
    }
    
    
    
    /* ACTIVE */
    
    
    /**
     * @return ActiveDTO
     */
    public function getActive() : ActiveDTO
    {
        return $this->active;
    }
    
    /**
     * @param ActiveDTO $active
     */
    public function setActive(ActiveDTO $active) : void
    {
        $this->active = $active;
    }
    
    /** Метод для инициализации и маппинга сущности на DTO в коллекции  */
    public function getActiveClass() : ActiveInterface
    {
        return new ActiveDTO();
    }
    
    /* FILES */
    
    /**
     * @return ArrayCollection
     */
    public function getFiles() : ArrayCollection
    {
        if($this->files->isEmpty())
        {
            $this->addFile(new FilesCollectionDTO());
        }
        
        
        return $this->files;
    }
    

    public function addFile(FilesCollectionDTO $file) : void
    {
        if(!$this->files->contains($file))
        {
            $this->files[] = $file;
        }
    }
    
    public function removeFile(FilesCollectionDTO $file) : void
    {
        $this->files->removeElement($file);
    }
    
    /** Метод для инициализации и маппинга сущности на DTO в коллекции  */
    public function getFileClass() : FilesInterface
    {
        return new FilesCollectionDTO();
    }
    
    
    /* OFFERS */
    
    /**
     * @return ArrayCollection
     */
    public function getOffers() : ArrayCollection
    {
//        if($this->offers->isEmpty())
//        {
//            $this->addOffer(new OffersCollectionDTO());
//        }
        
        return $this->offers;
    }
    
    public function addOffer(OffersCollectionDTO $offer) : void
    {
        if(!$this->offers->contains($offer))
        {
            $this->offers[] = $offer;
        }
    }
    
    public function removeOffer(OffersCollectionDTO $offer) : void
    {
        $this->offers->removeElement($offer);
    }
    
    /** Метод для инициализации и маппинга сущности на DTO в коллекции  */
    public function getOfferClass() : OffersInterface
    {
        return new OffersCollectionDTO();
    }
    
    
    /* PHOTOS */
    
    /**
     * @return ArrayCollection
     */
    public function getPhotos() : ArrayCollection
    {
        if($this->photos->isEmpty())
        {
            $this->addPhoto(new PhotoCollectionDTO());
        }
        
        return $this->photos;
    }
    
    public function addPhoto(PhotoCollectionDTO $photo) : void
    {
        if(!$this->photos->contains($photo))
        {
            $this->photos[] = $photo;
        }
    }
    
    public function removePhoto(PhotoCollectionDTO $photo) : void
    {
        $this->photos->removeElement($photo);
    }
    
    /** Метод для инициализации и маппинга сущности на DTO в коллекции  */
    public function getPhotoClass() : PhotoInterface
    {
        return new PhotoCollectionDTO();
    }
    
    /* VIDEOS */
    
    
    /* PHOTOS */
    
    /**
     * @return ArrayCollection
     */
    public function getVideos() : ArrayCollection
    {
        if($this->videos->isEmpty())
        {
            $this->addVideo(new VideoCollectionDTO());
        }
        
        return $this->videos;
    }
    
    public function addVideo(VideoCollectionDTO $video) : void
    {
        if(!$this->videos->contains($video))
        {
            $this->videos[] = $video;
        }
    }
    
    public function removeVideo(VideoCollectionDTO $video) : void
    {
        $this->photos->removeElement($video);
    }
    
    /** Метод для инициализации и маппинга сущности на DTO в коллекции  */
    public function getVideoClass() : VideoInterface
    {
        return new VideoCollectionDTO();
    }
    
    
    /* PRICE */
    
    /**
     * @return PriceDTO
     */
    public function getPrice() : PriceDTO
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
    public function getPriceClass() : PriceInterface
    {
        return new PriceDTO();
    }
    
    
    /* PROPERTIES */
    
    /**
     * @return ArrayIterator
     * @throws Exception
     */
    public function getProperty() : ArrayIterator
    {

        $iterator = $this->property->getIterator();
    
        $iterator->uasort(function ($first, $second) {
            return  $first->getSection() >  $second->getSection() ? -1 : 1;
        });
        
        return $iterator;
    }
    
    
    
    
    
    public function addProperty(PropertyCollectionDTO $property) : void
    {
        if(!$this->property->contains($property))
        {
            $this->property[] = $property;
        }
    }
    
    public function removeProperty(PropertyCollectionDTO $property) : void
    {
        $this->property->removeElement($property);
    }
    
    /** Метод для инициализации и маппинга сущности на DTO в коллекции  */
    public function getPropertyClass() : PropertyInterface
    {
        return new PropertyCollectionDTO();
    }
    

    /* SEO  */
    
    
    public function addSeo(SeoCollectionDTO $seo) : void
    {
        if(!$this->seo->contains($seo))
        {
            $this->seo[] = $seo;
        }
    }
    
    public function removeSeo(SeoCollectionDTO $seo) : void
    {
        $this->seo->removeElement($seo);
    }
    
    
    /**
     * @return ArrayCollection
     */
    public function getSeo() : ArrayCollection
    {
        
        /* Вычисляем расхождение и добавляем неопределенные локали */
        foreach(Locale::diffLocale($this->seo) as $locale)
        {
            $CategorySeoDTO = new SeoCollectionDTO();
            $CategorySeoDTO->setLocal($locale);
            $this->addSeo($CategorySeoDTO);
        }
        
        return $this->seo;
    }
    
    /** Метод для инициализации и маппинга сущности на DTO в коллекции  */
    public function getSeoClass() : SeoInterface
    {
        return new SeoCollectionDTO();
    }
    
    
    /* TRANS */
    
    
    /**
     * @param ArrayCollection $trans
     */
    public function setTrans(ArrayCollection $trans) : void
    {
        $this->trans = $trans;
    }
    
    
    /**
     * @return ArrayCollection
     */
    public function getTrans() : ArrayCollection
    {
        /* Вычисляем расхождение и добавляем неопределенные локали */
        foreach(Locale::diffLocale($this->trans) as $locale)
        {
            $ProductTransDTO = new Trans\ProductTransDTO();
            $ProductTransDTO->setLocal($locale);
            $this->addTran($ProductTransDTO);
        }
        
        return $this->trans;
    }
    
    /** Добавляем перевод категории
     * @param Trans\ProductTransDTO $trans
     * @return void
     */
    public function addTran(Trans\ProductTransDTO $trans) : void
    {
        if(!$this->trans->contains($trans))
        {
            $this->trans[] = $trans;
        }
    }
    
    /** Метод для инициализации и маппинга сущности на DTO в коллекции  */
    public function getTranClass() : TransInterface
    {
        return new Trans\ProductTransDTO();
    }
    
    
    
    
}

