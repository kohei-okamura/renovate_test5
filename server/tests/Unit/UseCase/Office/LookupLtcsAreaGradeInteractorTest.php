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
use Tests\Unit\Mixins\LtcsAreaGradeRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Office\LookupLtcsAreaGradeInteractor;

/**
 * LookupLtcsAreaGradeInteractor のテスト.
 */
final class LookupLtcsAreaGradeInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use LtcsAreaGradeRepositoryMixin;
    use UnitSupport;

    private LookupLtcsAreaGradeInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupLtcsAreaGradeInteractorTest $self): void {
            $self->context->allows('organization')->andReturn($self->examples->organizations[0]);

            $self->interactor = app(LookupLtcsAreaGradeInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a seq of LtcsAreaGrade', function (): void {
            $this->ltcsAreaGradeRepository
                ->expects('lookup')
                ->with($this->examples->ltcsAreaGrades[0]->id)
                ->andReturn(Seq::from($this->examples->ltcsAreaGrades[0]));

            $actual = $this->interactor->handle($this->context, $this->examples->ltcsAreaGrades[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($this->examples->ltcsAreaGrades[0], $actual->head());
        });
    }
}
