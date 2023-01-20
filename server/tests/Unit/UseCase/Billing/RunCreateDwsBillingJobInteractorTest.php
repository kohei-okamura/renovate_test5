<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Closure;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Job\Job as DomainJob;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunJobUseCaseMixin;
use Tests\Unit\Test;
use UseCase\Billing\RunCreateDwsBillingJobInteractor;

/**
 * {@link \UseCase\Billing\RunCreateDwsBillingJobInteractor} のテスト.
 */
final class RunCreateDwsBillingJobInteractorTest extends Test
{
    use ContextMixin;
    use CreateDwsBillingUseCaseMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use RunJobUseCaseMixin;
    use UnitSupport;

    private CarbonRange $fixedAt;
    private RunCreateDwsBillingJobInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->fixedAt = CarbonRange::create([
                'start' => Carbon::create(2020, 12, 1),
                'end' => Carbon::create(2020, 12, 31)->endOfDay(),
            ]);
        });
        self::beforeEachSpec(function (self $self): void {
            $self->createDwsBillingUseCase
                ->allows('handle')
                ->andReturn($self->examples->dwsBillings[1])
                ->byDefault();
            $self->runJobUseCase
                ->allows('handle')
                ->andReturnUsing(function ($context, $domainJob, Closure $f): void {
                    $f();
                })
                ->byDefault();

            $self->interactor = app(RunCreateDwsBillingJobInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use CreateDwsBillingUseCase', function (): void {
            $this->markTestSkipped();
            $this->createDwsBillingUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->offices[1]->id,
                    equalTo(Carbon::create(2021, 1)),
                    equalTo($this->fixedAt)
                )
                ->andReturn($this->examples->dwsBillings[0]);

            $this->interactor->handle(
                $this->context,
                DomainJob::create(),
                $this->examples->offices[1]->id,
                Carbon::create(2021, 1),
                $this->fixedAt
            );
        });
        $this->should('return array by Closure', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->andReturnUsing(function ($context, $domainJob, Closure $f): void {
                    $this->assertEquals(
                        ['id' => $this->examples->dwsBillings[1]->id],
                        $f()
                    );
                });
            $this->interactor->handle(
                $this->context,
                DomainJob::create(),
                $this->examples->offices[1]->id,
                Carbon::create(2021, 1),
                $this->fixedAt
            );
        });
    }
}
