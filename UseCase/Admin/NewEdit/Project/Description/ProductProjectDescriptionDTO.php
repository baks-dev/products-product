<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Project\Description;

use BaksDev\Core\Type\Device\Device;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Products\Product\Entity\Project\Description\ProductProjectDescriptionInterface;
use ReflectionProperty;
use Symfony\Component\Validator\Constraints as Assert;

final class ProductProjectDescriptionDTO implements ProductProjectDescriptionInterface
{
    /** Локаль */
    #[Assert\NotBlank]
    private Locale $local;

    /** Device  */
    #[Assert\NotBlank]
    private Device $device;

    /** Краткое описание */
    #[Assert\Valid]
    private ?string $preview = null;

    /** Детальное описание */
    #[Assert\Valid]
    private ?string $description = null;


    public function __construct()
    {
        $this->local = new Locale(Locale::default());
    }

    /** Локаль */

    public function getLocal(): Locale
    {
        return $this->local;
    }


    public function setLocal(Locale $local): self
    {
        if(!(new ReflectionProperty(self::class, 'local'))->isInitialized($this))
        {
            $this->local = $local;
        }

        return $this;
    }


    /** Краткое описание */

    public function getPreview(): ?string
    {
        return $this->preview;
    }


    public function setPreview(?string $preview): self
    {
        $this->preview = $preview;
        return $this;
    }


    /** Детальное описание */

    public function getDescription(): ?string
    {
        return $this->description;
    }


    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Device
     */
    public function getDevice(): Device
    {
        return $this->device;
    }

    public function setDevice(Device $device): self
    {
        if(!(new ReflectionProperty(self::class, 'device'))->isInitialized($this))
        {
            $this->device = $device;
        }

        return $this;
    }
}