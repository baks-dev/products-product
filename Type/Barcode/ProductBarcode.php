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

namespace BaksDev\Products\Product\Type\Barcode;

use BaksDev\Core\Type\UidType\Uid;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

final class ProductBarcode
{
    public const TYPE = 'product_barcode';

    private string $value;

    public function __construct(string $value = null)
    {
        if($value)
        {
            $this->value = $value;
            return;
        }

        $this->value = $this->barcode(self::generate());
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public static function generate(Uid|Uuid|string|null $uuid = null): string
    {
        if(is_null($uuid))
        {
            usleep(12000);
            $uuid = new UuidV7();
        }

        if(is_string($uuid))
        {
            $uuid = new UuidV7($uuid);
        }

        if($uuid instanceof Uid)
        {
            $uuid = $uuid->getValue();
        }

        if($uuid instanceof Uuid)
        {
            $uuid = $uuid->toString();
        }

        $uid = new UuidV7($uuid);

        $low = explode('-', $uuid, 2);
        $currentLow = current($low);

        $mid = explode('-', end($low), 2);
        $currentMid = current($mid);

        $res = filter_var($currentLow.$currentMid, FILTER_SANITIZE_NUMBER_INT);
        $res = ltrim($res, '0');

        $rtrim = rtrim($uid->getDateTime()->getTimestamp().$uid->getDateTime()->format('u'), '0');

        $pre = '2'.substr($rtrim, 1).$res;

        $barcode = substr($pre, 0, 13);

        /** Делаем проверку уникальности штрихкода */

        $cache = new FilesystemAdapter('products-product');

        $count = 0;

        while(true)
        {
            $item = $cache->getItem('barcode-'.$barcode);

            /** Присваиваем штрихкод если НЕ найден */
            if($item->isHit() === false)
            {
                $item->set($uuid);
                $cache->save($item);
                break;
            }

            /** Присваиваем штрихкод если найден и совпадает в uid */
            if($item->get() === $uuid)
            {
                break;
            }

            /** Генерируем новый штрихкод на +1 */
            $barcode = substr_replace($barcode, '40'.$count, 0, 3);
            $count++;
        }

        return $barcode;
    }

    public function barcode(string $article): string
    {
        // Generate a random alphanumeric string
        $article = str_replace('-', '', $article);
        $article = strrev($article);

        $length = 10;
        $barcode = '460'; // 460 1089289353

        for($i = 0; $i < $length; $i++)
        {
            $char = mb_substr($article, $i, 1);
            $index = ord($char) % 10;
            $barcode .= $index;
        }

        return $barcode;
    }

//    public static function generate(): string
//    {
//        $uid = new UuidV7();
//        return self::uuid_barcode($uid);
//    }
}
