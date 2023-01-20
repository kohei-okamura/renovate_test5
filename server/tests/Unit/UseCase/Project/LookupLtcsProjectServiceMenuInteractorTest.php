<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Project;

use Domain\Project\LtcsProjectServiceMenu;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LtcsProjectServiceMenuRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Project\LookupLtcsProjectServiceMenuInteractor;

/**
 * {@link \UseCase\Project\LookupLtcsProjectServiceMenuInteractor} のテスト.
 */
class LookupLtcsProjectServiceMenuInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use LtcsProjectServiceMenuRepositoryMixin;
    use UnitSupport;

    private LtcsProjectServiceMenu $ltcsProjectServiceMenu;
    private LookupLtcsProjectServiceMenuInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupLtcsProjectServiceMenuInteractorTest $self): void {
            $self->ltcsProjectServiceMenu = $self->examples->ltcsProjectServiceMenus[0];
            $self->ltcsProjectServiceMenuRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->ltcsProjectServiceMenu))
                ->byDefault();

            $self->interactor = app(LookupLtcsProjectServiceMenuInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a Seq of LtcsProjectServiceMenu', function (): void {
            $actual = $this->interactor->handle($this->context, $this->ltcsProjectServiceMenu->id);

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertSame(1, $actual->size());
            $this->assertModelStrictEquals($this->ltcsProjectServiceMenu, $actual->head());
        });
    }
}
