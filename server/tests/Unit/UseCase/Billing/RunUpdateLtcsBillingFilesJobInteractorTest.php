<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Closure;
use Domain\Job\Job as DomainJob;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunJobUseCaseMixin;
use Tests\Unit\Mixins\UpdateLtcsBillingFilesUseCaseMixin;
use Tests\Unit\Test;
use UseCase\Billing\RunUpdateLtcsBillingFilesJobInteractor;

/**
 * {@link \UseCase\Billing\RunUpdateLtcsBillingFilesJobInteractor} のテスト.
 */
final class RunUpdateLtcsBillingFilesJobInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use RunJobUseCaseMixin;
    use UnitSupport;
    use UpdateLtcsBillingFilesUseCaseMixin;

    private RunUpdateLtcsBillingFilesJobInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->runJobUseCase
                ->allows('handle')
                ->andReturnUsing(function ($context, $job, $f): void {
                    $f();
                })
                ->byDefault();
            $self->updateLtcsBillingFilesUseCase
                ->allows('handle')
                ->andReturn($self->examples->ltcsBillings[1])
                ->byDefault();

            $self->interactor = app(RunUpdateLtcsBillingFilesJobInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use UpdateLtcsBillingFilesUseCase', function (): void {
            $this->updateLtcsBillingFilesUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->ltcsBillings[1]->id
                )
                ->andReturn($this->examples->ltcsBillings[1]);

            $this->interactor
                ->handle(
                    $this->context,
                    DomainJob::create(),
                    $this->examples->ltcsBillings[1]->id
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
                    $this->examples->ltcsBillings[1]->id,
                );
        });
    }
}
