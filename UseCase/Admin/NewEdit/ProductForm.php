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

use BaksDev\Core\Services\Reference\ReferenceChoice;
use BaksDev\Products\Category\Repository\CategoryOffersForm\CategoryOffersFormInterface;
use BaksDev\Products\Category\Repository\CategoryPropertyById\CategoryPropertyByIdInterface;
use BaksDev\Products\Category\Repository\CategoryVariationForm\CategoryVariationFormInterface;
use BaksDev\Products\Product\UseCase\Admin\NewEdit;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Category\CategoryCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Property\PropertyCollectionDTO;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductForm extends AbstractType
{
	
	private CategoryPropertyByIdInterface $categoryProperty;
	
	private CategoryOffersFormInterface $categoryOffers;
	
	private CategoryVariationFormInterface $categoryVariation;
	
	private ReferenceChoice $reference;
	
	public function __construct(
		CategoryPropertyByIdInterface $categoryProperty,
		CategoryOffersFormInterface $categoryOffers,
		CategoryVariationFormInterface $categoryVariation,
		ReferenceChoice $reference
	)
	{
		
		$this->categoryProperty = $categoryProperty;
		$this->categoryOffers = $categoryOffers;
		$this->categoryVariation = $categoryVariation;
		$this->reference = $reference;
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
			'prototype_name' => '__categories__',
		]);
		
		/* FILES Collection */
		$builder->add('file', CollectionType::class, [
			'entry_type' => NewEdit\Files\FilesCollectionForm::class,
			'entry_options' => ['label' => false],
			'label' => false,
			'by_reference' => false,
			'allow_delete' => true,
			'allow_add' => true,
			'prototype_name' => '__files__',
		]);
		
		/* SEO Collection */
		$builder->add('seo', CollectionType::class, [
			'entry_type' => NewEdit\Seo\SeoCollectionForm::class,
			'entry_options' => ['label' => false],
			'label' => false,
			'by_reference' => false,
			'allow_delete' => true,
			'allow_add' => true,
			'prototype_name' => '__seo__',
		]);
		
		/* TRANS CollectionType */
		$builder->add('translate', CollectionType::class, [
			'entry_type' => NewEdit\Trans\ProductTransForm::class,
			'entry_options' => ['label' => false],
			'label' => false,
			'by_reference' => false,
			'allow_delete' => true,
			'allow_add' => true,
			'prototype_name' => '__trans__',
		]);
		
		/* PHOTOS CollectionType */
		$builder->add('photo', CollectionType::class, [
			'entry_type' => NewEdit\Photo\PhotoCollectionForm::class,
			'entry_options' => ['label' => false],
			'label' => false,
			'by_reference' => false,
			'allow_delete' => true,
			'allow_add' => true,
			'prototype_name' => '__photos__',
		]);
		
		/* FILES CollectionType */
		$builder->add('file', CollectionType::class, [
			'entry_type' => NewEdit\Files\FilesCollectionForm::class,
			'entry_options' => ['label' => false],
			'label' => false,
			'by_reference' => false,
			'allow_delete' => true,
			'allow_add' => true,
			'prototype_name' => '__files__',
		]);
		
		/* VIDEOS CollectionType */
		$builder->add('video', CollectionType::class, [
			'entry_type' => NewEdit\Video\VideoCollectionForm::class,
			'entry_options' => ['label' => false],
			'label' => false,
			'by_reference' => false,
			'allow_delete' => true,
			'allow_add' => true,
			'prototype_name' => '__videos__',
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
			'prototype_name' => '__properties__',
		]);
		
		
		
		
		
		$builder->addEventListener(
			FormEvents::PRE_SET_DATA,
			function(FormEvent $event) use ($propertyCategory) {
				
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
				
							/* ???????? ???????? ?????? ?????????????????? - ???? ?????????????????? */
							if($propCat->fieldUid->equals($fieldProperty->getField()))
							{
								$fieldProperty->setSection($propCat->sectionUid);
								
								$new = false;
								break;
							}
							
							/* ?????????????? ????????????????, ?????????????? ???????? ?????????????? ???? ?????????????????? */
							if(!isset($propertyCategory[(string) $fieldProperty->getField()]))
							{
								$data->removeProperty($fieldProperty);
							}
							
						}
						
						/* ???????? ???????? ???? ?????????????????? ?????????? - ?????????????? */
						if($new)
						{
							$Property = new PropertyCollectionDTO();
							$Property->setField($propCat->fieldUid);
							$Property->setSection($propCat->sectionUid);
							$data->addProperty($Property);
						}
						
					}
				}
			}
		);
		
		/* ?????????????????? ******************************************************/
		$builder->add(
			'Save',
			SubmitType::class,
			['label' => 'Save', 'label_html' => true, 'attr' => ['class' => 'btn-primary']]
		);
		
		/*
		 * OFFERS
		*/
		
		//$offers = $category ? $this->categoryOffers->get($category->getCategory()) : null; //  $this->getField->get($profileType);
		
		/** ?????????????? ???????????????? ??????????????????????  */
		$offersCategory = $category->getCategory() ? $this->categoryOffers->get($category->getCategory()) : null;
		
		/* ???????????????? ?????????????????????????? ???????????????? ???? */
		
		$variationCategory = $offersCategory ? $this->categoryVariation->get($offersCategory->id) : null;
		
		$builder->add('offer', CollectionType::class, [
			'entry_type' => NewEdit\Offers\ProductOffersCollectionForm::class,
			'entry_options' => [
				'label' => false,
				//'category_id' => $category,
				'variation' => $variationCategory,
				'offers' => $offersCategory,
			],
			'label' => false,
			'by_reference' => false,
			'allow_delete' => true,
			'allow_add' => true,
			'prototype_name' => '__offers__',
		]);
		
		
		if($offersCategory)
		{
			$builder->addEventListener(
				FormEvents::PRE_SET_DATA,
				function(FormEvent $event) use ($offersCategory, $variationCategory)
				{
					
					/* @var ProductDTO $data */
					$data = $event->getData();
					$form = $event->getForm();
					
					
					if(!empty($offersCategory))
					{
						/* ?????????????? ???????????????? ?? ?????????????????????????????? ???? ?????? ?????????????????? */
						$form->add('dataOffer', HiddenType::class,  ['data' => $offersCategory->id, 'mapped' => false]);
						
						if($offersCategory->reference)
						{
							$reference = $this->reference->getChoice($offersCategory->reference);
							
							if($reference)
							{
								$form
									->add('data-offer-reference', ChoiceType::class, [
										'choices' => $reference->choice(),
										'choice_value' => function($choice) {
											return $choice?->getType()->value;
										},
										'choice_label' => function($choice) {
											return $choice?->getType()->value;
										},
										
										//'choice_translation_domain' => 'reference.'.$offer->reference,
										'required' => false,
										'label' => false,
										'expanded' => false,
										'multiple' => false,
										'mapped' => false,
										'placeholder' => 'placeholder',
										'translation_domain' => $reference->domain(),
										'attr' => ['style' => 'display: none;' ]
									])
								;
							}
						}
					}
					
					
					if(!empty($variationCategory))
					{
						/* ?????????????? ???????????????? ?? ?????????????????????????????? ???????????????????????????? ???????????????? ?????? ?????????????????? */
						$form->add('dataVariation', HiddenType::class,  ['data' => $variationCategory->id, 'mapped' => false]);
						
						if($variationCategory->reference)
						{
							
							//$form->add('data-offer-reference', HiddenType::class,  ['data' => $offersCategory->id, 'mapped' => false]);
							
							$reference = $this->reference->getChoice($variationCategory->reference);
							
							if($reference)
							{
								$form
									->add('data-variation-reference', ChoiceType::class, [
										'choices' => $reference->choice(),
										'choice_value' => function($choice) {
											return $choice?->getType()->value;
										},
										'choice_label' => function($choice) {
											return $choice?->getType()->value;
										},
										
										//'choice_translation_domain' => 'reference.'.$offer->reference,
										
										'required' => false,
										'label' => false,
										'expanded' => false,
										'multiple' => false,
										'mapped' => false,
										'placeholder' => 'placeholder',
										'translation_domain' => $reference->domain(),
										'attr' => ['style' => 'display: none;' ]
									])
								;
							}
						}
					}
					
					
					if(!empty($offersCategory) && $data->getOffer()->isEmpty())
					//if(!empty($offersCategory))
					{
						

						
						$ProductOffersCollectionDTO = new NewEdit\Offers\ProductOffersCollectionDTO();
						$ProductOffersCollectionDTO->setCategoryOffer($offersCategory->id);
						
						if($offersCategory->image)
						{
							$ProductOfferImageCollectionDTO = new NewEdit\Offers\Image\ProductOfferImageCollectionDTO();
							$ProductOfferImageCollectionDTO->setRoot(true);
							$ProductOffersCollectionDTO->addImage($ProductOfferImageCollectionDTO);
						}
						
						
						//		                  foreach($offersCategory as $offer)
						//		                  {
						//		                      $offerDTO = new NewEdit\Offers\Offer\OfferDTO();
						//		                      $offerDTO->setOffer($offer->id);
						//		                      $offers->addOffer($offerDTO);
						//		                  }
						
						
						if($variationCategory)
						{
							
	
							$ProductOffersVariationCollectionDTO = new NewEdit\Offers\Variation\ProductOffersVariationCollectionDTO();
							$ProductOffersVariationCollectionDTO->setCategoryVariation($variationCategory->id);
							
							if($variationCategory->image)
							{
								$ProductOfferVariationImageCollectionDTO = new NewEdit\Offers\Variation\Image\ProductOfferVariationImageCollectionDTO();
								$ProductOfferVariationImageCollectionDTO->setRoot(true);
								$ProductOffersVariationCollectionDTO->addImage($ProductOfferVariationImageCollectionDTO);
							}
							
							
							$ProductOffersCollectionDTO->addVariation($ProductOffersVariationCollectionDTO);
						}
						
						$data->addOffer($ProductOffersCollectionDTO);
					}
					
				}
			);
			
			
			
		}
		
		

		
	}
	
	
	public function configureOptions(OptionsResolver $resolver) : void
	{
		$resolver->setDefaults
		(
			[
				'data_class' => ProductDTO::class,
				'method' => 'POST',
				'attr' => ['class' => 'w-100'],
			]
		);
	}
	
}
