<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBillingStatement;
use Domain\Permission\Permission;
use Domain\User\User;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildDwsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\DwsBillingStatementRepositoryMixin;
use Tests\Unit\Mixins\IdentifyHomeHelpServiceCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\IdentifyVisitingCareForPwsdCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\CreateDwsBillingStatementListInteractor;

/**
 * {@link \UseCase\Billing\CreateDwsBillingStatementListInteractor} のテスト.
 */
final class CreateDwsBillingStatementListInteractorTest extends Test
{
    use BuildDwsBillingStatementUseCaseMixin;
    use CarbonMixin;
    use DummyContextMixin;
    use DwsBillingStatementRepositoryMixin;
    use DwsBillingTestSupport;
    use IdentifyHomeHelpServiceCalcSpecUseCaseMixin;
    use IdentifyVisitingCareForPwsdCalcSpecUseCaseMixin;
    use LookupUserUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private CreateDwsBillingStatementListInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
        });
        self::beforeEachSpec(function (self $self): void {
            $self->identifyHomeHelpServiceCalcSpecUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->homeHelpServiceCalcSpec))
                ->byDefault();

            $self->identifyVisitingCareForPwsdCalcSpecUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->visitingCareForPwsdCalcSpec))
                ->byDefault();

            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(
                    Seq::from($self->users[1]),
                    Seq::from($self->users[2]),
                )
                ->byDefault();

            $self->buildDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(...$self->statements)
                ->byDefault();

            $self->dwsBillingStatementRepository
                ->allows('store')
                ->andReturnUsing(fn (DwsBillingStatement $x): DwsBillingStatement => $x->copy([
                    'id' => $x->user->userId,
                ]))
                ->byDefault();

            $self->interactor = app(CreateDwsBillingStatementListInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle()
    {
        $this->should('run in transaction', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturn(Seq::emptySeq());
            $this->identifyHomeHelpServiceCalcSpecUseCase->expects('handle')->never();
            $this->identifyVisitingCareForPwsdCalcSpecUseCase->expects('handle')->never();
            $this->lookupUserUseCase->expects('handle')->never();
            $this->dwsBillingStatementRepository->expects('store')->never();

            $this->interactor->handle($this->context, $this->office, $this->bundle);
        });
        $this->should('identify HomeHelpServiceCalcSpec', function (): void {
            $this->identifyHomeHelpServiceCalcSpecUseCase
                ->expects('handle')
                ->with($this->context, $this->office, $this->bundle->providedIn)
                ->andReturn(Option::some($this->homeHelpServiceCalcSpec));

            $this->interactor->handle($this->context, $this->office, $this->bundle);
        });
        $this->should('identify VisitingCareForPwsdCalcSpec', function (): void {
            $this->identifyVisitingCareForPwsdCalcSpecUseCase
                ->expects('handle')
                ->with($this->context, $this->office, $this->bundle->providedIn)
                ->andReturn(Option::some($this->visitingCareForPwsdCalcSpec));

            $this->interactor->handle($this->context, $this->office, $this->bundle);
        });
        $this->should(
            'lookup User for each userId',
            function (User $user): void {
                $permission = Permission::createBillings();
                $this->lookupUserUseCase
                    ->expects('handle')
                    ->with($this->context, $permission, $user->id)
                    ->andReturn(Seq::from($user));

                $this->interactor->handle($this->context, $this->office, $this->bundle);
            },
            [
                'examples' => [
                    [$this->users[1]],
                    [$this->users[2]],
                ],
            ]
        );
        $this->should(
            'throw NotFoundException when User not found',
            function (int $times, ...$returnValues): void {
                $this->lookupUserUseCase->expects('handle')->andReturn(...$returnValues)->times($times);

                $this->assertThrows(NotFoundException::class, function (): void {
                    $this->interactor->handle($this->context, $this->office, $this->bundle);
                });
            },
            [
                'examples' => [
                    [2, Seq::from($this->users[1]), Seq::emptySeq()],
                    [1, Seq::emptySeq(), Seq::from($this->users[2])],
                ],
            ]
        );
        $this->should('store each DwsBillingStatements to repository', function (): void {
            $this->dwsBillingStatementRepository
                ->expects('store')
                ->with($this->statements[0])
                ->andReturnUsing(fn (DwsBillingStatement $x): DwsBillingStatement => $x->copy([
                    'id' => $x->user->userId,
                ]));
            $this->dwsBillingStatementRepository
                ->expects('store')
                ->with($this->statements[1])
                ->andReturnUsing(fn (DwsBillingStatement $x): DwsBillingStatement => $x->copy([
                    'id' => $x->user->userId,
                ]));

            $this->interactor->handle($this->context, $this->office, $this->bundle);
        });
        $this->should('return a Seq of DwsBillingStatement', function (): void {
            $actual = $this->interactor->handle($this->context, $this->office, $this->bundle);

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertForAll($actual, fn ($x): bool => $x instanceof DwsBillingStatement);
            $this->assertMatchesModelSnapshot($actual);
        });
    }
}
