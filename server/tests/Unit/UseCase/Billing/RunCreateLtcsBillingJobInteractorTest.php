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
use Tests\Unit\Mixins\CreateLtcsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunJobUseCaseMixin;
use Tests\Unit\Test;
use UseCase\Billing\RunCreateLtcsBillingJobInteractor;

/**
 * {@link \UseCase\Billing\RunCreateLtcsBillingJobInteractor} のテスト.
 */
final class RunCreateLtcsBillingJobInteractorTest extends Test
{
    use ContextMixin;
    use CreateLtcsBillingUseCaseMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use RunJobUseCaseMixin;
    use UnitSupport;

    private CarbonRange $fixedAt;
    private RunCreateLtcsBillingJobInteractor $interactor;

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
            $self->createLtcsBillingUseCase
                ->allows('handle')
                ->andReturn($self->examples->ltcsBillings[1])
                ->byDefault();
            $self->runJobUseCase
                ->allows('handle')
                ->andReturnUsing(function ($context, $domainJob, Closure $f): void {
                    $f();
                })
                ->byDefault();

            $self->interactor = app(RunCreateLtcsBillingJobInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use CreateLtcsBillingUseCase', function (): void {
            $this->createLtcsBillingUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->offices[1]->id,
                    equalTo(Carbon::create(2021, 1)),
                    equalTo($this->fixedAt)
                )
                ->andReturn($this->examples->ltcsBillings[0]);

            $this->interactor
                ->handle(
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
                        ['id' => $this->examples->ltcsBillings[1]->id],
                        $f()
                    );
                });
            $this->interactor
                ->handle(
                    $this->context,
                    DomainJob::create(),
                    $this->examples->offices[1]->id,
                    Carbon::create(2021, 1),
                    $this->fixedAt
                );
        });
    }
}
