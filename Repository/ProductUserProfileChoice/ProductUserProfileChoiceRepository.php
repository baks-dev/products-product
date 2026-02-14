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

namespace BaksDev\Products\Product\Repository\ProductUserProfileChoice;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Status\AccountStatus;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusActive;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\Profile\TypeProfile\Type\Id\Choice\TypeProfileIndividual;
use BaksDev\Users\Profile\TypeProfile\Type\Id\Choice\TypeProfileOrganization;
use BaksDev\Users\Profile\TypeProfile\Type\Id\Choice\TypeProfilePartner;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use Doctrine\DBAL\ArrayParameterType;
use Generator;

final class ProductUserProfileChoiceRepository implements ProductUserProfileChoiceInterface
{

    private bool $shop = false;

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    public function onlyShopProfile(): self
    {
        $this->shop = true;
        return $this;
    }

    /**
     * Возвращает список профилей пользователей, доступных к созданию карточек
     */
    public function getProfileCollection(): Generator
    {

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal->from(UserProfile::class, 'user_profile');

        $dbal->join(
            'user_profile',
            UserProfileInfo::class,
            'info',
            'info.profile = user_profile.id AND info.status = :status',
        )
            ->setParameter(
                'status',
                UserProfileStatusActive::class,
                UserProfileStatus::TYPE,
            );

        if($this->shop)
        {
            $dbal->join(
                'user_profile',
                UserProfileEvent::class,
                'users_profile_event',
                '
                    users_profile_event.id = user_profile.event
                    AND users_profile_event.type IN (:types)
                ')
                ->setParameter(
                    key: 'types',
                    value: [
                        TypeProfileOrganization::TYPE,
                        TypeProfileIndividual::TYPE,
                        TypeProfilePartner::TYPE,
                    ],
                    type: ArrayParameterType::STRING,
                );

        }


        $dbal->join(
            'user_profile',
            UserProfilePersonal::class,
            'personal',
            'personal.event = user_profile.event',
        );

        $dbal->join(
            'info',
            Account::class,
            'account',
            'account.id = info.usr',
        );

        $dbal->join(
            'account',
            AccountStatus::class,
            'status',
            'status.event = account.event AND status.status = :account_status',
        )
            ->setParameter(
                'account_status',
                new EmailStatus(EmailStatusActive::class),
                EmailStatus::TYPE,
            );


        /** Свойства конструктора объекта гидрации */

        $dbal->addSelect('user_profile.id AS value');
        $dbal->addSelect('personal.username AS attr');

        $result = $dbal->fetchAllHydrate(UserProfileUid::class);

        /** Сбрасываем свойства */
        $this->shop = false;

        return $result;

    }

}
