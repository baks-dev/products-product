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

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Active;

use BaksDev\Products\Product\Entity\Active\ProductActiveInterface;
use DateTimeImmutable;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

final class ActiveDTO implements ProductActiveInterface
{
    /**
     * Идентификатор
     *
     * @var string
     */
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


    /**
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }


    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }


    /**
     * @return DateTimeImmutable
     */
    public function getActiveFrom(): DateTimeImmutable
    {
        return $this->activeFrom;
    }


    /**
     * @param DateTimeImmutable $activeFrom
     */
    public function setActiveFrom(DateTimeImmutable $activeFrom): void
    {
        $this->activeFrom = $activeFrom;
    }


    /**
     * @return DateTimeImmutable|null
     */
    public function getActiveTo(): ?DateTimeImmutable
    {

        return $this->activeTo;
    }


    /**
     * @param DateTimeImmutable|null $activeTo
     */
    public function setActiveTo(?DateTimeImmutable $activeTo): void
    {
        $this->activeTo = $activeTo;
    }


    /**
     * @return DateTimeImmutable
     */
    public function getActiveFromTime(): DateTimeImmutable
    {
        return $this->activeFrom;
    }


    /**
     * @return DateTimeImmutable
     */
    public function activeFromTime(): DateTimeImmutable
    {
        return $this->activeFromTime;
    }


    /**
     * @param DateTimeImmutable $activeFromTime
     */
    public function setActiveFromTime(DateTimeImmutable $activeFromTime): void
    {
        $this->activeFromTime = $activeFromTime;
    }


    /**
     * @return DateTimeImmutable|null
     */
    public function getActiveToTime(): ?DateTimeImmutable
    {
        return $this->activeTo;
    }


    /**
     * @return DateTimeImmutable|null
     */
    public function activeToTime(): ?DateTimeImmutable
    {
        return $this->activeToTime;
    }


    /**
     * @param DateTimeImmutable|null $activeToTime
     */
    public function setActiveToTime(?DateTimeImmutable $activeToTime): void
    {
        $this->activeToTime = $activeToTime;
    }

}
