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

namespace App\Module\Products\Product\UseCase\Admin\NewEdit\Info;

use App\Module\Products\Product\Entity\Info\InfoInterface;
use App\Module\User\Profile\TypeProfile\Type\Id\ProfileUid;
use App\Module\User\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Component\Validator\Constraints as Assert;

final class InfoDTO implements InfoInterface
{
    /** Семантическая ссылка на товар (строка с тире и нижним подчеркиванием) */
    #[Assert\NotBlank]
    #[Assert\Regex(
      pattern: '/^[a-z0-9\_\-]+$/i'
    )]
    private string $url;
    
    /** Артикул товара */
    private ?string $article = null;
    
    #[Assert\Uuid]
    #[Assert\NotBlank]
    private ?UserProfileUid $profile = null;
    
    
    /**
     * @return string
     */
    public function getUrl() : string
    {
        return $this->url;
    }
    
    /**
     * @param string $url
     */
    public function setUrl(string $url) : void
    {
        $this->url = $url;
    }
    

    public function updateUrlUniq() : void
    {
        $this->url = uniqid($this->url.'_', false);
    }
    
    /* ARTICLE */
    
    /**
     * @return ?string
     */
    public function getArticle() : ?string
    {
        return $this->article;
    }

    public function setArticle(?string $article) : void
    {
        $this->article = $article;
    }

    public function getProfile() : ?UserProfileUid
    {
        return $this->profile;
    }
    
    /**
     * @param UserProfileUid $profile
     */
    public function setProfile(UserProfileUid $profile) : void
    {
        $this->profile = $profile;
    }

}

