<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingStatement;
use Domain\Common\Decimal;
use Domain\Office\Office;
use Domain\User\User;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildLtcsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\LtcsBillingStatementRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\CreateLtcsBillingStatementInteractor;

/**
 * {@link \UseCase\Billing\CreateLtcsBillingStatementInteractor} のテスト.
 */
final class CreateLtcsBillingStatementInteractorTest extends Test
{
    use BuildLtcsBillingStatementUseCaseMixin;
    use DummyContextMixin;
    use ExamplesConsumer;
    use LtcsBillingStatementRepositoryMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private LtcsBillingBundle $bundle;
    private User $user;
    private Office $office;
    private Seq $details;
    private Seq $reports;
    private Decimal $unitCost;
    private LtcsBillingStatement $statement;

    private CreateLtcsBillingStatementInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->bundle = $self->examples->ltcsBillingBundles[0];
            $self->user = $self->examples->users[0];
            $self->office = $self->examples->offices[0];
            $self->details = Seq::fromArray($self->bundle->details);
            $self->unitCost = Decimal::fromInt(11_2000);
            $self->statement = $self->examples->ltcsBillingStatements[0];
            $self->reports = Seq::fromArray($self->examples->ltcsProvisionReports);
        });
        self::beforeEachSpec(function (self $self): void {
            $self->ltcsBillingStatementRepository
                ->allows('store')
                ->andReturn($self->statement)
                ->byDefault();

            $self->buildLtcsBillingStatementUseCase
                ->allows('handle')
                ->andReturn($self->statement)
                ->byDefault();

            $self->interactor = app(CreateLtcsBillingStatementInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('run in transaction', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturn($this->statement);
            $this->ltcsBillingStatementRepository->expects('store')->never();

            $this->interactor->handle(
                $this->context,
                $this->bundle,
                $this->user,
                $this->office,
                $this->details,
                $this->unitCost,
                $this->reports,
            );
        });
        $this->should('use BuildLtcsBillingStatementUseCase', function (): void {
            $this->buildLtcsBillingStatementUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->bundle,
                    $this->user,
                    $this->office,
                    $this->details,
                    $this->unitCost,
                    $this->reports,
                )
                ->andReturn($this->statement);

            $this->interactor->handle(
                $this->context,
                $this->bundle,
                $this->user,
                $this->office,
                $this->details,
                $this->unitCost,
                $this->reports,
            );
        });
        $this->should('store a statement to LtcsBillingStatementRepository', function (): void {
            $this->ltcsBillingStatementRepository
                ->expects('store')
                ->with($this->statement)
                ->andReturn($this->statement);

            $this->interactor->handle(
                $this->context,
                $this->bundle,
                $this->user,
                $this->office,
                $this->details,
                $this->unitCost,
                $this->reports,
            );
        });
        $this->should('return a LtcsBillingStatement', function (): void {
            $actual = $this->interactor->handle(
                $this->context,
                $this->bundle,
                $this->user,
                $this->office,
                $this->details,
                $this->unitCost,
                $this->reports,
            );

            $this->assertModelStrictEquals($this->statement, $actual);
        });
    }
}
