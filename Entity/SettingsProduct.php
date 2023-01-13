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

namespace App\Module\Products\Product\Entity;


use App\Module\Products\Product\Type\Settings\ProductSettings;
use App\Module\User\Profile\TypeProfile\Type\Id\ProfileUid;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

/* Настройки сущности Product */

#[ORM\Entity()]
#[ORM\Table(name: 'product_settings')]
class SettingsProduct
{
    public const TABLE = 'product_settings';

    /** ID */
    #[ORM\Id]
    #[ORM\Column(type: ProductSettings::TYPE)]
    private ProductSettings $id;

    /** Очищать корзину старше n дней */
    #[ORM\Column(name: 'settings_truncate', type: Types::SMALLINT, length: 3, nullable: false)]
    private int $settingsTruncate = 365;
    
    
    /** Очищать события старше n дней */
    #[ORM\Column(name: 'settings_history', type: Types::SMALLINT, length: 3, nullable: false)]
    private int $settingsHistory = 365;
    
    /** Тип профиля, доступный к озданию карточек */
    #[ORM\Column(type: ProfileUid::TYPE, nullable: true)]
    private ?ProfileUid $profile = null;

    public function __construct() { $this->id = new ProductSettings(); }

//    /**
//    * @return ProductSettings
//    */
//    public function getId() : ProductSettings
//    {
//        return $this->id;
//    }
//
//    /**
//     * @return int
//     */
//    public function getSettingsTruncate() : int
//    {
//        return $this->settingsTruncate;
//    }
//
//    /**
//     * @param int $settingsTruncate
//     */
//    public function setSettingsTruncate(int $settingsTruncate) : void
//    {
//        $this->settingsTruncate = $settingsTruncate;
//    }
//
//    /**
//     * @return int
//     */
//    public function getSettingsHistory() : int
//    {
//        return $this->settingsHistory;
//    }
//
//    /**
//     * @param int $settingsHistory
//     */
//    public function setSettingsHistory(int $settingsHistory) : void
//    {
//        $this->settingsHistory = $settingsHistory;
//    }
}
