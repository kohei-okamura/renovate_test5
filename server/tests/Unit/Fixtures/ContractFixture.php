<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Domain\Billing\DwsServiceDivisionCode;
use Infrastructure\Contract\Contract;
use Infrastructure\Contract\ContractAttr;
use Infrastructure\Contract\ContractAttrDwsPeriod;

/**
 * Contract fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait ContractFixture
{
    /**
     * 契約 登録.
     *
     * @return void
     */
    protected function createContracts(): void
    {
        foreach ($this->examples->contracts as $entity) {
            $contract = Contract::fromDomain($entity)->saveIfNotExists();
            $attr = $contract->attr()->save(ContractAttr::fromDomain($entity));
            assert($attr instanceof ContractAttr);
            foreach ($entity->dwsPeriods as $key => $value) {
                $code = DwsServiceDivisionCode::from((string)$key);
                ContractAttrDwsPeriod::fromDomain($value, $attr->id, $code)->save();
            }
        }
    }
}
