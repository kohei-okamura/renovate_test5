<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\LtcsInsCard\LtcsInsCard;
use Infrastructure\LtcsInsCard\LtcsInsCardAttr;
use Infrastructure\LtcsInsCard\LtcsInsCardMaxBenefitQuota;

/**
 * LtcsInsCard fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait LtcsInsCardFixture
{
    /**
     * 介護保険被保険者証 登録.
     *
     * @return void
     */
    protected function createLtcsInsCards(): void
    {
        foreach ($this->examples->ltcsInsCards as $entity) {
            $ltcsInsCard = LtcsInsCard::fromDomain($entity)->saveIfNotExists();
            $ltcsInsCard->attr()->save(LtcsInsCardAttr::fromDomain($entity));
            foreach ($entity->maxBenefitQuotas as $key => $domainMaxBenefitQuota) {
                $maxBenefitQuota = LtcsInsCardMaxBenefitQuota::fromDomain(
                    $domainMaxBenefitQuota,
                    ['sort_order' => $key]
                );
                $ltcsInsCard->attr->maxBenefitQuotas()->save($maxBenefitQuota);
            }
        }
    }
}
