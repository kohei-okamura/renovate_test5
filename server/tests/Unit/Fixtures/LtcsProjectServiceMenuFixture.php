<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Project\LtcsProjectServiceMenu;

/**
 * LtcsProjectServiceMenu fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait LtcsProjectServiceMenuFixture
{
    /**
     * 介護保険サービス：計画：サービス内容 登録.
     *
     * @return void
     */
    protected function createLtcsProjectServiceMenus(): void
    {
        foreach ($this->examples->ltcsProjectServiceMenus as $entity) {
            LtcsProjectServiceMenu::fromDomain($entity)->save();
        }
    }
}
