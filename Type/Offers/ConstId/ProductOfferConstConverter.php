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

namespace App\Module\Products\Product\Type\Offers\ConstId;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

final class ProductOfferConstConverter implements ParamConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ConfigurationInterface $configuration) : bool
    {
        $value = $request->attributes->get($configuration->getName()) ?: $request->attributes->get('id');
    
        if($value)
        {
            $request->attributes->set($configuration->getName(), new ProductOfferConst($value));
        }
    
        return $value !== null;
    }
    
    /**
     * {@inheritdoc}
     */
    public function supports(ConfigurationInterface $configuration) : bool
    {
        return ProductOfferConst::class === $configuration->getClass();
    }
}