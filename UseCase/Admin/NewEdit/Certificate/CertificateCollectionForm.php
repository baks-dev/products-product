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

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Certificate;

use BaksDev\Materials\Sign\Type\Status\MaterialSignStatus\Collection\MaterialSignStatusInterface;
use BaksDev\Products\Product\Type\Certificate\Document\Types\CertificateDocumentInterface;
use BaksDev\Products\Product\Type\Certificate\Match\Types\CertificateMatchInterface;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Certificate\Date\ProductCertificateDateForm;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Certificate\Match\ProductCertificateMatchForm;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Certificate\Number\ProductCertificateNumberForm;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Certificate\Title\ProductCertificateTitleForm;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Certificate\Type\ProductCertificateTypeForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CertificateCollectionForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'file',
                FileType::class,
                [
                    'label' => false,
                    'required' => false,
                    'attr' => ['accept' => ".pdf, .jpg, .jpeg, .png"],
                ],
            );

        $builder->add(
            'delete_cert',
            ButtonType::class,
            [
                'label_html' => true,
            ],
        );

        $builder->add('name', HiddenType::class);

        $builder->add('number', ProductCertificateNumberForm::class);

        $builder->add('title', ProductCertificateTitleForm::class);

        $builder->add('type', ProductCertificateTypeForm::class);

        $builder->add('match', ProductCertificateMatchForm::class);

        $builder->add('date', ProductCertificateDateForm::class);

    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CertificateCollectionDTO::class,
        ]);
    }

}
