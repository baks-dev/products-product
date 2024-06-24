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

    public function barcode(string $article): string
    {
        // Generate a random alphanumeric string
        $article = str_replace('-', '', $article);
        $article = strrev($article);

        $length = 10;
        $barcode = '460';

        for($i = 0; $i < $length; $i++)
        {
            $char = mb_substr($article, $i, 1);
            $index = ord($char) % 10;
            $barcode .= $index;
        }

        return $barcode;
    }

    public static function generate(): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $barcode = '';

        for($i = 0; $i < 13; $i++)
        {
            $barcode .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $barcode;
    }
}
