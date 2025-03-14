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

declare(strict_types=1);

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Materials;


use BaksDev\Products\Product\Repository\MaterialsChoice\MaterialsChoiceInterface;
use BaksDev\Products\Product\Type\Material\MaterialUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductMaterialForm extends AbstractType
{
    public function __construct(private readonly MaterialsChoiceInterface $materials) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $material = $this->materials->findAll();

        if(false === $material || false === $material->valid())
        {
            $material = [];
        }

        $builder
            ->add('material', ChoiceType::class, [
                'choices' => $material,
                'choice_value' => function(?MaterialUid $material) {
                    return $material instanceof MaterialUid ? $material?->getValue() : null;
                },
                'choice_label' => function(MaterialUid $material) {
                    return $material->getAttr();
                },

                'choice_attr' => function(?MaterialUid $material) {
                    return $material ? [
                        'data-filter' => ' ['.$material->getOption().']',
                        'data-max' => $material->getOption(),
                        'data-name' => $material->getAttr(),
                    ] : [];
                },

                'label' => false,
                'expanded' => false,
                'multiple' => false,
                'required' => false,
            ]);

        $builder->add(
            'DeleteMaterial', ButtonType::class,
            [
                'label_html' => true,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductMaterialDTO::class,
            'method' => 'POST',
            'attr' => ['class' => 'w-100'],
        ]);
    }
}