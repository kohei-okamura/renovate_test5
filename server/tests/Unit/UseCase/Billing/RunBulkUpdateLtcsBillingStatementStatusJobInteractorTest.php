<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\LtcsBillingStatus;
use Domain\Job\Job as DomainJob;
use Domain\Permission\Permission;
use Mockery;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BulkUpdateLtcsBillingStatementStatusUseCaseMixin;
use Tests\Unit\Mixins\ConfirmLtcsBillingStatementStatusUseCaseMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupLtcsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunJobUseCaseMixin;
use Tests\Unit\Test;
use UseCase\Billing\RunBulkUpdateLtcsBillingStatementStatusJobInteractor;

/**
 * {@link \UseCase\Billing\RunBulkUpdateLtcsBillingStatementStatusJobInteractor} のテスト.
 */
final class RunBulkUpdateLtcsBillingStatementStatusJobInteractorTest extends Test
{
    use BulkUpdateLtcsBillingStatementStatusUseCaseMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LookupLtcsBillingUseCaseMixin;
    use ConfirmLtcsBillingStatementStatusUseCaseMixin;
    use MockeryMixin;
    use RunJobUseCaseMixin;
    use UnitSupport;

    private DomainJob $domainJob;
    private int $billingId;
    private int $bundleId;
    private array $ids;
    private LtcsBillingStatus $status;
    private RunBulkUpdateLtcsBillingStatementStatusJobInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->domainJob = $self->examples->jobs[0];
            $self->billingId = $self->examples->ltcsBillingStatements[0]->billingId;
            $self->bundleId = $self->examples->ltcsBillingStatements[0]->bundleId;
            $self->ids = [$self->examples->ltcsBillingStatements[0]->id];
            $self->status = LtcsBillingStatus::fixed();
            $self->runJobUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->bulkUpdateLtcsBillingStatementStatusUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->lookupLtcsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ltcsBillings[0]))
                ->byDefault();
            $self->confirmLtcsBillingStatementStatusUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();

            $self->interactor = app(RunBulkUpdateLtcsBillingStatementStatusJobInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call RunJobUseCase', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::any())
                ->andReturnNull();

            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->billingId,
                $this->bundleId,
                $this->ids,
                $this->status
            );
        });
        $this->should('use LookupLtcsBillingUseCase', function (): void {
            $this->lookupLtcsBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateBillings(), $this->billingId)
                ->andReturn(Seq::from($this->examples->ltcsBillings[0]));

            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->billingId,
                $this->bundleId,
                $this->ids,
                $this->status
            );
        });
        $this->should('use LookupLtcsBillingUseCase', function (): void {
            $this->confirmLtcsBillingStatementStatusUseCase
                ->expects('handle')
                ->with($this->context, equalTo($this->examples->ltcsBillings[0]))
                ->andReturnNull();

            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->billingId,
                $this->bundleId,
                $this->ids,
                $this->status
            );
        });
    }
}
