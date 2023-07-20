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

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit;

use ArrayIterator;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Products\Product\Entity\Event\ProductEventInterface;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Active\ActiveDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Category\CategoryCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Files\FilesCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Info\InfoDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Photo\PhotoCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Price\PriceDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Property\PropertyCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Seo\SeoCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Video\VideoCollectionDTO;
use Doctrine\Common\Collections\ArrayCollection;
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
    private ArrayCollection $file;

    #[Assert\Valid]
    private ArrayCollection $video;

    #[Assert\Valid]
    private ArrayCollection $offer;

    #[Assert\Valid]
    private ArrayCollection $photo;

    #[Assert\Valid]
    private ArrayCollection $property;

    #[Assert\Valid]
    private ArrayCollection $seo;

    #[Assert\Valid]
    private ArrayCollection $translate;


    //private ArrayCollection $collectionProperty;

    public function __construct()
    {
        $this->info = new InfoDTO();
        $this->active = new ActiveDTO();
        $this->price = new PriceDTO();

        $this->category = new ArrayCollection();
        $this->file = new ArrayCollection();
        $this->offer = new ArrayCollection();
        $this->photo = new ArrayCollection();
        $this->property = new ArrayCollection();
        $this->seo = new ArrayCollection();
        $this->translate = new ArrayCollection();
        $this->video = new ArrayCollection();
    }


    public function getEvent(): ?ProductEventUid
    {
        return $this->id;
    }


    public function setId(ProductEventUid $id): void
    {
        $this->id = $id;
    }


    /* INFO  */

    public function getInfo(): InfoDTO
    {
        return $this->info;
    }


    public function setInfo(InfoDTO $info): void
    {
        $this->info = $info;
    }


    /* CATEGORIES */

    public function addCategory(CategoryCollectionDTO $category): void
    {
        $filter = $this->category->filter(function(CategoryCollectionDTO $element) use ($category)
            {
                return $category->getCategory()?->equals($element->getCategory());
            });

        if($filter->isEmpty())
        {
            $this->category[] = $category;
        }
    }


    public function removeCategory(CategoryCollectionDTO $category): void
    {
        $this->category->removeElement($category);
    }


    /**
     * @return ArrayCollection
     */
    public function getCategory(): ArrayCollection
    {
        if($this->category->isEmpty())
        {
            $CategoryCollectionDTO = new CategoryCollectionDTO();
            $this->addCategory($CategoryCollectionDTO);
        }

        return $this->category;
    }


    /* ACTIVE */

    /**
     * @return ActiveDTO
     */
    public function getActive(): ActiveDTO
    {
        return $this->active;
    }


    /**
     * @param ActiveDTO $active
     */
    public function setActive(ActiveDTO $active): void
    {
        $this->active = $active;
    }


    /** Метод для инициализации и маппинга сущности на DTO в коллекции  */
    public function getActiveClass(): ActiveDTO
    {
        return new ActiveDTO();
    }

    /* FILES */

    /**
     * @return ArrayCollection
     */
    public function getFile(): ArrayCollection
    {
        if($this->file->isEmpty())
        {
            $this->addFile(new FilesCollectionDTO());
        }

        return $this->file;
    }


    public function addFile(FilesCollectionDTO $file): void
    {
        if(!$this->file->contains($file))
        {
            $this->file->add($file);
        }
    }


    public function removeFile(FilesCollectionDTO $file): void
    {
        $this->file->removeElement($file);
    }


    /* OFFERS */

    public function getOffer(): ArrayCollection
    {
        return $this->offer;
    }


    public function addOffer(Offers\ProductOffersCollectionDTO $offer): void
    {

        $filter = $this->offer->filter(function(Offers\ProductOffersCollectionDTO $element) use ($offer)
            {
                return $offer->getValue() === $element->getValue();
            });

        if($filter->isEmpty())
        {
            $this->offer->add($offer);
        }

    }


    public function removeOffer(Offers\ProductOffersCollectionDTO $offer): void
    {
        $this->offer->removeElement($offer);
    }


    /* PHOTOS */

    /**
     * @return ArrayCollection
     */
    public function getPhoto(): ArrayCollection
    {
        if($this->photo->isEmpty())
        {
            $this->addPhoto(new PhotoCollectionDTO());
        }

        return $this->photo;
    }


    public function addPhoto(PhotoCollectionDTO $photo): void
    {

        $filter = $this->photo->filter(function(PhotoCollectionDTO $element) use ($photo)
            {
                return $photo->getName() === $element->getName();
            });

        if($filter->isEmpty())
        {
            $this->photo->add($photo);
        }

    }


    public function removePhoto(PhotoCollectionDTO $photo): void
    {
        $this->photo->removeElement($photo);
    }


    public function getVideo(): ArrayCollection
    {
        if($this->video->isEmpty())
        {
            $this->addVideo(new VideoCollectionDTO());
        }

        return $this->video;
    }


    public function addVideo(VideoCollectionDTO $video): void
    {
        if(!$this->video->contains($video))
        {
            $this->video->add($video);
        }
    }


    public function removeVideo(VideoCollectionDTO $video): void
    {
        $this->video->removeElement($video);
    }



    /* PRICE */

    /**
     * @return PriceDTO
     */
    public function getPrice(): PriceDTO
    {
        return $this->price;
    }


    /**
     * @param PriceDTO $price
     */
    public function setPrice(PriceDTO $price): void
    {
        $this->price = $price;
    }


    /* PROPERTIES */
    public function getProperty(): ArrayIterator
    {

        $iterator = $this->property->getIterator();

        $iterator->uasort(function($first, $second)
            {

                return $first->getSort() > $second->getSort() ? 1 : -1;
            });

        return $iterator;
    }


    public function addProperty(PropertyCollectionDTO $property): void
    {

        $filter = $this->property->filter(function(PropertyCollectionDTO $element) use ($property)
            {
                return $property->getField()?->equals($element->getField());
            });

        if($filter->isEmpty())
        {
            $this->property->add($property);
        }

    }


    public function removeProperty(PropertyCollectionDTO $property): void
    {
        $this->property->removeElement($property);
    }


    /* SEO  */

    public function addSeo(SeoCollectionDTO $seo): void
    {
        if(!$this->seo->contains($seo))
        {
            $this->seo->add($seo);
        }
    }


    public function removeSeo(SeoCollectionDTO $seo): void
    {
        $this->seo->removeElement($seo);
    }


    public function getSeo(): ArrayCollection
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


    /* TRANS */

    public function setTranslate(ArrayCollection $trans): void
    {
        $this->translate = $trans;
    }


    public function getTranslate(): ArrayCollection
    {

        /* Вычисляем расхождение и добавляем неопределенные локали */
        foreach(Locale::diffLocale($this->translate) as $locale)
        {
            $ProductTransDTO = new Trans\ProductTransDTO();
            $ProductTransDTO->setLocal($locale);
            $this->addTranslate($ProductTransDTO);
        }

        return $this->translate;
    }


    public function addTranslate(Trans\ProductTransDTO $trans): void
    {
        if(!$this->translate->contains($trans))
        {
            $this->translate->add($trans);
        }
    }

}

