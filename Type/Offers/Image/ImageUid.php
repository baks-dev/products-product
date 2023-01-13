<?php

namespace App\Module\Products\Product\Type\Offers\Image;

use App\System\Type\UidType\Uid;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;

final class ImageUid extends Uid //
{
    public const TYPE = 'product_offer_image_id';
    
//    private Uuid $value;
//
//    public function __construct(AbstractUid|string|null $value = null)
//    {
//        if($value === null)
//        {
//            $value = Uuid::v7();
//        }
//
//        else if(is_string($value))
//        {
//            $value = new Uuid($value);
//        }
//
//        $this->value = $value;
//    }
//
//    public function __toString() : string
//    {
//        return $this->value;
//    }
//
//    public function getValue() : AbstractUid
//    {
//        return $this->value;
//    }
}