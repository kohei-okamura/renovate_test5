<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\ConfirmLtcsBillingStatementStatusJob;
use Domain\Billing\LtcsBilling;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfirmLtcsBillingStatementStatusUseCaseMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Jobs\ConfirmLtcsBillingStatementStatusJob} のテスト.
 */
final class ConfirmLtcsBillingStatementStatusJobTest extends Test
{
    use ConfirmLtcsBillingStatementStatusUseCaseMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private LtcsBilling $billing;
    private ConfirmLtcsBillingStatementStatusJob $job;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->billing = $self->examples->ltcsBillings[2];
        });
        self::beforeEachSpec(function (self $self): void {
            $self->job = new ConfirmLtcsBillingStatementStatusJob($self->context, $self->billing);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call useCase', function (): void {
            $this->confirmLtcsBillingStatementStatusUseCase
                ->expects('handle')
                ->with($this->context, $this->billing)
                ->andReturnNull();

            $this->job->handle($this->confirmLtcsBillingStatementStatusUseCase);
        });
    }
}
