<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\OwnExpenseProgram\OwnExpenseProgram;
use Infrastructure\OwnExpenseProgram\OwnExpenseProgramAttr;

/**
 * OwnExpenseProgram fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait OwnExpenseProgramFixture
{
    /**
     * 自費サービス情報 登録.
     *
     * @return void
     */
    protected function createOwnExpensePrograms(): void
    {
        foreach ($this->examples->ownExpensePrograms as $entity) {
            $ownExpenseProgram = OwnExpenseProgram::fromDomain($entity)->saveIfNotExists();
            $ownExpenseProgram->attr()->save(OwnExpenseProgramAttr::fromDomain($entity));
        }
    }
}
