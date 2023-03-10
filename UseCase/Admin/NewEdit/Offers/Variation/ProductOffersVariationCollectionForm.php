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

//namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Offer;

//use App\Module\Product\Entity\Product\Offers\Offer;
namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Variation;

use App\Module\Products\Category\Repository\CategoryOffersForm\CategoryOffersFormDTO;
use BaksDev\Core\Services\Reference\ReferenceChoice;
use BaksDev\Products\Category\Type\Offers\Variation\ProductCategoryOffersVariationUid;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Offer\Image\ImageCollectionForm;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Offer\Price\PriceForm;
use App\System\Type\Reference\ReferenceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ProductOffersVariationCollectionForm extends AbstractType
{
	private ReferenceChoice $reference;
	
	
	public function __construct(ReferenceChoice $reference)
	{
		$this->reference = $reference;
	}
	
	
	public function buildForm(FormBuilderInterface $builder, array $options) : void
	{
		
		$variation = $options['variation'];
		
		$builder->add('categoryVariation', HiddenType::class);
		
		
		$builder->get('categoryVariation')->addModelTransformer(
			new CallbackTransformer(
				function($categoryVariation) {
					return $categoryVariation instanceof ProductCategoryOffersVariationUid ? $categoryVariation->getValue() : $categoryVariation;
				},
				function($categoryVariation) {
					return new ProductCategoryOffersVariationUid($categoryVariation);
				}
			)
		);
		
		
		
		
		$builder->add('article', TextType::class);
		
		$builder->add('value', TextType::class, ['label' => $variation->name, 'attr' => [ 'class' => 'mb-3' ]]);
		
		$builder->add('price', Price\ProductOfferVariationPriceForm::class, ['label' => false]);
		
		$builder->add('quantity', Quantity\ProductOfferVariationQuantityForm::class, ['label' => false]);
		
		/** ???????????????? ?????????????????????? */
		$builder->add('image', CollectionType::class, [
			'entry_type' => Image\ProductOfferVariationImageCollectionForm::class,
			'entry_options' => [
				'label' => false,
			],
			'label' => false,
			'by_reference' => false,
			'allow_delete' => true,
			'allow_add' => true,
			'prototype_name' => '__variation_image__',
		]);
		
		$builder->addEventListener(
			FormEvents::PRE_SET_DATA,
			function(FormEvent $event) use ($variation) {
				$data = $event->getData();
				$form = $event->getForm();
				
				if($data)
				{
					
					/* ???????????????? ???????????? ???????????????? ?????????????????????? ?????????????????? */
					//$offerCat = $data->getOffer();
					
					/* ???????? ???? - ???????????????????? - ?????????????????????? ???????? ChoiceType   */
					if($variation->reference)
					{
						$reference = $this->reference->getChoice($variation->reference);
						
						if($reference)
						{
							$form
								->add('value', ChoiceType::class, [
									'choices' => $reference->choice(),
									'choice_value' => function($choice) {
										if(is_string($choice)) { return $choice; }
										return $choice?->getType()->value;
									},
									'choice_label' => function($choice) {
										return $choice?->getType()->value;
									},
									
									
									'label' => $variation->name,
									'expanded' => false,
									'multiple' => false,
									'required' => true,
									'placeholder' => 'placeholder',
									'translation_domain' => $reference->domain(),
									'attr' => [ 'data-select' => 'select2' ]
								])
							;
						}
					}
					
					
					/* ?????????????? ???????????????????????????? ???????? */
					if(!$variation->quantitative)
					{
						$form->remove('quantity');
					}
					
					/* ?????????????? ?????????????? ???????? ?????????????????? */
					if(!$variation->article)
					{
						$form->remove('article');
					}
					
					/* ?????????????? ???????????????????????????????? ?????????????????????? ???????? ?????????????????? */
					if(!$variation->image)
					{
						$form->remove('image');
					}
					
					/* ?????????????? ?????????? ???? ???????????????? ??????????????????????, ???????? ?????? ???????????? */
					if(!$variation->price)
					{
						$form->remove('price');
					}
				}
			}
		);
		
		$builder->add('DeleteVariation',
			ButtonType::class,
			[
				'label_html' => true,
				'attr' =>
					['class' => 'btn btn-sm btn-icon btn-light-danger del-item-variation'],
			]
		);
		
	}
	
	
	public function configureOptions(OptionsResolver $resolver) : void
	{
		$resolver->setDefaults([
			'data_class' => ProductOffersVariationCollectionDTO::class,
			'variation' => null,
			//'offers' => null,
		]);
	}
	
}
