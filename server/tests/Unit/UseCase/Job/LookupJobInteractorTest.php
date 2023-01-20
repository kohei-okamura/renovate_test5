<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Job;

use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\JobRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Job\LookupJobInteractor;

/**
 * LookupJobInteractor のテスト.
 */
class LookupJobInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use JobRepositoryMixin;
    use UnitSupport;

    private LookupJobInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupJobInteractorTest $self): void {
            $self->context->allows('organization')->andReturn($self->examples->organizations[0]);
            $self->interactor = app(LookupJobInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a seq of job', function (): void {
            $this->jobRepository
                ->expects('lookup')
                ->with($this->examples->jobs[0]->id)
                ->andReturn(Seq::from($this->examples->jobs[0]));

            $actual = $this->interactor->handle($this->context, $this->examples->jobs[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($this->examples->jobs[0], $actual->head());
        });

        $this->should('return empty seq when different organizationId given', function (): void {
            $job = $this->examples->jobs[0]->copy(['organizationId' => self::NOT_EXISTING_ID]);
            $this->jobRepository
                ->allows('lookup')
                ->andReturn(Seq::from($job));

            $actual = $this->interactor->handle($this->context, $this->examples->jobs[0]->id);
            $this->assertCount(0, $actual);
        });
    }
}
