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

namespace App\Module\Products\Product\UseCase\Admin\NewEdit;

use App\Module\Products\Category\Repository\CategoryOffersForm\CategoryOffersFormInterface;
use App\Module\Products\Category\Repository\CategoryPropertyById\CategoryPropertyByIdInterface;
use App\Module\Products\Product\UseCase\Admin\NewEdit;
use App\Module\Products\Product\UseCase\Admin\NewEdit\Category\CategoryCollectionDTO;
use App\Module\Products\Product\UseCase\Admin\NewEdit\Property\PropertyCollectionDTO;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ProductForm extends AbstractType
{
    
    private CategoryPropertyByIdInterface $categoryProperty;
    private CategoryOffersFormInterface $categoryOffers;
    
    public function __construct(
      CategoryPropertyByIdInterface $categoryProperty,
      CategoryOffersFormInterface $categoryOffers
    )
    {
        
        $this->categoryProperty = $categoryProperty;
        $this->categoryOffers = $categoryOffers;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        
        $builder->add('info', Info\InfoForm::class, ['label' => false]);
        
        $builder->add('active', Active\ActiveForm::class, ['label' => false]);
        
        $builder->add('price', Price\PriceForm::class, ['label' => false]);
        
        /* CATEGORIES CollectionType */
        $builder->add('category', CollectionType::class, [
          'entry_type' => NewEdit\Category\CategoryCollectionForm::class,
          'entry_options' => ['label' => false],
          'label' => false,
          'by_reference' => false,
          'allow_delete' => true,
          'allow_add' => true,
          'prototype_name' => '__categories__'
        ]);
        
        /* FILES Collection */
        $builder->add('files', CollectionType::class, [
          'entry_type' => NewEdit\Files\FilesCollectionForm::class,
          'entry_options' => ['label' => false],
          'label' => false,
          'by_reference' => false,
          'allow_delete' => true,
          'allow_add' => true,
          'prototype_name' => '__files__'
        ]);
        
        /* SEO Collection */
        $builder->add('seo', CollectionType::class, [
          'entry_type' => NewEdit\Seo\SeoCollectionForm::class,
          'entry_options' => ['label' => false],
          'label' => false,
          'by_reference' => false,
          'allow_delete' => true,
          'allow_add' => true,
          'prototype_name' => '__seo__'
        ]);
        
        /* TRANS CollectionType */
        $builder->add('trans', CollectionType::class, [
          'entry_type' => NewEdit\Trans\ProductTransForm::class,
          'entry_options' => ['label' => false],
          'label' => false,
          'by_reference' => false,
          'allow_delete' => true,
          'allow_add' => true,
          'prototype_name' => '__trans__'
        ]);
        
        /* PHOTOS CollectionType */
        $builder->add('photos', CollectionType::class, [
          'entry_type' => NewEdit\Photo\PhotoCollectionForm::class,
          'entry_options' => ['label' => false],
          'label' => false,
          'by_reference' => false,
          'allow_delete' => true,
          'allow_add' => true,
          'prototype_name' => '__photos__'
        ]);
        
        /* FILES CollectionType */
        $builder->add('files', CollectionType::class, [
          'entry_type' => NewEdit\Files\FilesCollectionForm::class,
          'entry_options' => ['label' => false],
          'label' => false,
          'by_reference' => false,
          'allow_delete' => true,
          'allow_add' => true,
          'prototype_name' => '__files__'
        ]);
        
        /* VIDEOS CollectionType */
        $builder->add('videos', CollectionType::class, [
          'entry_type' => NewEdit\Video\VideoCollectionForm::class,
          'entry_options' => ['label' => false],
          'label' => false,
          'by_reference' => false,
          'allow_delete' => true,
          'allow_add' => true,
          'prototype_name' => '__videos__'
        ]);
        
        /*
         * PROPERTIES
        */
        
        /* @var ArrayCollection $categories */
        $categories = $options['data']->getCategory();
        /* @var CategoryCollectionDTO $category */
        $category = $categories->current();
        
        $propertyCategory = $category->getCategory() ? $this->categoryProperty->get($category->getCategory()) : null;
        
        /* CollectionType */
        $builder->add('property', CollectionType::class, [
          'entry_type' => NewEdit\Property\PropertyCollectionForm::class,
          'entry_options' => [
            'label' => false,
            'properties' => $propertyCategory,
          ],
          'label' => false,
          'by_reference' => false,
          'allow_delete' => true,
          'allow_add' => true,
          'prototype_name' => '__properties__'
        ]);
        
        $builder->addEventListener(
          FormEvents::PRE_SET_DATA,
          function (FormEvent $event) use ($propertyCategory)
          {
              
              /* @var ProductDTO $data */
              $data = $event->getData();
              $form = $event->getForm();
              
              if($data && $propertyCategory)
              {
                  
                  foreach($propertyCategory as $key => $propCat)
                  {
                      $new = true;
                      
                      foreach($data->getProperty() as $fieldProperty)
                      {
                          if($propCat->fieldUid === $fieldProperty->getField())
                          {
                              $fieldProperty->setSection($propCat->sectionUid);
                              
                              $new = false;
                              break;
                          }
                          
                          /* Удаляем свойства, Которые были удалены из категории */
                          if(!isset($propertyCategory[(string) $fieldProperty->getField()]))
                          {
                              $data->removeProperty($fieldProperty);
                          }
                          
                      }
                      
                      /* Если поле не заполнено ранее - создаем */
                      if($new)
                      {
                          $Property = new PropertyCollectionDTO();
                          $Property->setField($propCat->fieldUid);
                          $Property->setSection($propCat->sectionUid);
                          $data->addProperty($Property);
                      }
                      
                  }
              }
          });
        
        /*
         * OFFERS
        */
        
        //$offers = $category ? $this->offers->get($category) : null; //  $this->getField->get($profileType);
        
        $offersCategory = $category->getCategory() ? $this->categoryOffers->get($category->getCategory()) : null;
        
        $builder->add('offers', CollectionType::class, [
          'entry_type' => NewEdit\Offers\OffersCollectionForm::class,
          'entry_options' => [
            'label' => false,
            //'category_id' => $category,
            'offer_data' => $options['data'],
            'offers' => $offersCategory,
          ],
          'label' => false,
          'by_reference' => false,
          'allow_delete' => true,
          'allow_add' => true,
          'prototype_name' => '__offers__'
        ]);
        
        $builder->addEventListener(
          FormEvents::PRE_SET_DATA,
          function (FormEvent $event) use ($offersCategory)
          {
              
              /* @var ProductDTO $data */
              $data = $event->getData();
              $form = $event->getForm();
              
              if(!empty($offersCategory) && $data->getOffers()->isEmpty())
              {
                  $offers = new NewEdit\Offers\OffersCollectionDTO();
                  
                  foreach($offersCategory as $offer)
                  {
                      $offerDTO = new NewEdit\Offers\Offer\OfferDTO();
                      $offerDTO->setOffer($offer->id);
                      $offers->addOffer($offerDTO);
                  }
                  
                  $data->addOffer($offers);
              }
              
          });
        
    }
    
    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults
        (
          [
            'data_class' => ProductDTO::class,
            'method' => 'POST',
            'attr' => ['class' => 'w-100'],
          ]);
    }
    
}
