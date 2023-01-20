<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsAreaGradeRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Office\LookupDwsAreaGradeInteractor;

/**
 * LookupDwsAreaGradeInteractor のテスト.
 */
final class LookupDwsAreaGradeInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use DwsAreaGradeRepositoryMixin;
    use UnitSupport;

    private LookupDwsAreaGradeInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupDwsAreaGradeInteractorTest $self): void {
            $self->context->allows('organization')->andReturn($self->examples->organizations[0]);

            $self->interactor = app(LookupDwsAreaGradeInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a seq of DwsAreaGrade', function (): void {
            $this->dwsAreaGradeRepository
                ->expects('lookup')
                ->with($this->examples->dwsAreaGrades[0]->id)
                ->andReturn(Seq::from($this->examples->dwsAreaGrades[0]));

            $actual = $this->interactor->handle($this->context, $this->examples->dwsAreaGrades[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($this->examples->dwsAreaGrades[0], $actual->head());
        });
    }
}
