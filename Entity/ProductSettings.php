<?php
/*
*  Copyright Baks.dev <admin@baks.dev>
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

namespace BaksDev\Products\Product\Entity;

use BaksDev\Products\Product\Type\Settings\ProductSettingsIdentifier;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/* Настройки сущности Product */

#[ORM\Entity]
#[ORM\Table(name: 'product_settings')]
class ProductSettings
{
    public const TABLE = 'product_settings';

    /** ID */
    #[ORM\Id]
    #[ORM\Column(type: ProductSettingsIdentifier::TYPE)]
    private ProductSettingsIdentifier $id;

    /** Очищать корзину старше n дней */
    #[ORM\Column(name: 'settings_truncate', type: Types::SMALLINT, length: 3, nullable: false)]
    private int $settingsTruncate = 365;

    /** Очищать события старше n дней */
    #[ORM\Column(name: 'settings_history', type: Types::SMALLINT, length: 3, nullable: false)]
    private int $settingsHistory = 365;

    /** Тип профиля, доступный к cозданию карточек */
    #[ORM\Column(type: TypeProfileUid::TYPE, nullable: true)]
    private ?TypeProfileUid $profile = null;


    public function __construct()
    {
        $this->id = new ProductSettingsIdentifier();
    }

}
