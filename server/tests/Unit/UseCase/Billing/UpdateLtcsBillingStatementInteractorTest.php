<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingStatement;
use Domain\Permission\Permission;
use Domain\User\User;
use Domain\User\UserLtcsSubsidy;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GetLtcsBillingStatementInfoUseCaseMixin;
use Tests\Unit\Mixins\IdentifyUserLtcsSubsidyUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupLtcsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\LookupLtcsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\LookupLtcsBillingUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\LtcsBillingStatementRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\UpdateLtcsBillingInvoiceListUseCaseMixin;
use Tests\Unit\Test;
use UseCase\Billing\UpdateLtcsBillingStatementInteractor;

/**
 * {@link \UseCase\Billing\UpdateLtcsBillingStatementInteractor} のテスト.
 */
final class UpdateLtcsBillingStatementInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use GetLtcsBillingStatementInfoUseCaseMixin;
    use IdentifyUserLtcsSubsidyUseCaseMixin;
    use LoggerMixin;
    use LookupLtcsBillingBundleUseCaseMixin;
    use LookupLtcsBillingStatementUseCaseMixin;
    use LookupLtcsBillingUseCaseMixin;
    use LookupUserUseCaseMixin;
    use LtcsBillingStatementRepositoryMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;
    use UpdateLtcsBillingInvoiceListUseCaseMixin;

    /** @var \Domain\Billing\LtcsBilling[]&\ScalikePHP\Seq */
    private Seq $billings;
    private LtcsBilling $billing;

    /** @var \Domain\Billing\LtcsBillingBundle[]&\ScalikePHP\Seq */
    private Seq $bundles;
    private LtcsBillingBundle $bundle;

    /** @var \Domain\Billing\LtcsBillingInvoice[]&\ScalikePHP\Seq */
    private Seq $invoices;

    /** @var \Domain\Billing\LtcsBillingStatement[]&\ScalikePHP\Seq */
    private Seq $statements;
    private LtcsBillingStatement $statement;

    /** @var \Domain\User\User[]&\ScalikePHP\Seq */
    private Seq $users;
    private User $user;

    /** @var \Domain\User\UserLtcsSubsidy[]&\ScalikePHP\Seq */
    private Seq $subsidies;

    private UpdateLtcsBillingStatementInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->billing = $self->examples->ltcsBillings[0];
            $self->billings = Seq::from($self->billing);

            $self->bundle = $self->examples->ltcsBillingBundles[0];
            $self->bundles = Seq::from($self->bundle);

            $self->invoices = Seq::fromArray($self->examples->ltcsBillingInvoices)->take(2)->computed();

            $self->statement = $self->examples->ltcsBillingStatements[0];
            $self->statements = Seq::from($self->statement);

            $userId = $self->statements[0]->user->userId;
            $self->user = Seq::fromArray($self->examples->users)->find(fn (User $x): bool => $x->id === $userId)->get();
            $self->users = Seq::from($self->user);
            $self->subsidies = Seq::fromArray($self->examples->userLtcsSubsidies)
                ->filter(fn (UserLtcsSubsidy $x): bool => $x->userId === $userId)
                ->map(fn (UserLtcsSubsidy $x): Option => Option::some($x))
                ->append([Option::none(), Option::none(), Option::none()])
                ->take(3)
                ->computed();
        });
        self::beforeEachSpec(function (self $self): void {
            $self->lookupLtcsBillingUseCase->allows('handle')->andReturn($self->billings)->byDefault();
            $self->lookupLtcsBillingBundleUseCase->allows('handle')->andReturn($self->bundles)->byDefault();
            $self->lookupLtcsBillingStatementUseCase->allows('handle')->andReturn($self->statements)->byDefault();
            $self->lookupUserUseCase->allows('handle')->andReturn($self->users)->byDefault();
            $self->identifyUserLtcsSubsidyUseCase->allows('handle')->andReturn($self->subsidies)->byDefault();

            $id = 1000;
            $self->ltcsBillingStatementRepository
                ->allows('store')
                ->andReturnUsing(function (LtcsBillingStatement $x) use (&$id): LtcsBillingStatement {
                    return $x->id ? $x : $x->copy(['id' => ++$id]);
                })
                ->byDefault();

            $self->updateLtcsBillingInvoiceListUseCase
                ->allows('handle')
                ->andReturn($self->invoices)
                ->byDefault();

            $self->getLtcsBillingStatementInfoUseCase
                ->allows('handle')
                ->andReturn([
                    'billing' => $self->billing,
                    'bundle' => $self->bundle,
                    'statement' => $self->statement,
                ])
                ->byDefault();

            $self->logger->allows('info')->byDefault();

            $self->interactor = app(UpdateLtcsBillingStatementInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('run in transaction', function (): void {
            $this->transactionManager->expects('run')->andReturn([]);
            $this->lookupLtcsBillingUseCase->expects('handle')->never();
            $this->lookupLtcsBillingBundleUseCase->expects('handle')->never();
            $this->lookupLtcsBillingStatementUseCase->expects('handle')->never();
            $this->lookupUserUseCase->expects('handle')->never();
            $this->identifyUserLtcsSubsidyUseCase->expects('handle')->never();
            $this->ltcsBillingStatementRepository->expects('store')->never();
            $this->updateLtcsBillingInvoiceListUseCase->expects('handle')->never();
            $this->getLtcsBillingStatementInfoUseCase->expects('handle')->never();

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->bundle->id,
                $this->statement->id,
                []
            );
        });
        $this->should('lookup the billing', function (): void {
            $this->lookupLtcsBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateBillings(), $this->billing->id)
                ->andReturn($this->billings);

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->bundle->id,
                $this->statement->id,
                []
            );
        });
        $this->should('lookup the bundle', function (): void {
            $this->lookupLtcsBillingBundleUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateBillings(), $this->billing, $this->bundle->id)
                ->andReturn($this->bundles);

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->bundle->id,
                $this->statement->id,
                []
            );
        });
        $this->should('lookup the statement', function (): void {
            $this->lookupLtcsBillingStatementUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateBillings(),
                    $this->billing,
                    $this->bundle,
                    $this->statement->id
                )
                ->andReturn($this->statements);

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->bundle->id,
                $this->statement->id,
                []
            );
        });
        $this->should('lookup the user', function (): void {
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateBillings(), $this->user->id)
                ->andReturn($this->users);

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->bundle->id,
                $this->statement->id,
                []
            );
        });
        $this->should('identify subsidies of the user', function (): void {
            $this->identifyUserLtcsSubsidyUseCase
                ->expects('handle')
                ->with($this->context, $this->user, $this->bundle->providedIn)
                ->andReturn($this->subsidies);

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->bundle->id,
                $this->statement->id,
                []
            );
        });
        $this->should('store the statement', function (): void {
            $aggregate = $this->statement->aggregates[0];
            $plannedScore = $aggregate->plannedScore - 1;
            $this->ltcsBillingStatementRepository
                ->expects('store')
                ->andReturnUsing(function (LtcsBillingStatement $x) use (&$id): LtcsBillingStatement {
                    return $x->id ? $x : $x->copy(['id' => ++$id]);
                });

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->bundle->id,
                $this->statement->id,
                [
                    [
                        'serviceDivisionCode' => $aggregate->serviceDivisionCode,
                        'plannedScore' => $plannedScore,
                    ],
                ]
            );
        });
        $this->should('update the invoices', function (): void {
            $this->updateLtcsBillingInvoiceListUseCase
                ->expects('handle')
                ->andReturn($this->invoices);

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->bundle->id,
                $this->statement->id,
                []
            );
        });
        $this->should('get an information array of the statement for response', function (): void {
            $this->getLtcsBillingStatementInfoUseCase
                ->expects('handle')
                ->andReturn([
                    'billing' => $this->billing,
                    'bundle' => $this->bundle,
                    'statement' => $this->statement,
                ]);

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->bundle->id,
                $this->statement->id,
                []
            );
        });
        $this->should('log info that the statement has updated', function (): void {
            $expected = ['id' => $this->statement->id] + $this->context->logContext();
            $this->logger->expects('info')->with(
                '介護保険サービス：明細書が更新されました',
                Mockery::capture($actual)
            );

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->bundle->id,
                $this->statement->id,
                []
            );

            $this->assertSame($expected, $actual);
        });
        $this->should('return an array of billing, bundle and statement', function (): void {
            $expected = [
                'billing' => $this->billing,
                'bundle' => $this->bundle,
                'statement' => $this->statement,
            ];

            $actual = $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->bundle->id,
                $this->statement->id,
                []
            );

            $this->assertArrayStrictEquals($expected, $actual);
        });
    }
}
