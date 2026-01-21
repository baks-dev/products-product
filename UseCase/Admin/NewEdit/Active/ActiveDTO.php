<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Active;

use BaksDev\Products\Product\Entity\Active\ProductActiveInterface;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

/** @see ProductActive */
final class ActiveDTO implements ProductActiveInterface
{
    /** Идентификатор */
    public string $id;

    /** Статус активности товара */
    private bool $active = true;

    /** Начало активности */
    #[Assert\NotBlank]
    private DateTimeImmutable $activeFrom;

    private DateTimeImmutable $activeFromTime;

    /** Окончание активности */
    private ?DateTimeImmutable $activeTo = null;

    private ?DateTimeImmutable $activeToTime = null;

    public function __construct()
    {
        $this->activeFrom = new DateTimeImmutable();
        $this->activeFromTime = new DateTimeImmutable();
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getActiveFrom(): DateTimeImmutable
    {
        return $this->activeFrom;
    }

    public function setActiveFrom(DateTimeImmutable $activeFrom): void
    {
        $this->activeFrom = $activeFrom;
    }

    public function getActiveTo(): ?DateTimeImmutable
    {

        return $this->activeTo;
    }

    public function setActiveTo(?DateTimeImmutable $activeTo): void
    {
        $this->activeTo = $activeTo;
    }

    public function getActiveFromTime(): DateTimeImmutable
    {
        return $this->activeFrom;
    }

    public function setActiveFromTime(DateTimeImmutable $activeFromTime): void
    {
        $this->activeFromTime = $activeFromTime;
    }

    public function activeFromTime(): DateTimeImmutable
    {
        return $this->activeFromTime;
    }

    public function getActiveToTime(): ?DateTimeImmutable
    {
        return $this->activeTo;
    }

    public function setActiveToTime(?DateTimeImmutable $activeToTime): void
    {
        $this->activeToTime = $activeToTime;
    }

    public function activeToTime(): ?DateTimeImmutable
    {
        return $this->activeToTime;
    }

}
