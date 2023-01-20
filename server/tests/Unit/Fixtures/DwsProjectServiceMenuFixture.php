<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Project\DwsProjectServiceMenu;

/**
 * DwsProjectServiceMenu fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait DwsProjectServiceMenuFixture
{
    /**
     * 障害福祉サービス：計画：サービス内容 登録.
     *
     * @return void
     */
    protected function createDwsProjectServiceMenus(): void
    {
        foreach ($this->examples->dwsProjectServiceMenus as $entity) {
            DwsProjectServiceMenu::fromDomain($entity)->save();
        }
    }
}
