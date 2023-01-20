<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Closure;
use Domain\Billing\DwsBillingStatus;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use Domain\Permission\Permission;
use Mockery;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BulkUpdateDwsBillingStatementStatusUseCaseMixin;
use Tests\Unit\Mixins\ConfirmDwsBillingStatusUseCaseMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunJobUseCaseMixin;
use Tests\Unit\Test;
use UseCase\Billing\RunBulkUpdateDwsBillingStatementStatusJobInteractor;

/**
 * {@link \UseCase\Billing\RunBulkUpdateDwsBillingStatementStatusJobInteractor} のテスト.
 */
final class RunBulkUpdateDwsBillingStatementStatusJobInteractorTest extends Test
{
    use BulkUpdateDwsBillingStatementStatusUseCaseMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LookupDwsBillingUseCaseMixin;
    use ConfirmDwsBillingStatusUseCaseMixin;
    use MockeryMixin;
    use RunJobUseCaseMixin;
    use UnitSupport;

    private DomainJob $domainJob;
    private int $billingId;
    private array $ids;
    private DwsBillingStatus $status;
    private RunBulkUpdateDwsBillingStatementStatusJobInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->domainJob = $self->examples->jobs[0];
            $self->billingId = $self->examples->dwsBillingStatements[0]->dwsBillingId;
            $self->ids = [$self->examples->dwsBillingStatements[0]->id];
            $self->status = DwsBillingStatus::fixed();
            $self->runJobUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->bulkUpdateDwsBillingStatementStatusUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillings[0]))
                ->byDefault();
            $self->confirmDwsBillingStatusUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();

            $self->interactor = app(RunBulkUpdateDwsBillingStatementStatusJobInteractor::class);
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
                $this->ids,
                $this->status
            );
        });
        $this->should('call LookupDwsBillingUseCase', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::any())
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $this->lookupDwsBillingUseCase
                            ->expects('handle')
                            ->with($this->context, Permission::updateBillings(), $this->billingId)
                            ->andReturn(Seq::from($this->examples->dwsBillings[0]));
                        $f();
                    }
                );

            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->billingId,
                $this->ids,
                $this->status
            );
        });
        $this->should('call ConfirmDwsBillingStatusUseCase', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::any())
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $this->confirmDwsBillingStatusUseCase
                            ->expects('handle')
                            ->with($this->context, equalTo($this->examples->dwsBillings[0]))
                            ->andReturnNull();
                        $f();
                    }
                );

            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->billingId,
                $this->ids,
                $this->status
            );
        });
    }
}
