<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Project;

use Domain\Project\DwsProjectServiceMenu;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsProjectServiceMenuRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Project\LookupDwsProjectServiceMenuInteractor;

/**
 * {@link \UseCase\Project\LookupDwsProjectServiceMenuInteractor} のテスト.
 */
class LookupDwsProjectServiceMenuInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use DwsProjectServiceMenuRepositoryMixin;
    use UnitSupport;

    private DwsProjectServiceMenu $dwsProjectServiceMenu;
    private LookupDwsProjectServiceMenuInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupDwsProjectServiceMenuInteractorTest $self): void {
            $self->dwsProjectServiceMenu = $self->examples->dwsProjectServiceMenus[0];
            $self->dwsProjectServiceMenuRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->dwsProjectServiceMenu))
                ->byDefault();

            $self->interactor = app(LookupDwsProjectServiceMenuInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a Seq of DwsProjectServiceMenu', function (): void {
            $actual = $this->interactor->handle($this->context, $this->dwsProjectServiceMenu->id);

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertSame(1, $actual->size());
            $this->assertModelStrictEquals($this->dwsProjectServiceMenu, $actual->head());
        });
    }
}
