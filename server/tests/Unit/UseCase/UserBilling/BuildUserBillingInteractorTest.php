<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\UserBilling;

use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingResult;
use Lib\Exceptions\LogicException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupBankAccountUseCaseMixin;
use Tests\Unit\Mixins\LookupOwnExpenseProgramUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\UserBilling\BuildUserBillingInteractor;

/**
 * {@link \UseCase\UserBilling\BuildUserBillingInteractor} のテスト.
 */
final class BuildUserBillingInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LookupBankAccountUseCaseMixin;
    use LookupOwnExpenseProgramUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private BuildUserBillingInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->lookupBankAccountUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->bankAccounts[20]))
                ->byDefault();
            $self->lookupOwnExpenseProgramUseCase
                ->allows('handle')
                ->andReturn(Seq::from(
                    $self->examples->ownExpensePrograms[0],
                    $self->examples->ownExpensePrograms[6],
                    $self->examples->ownExpensePrograms[7],
                    $self->examples->ownExpensePrograms[8],
                    $self->examples->ownExpensePrograms[9],
                    $self->examples->ownExpensePrograms[10]
                ))
                ->byDefault();
            $self->interactor = app(BuildUserBillingInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return UserBilling', function (): void {
            $actual = $this->interactor->handle(
                $this->context,
                $this->examples->users[0],
                $this->examples->offices[0],
                Carbon::now(),
                Option::from($this->examples->dwsBillingStatements[0]),
                Option::from($this->examples->ltcsBillingStatements[0]),
                Option::from($this->examples->dwsProvisionReports[0]),
                Option::from($this->examples->ltcsProvisionReports[0])
            );

            $this->assertInstanceOf(UserBilling::class, $actual);
        });
        $this->should('throw a LogicException when statement is null and does not have ownExpense', function (): void {
            $this->assertThrows(LogicException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->examples->users[0],
                    $this->examples->offices[0],
                    Carbon::now(),
                    Option::none(),
                    Option::none(),
                    Option::from($this->examples->dwsProvisionReports[0]),
                    Option::from($this->examples->ltcsProvisionReports[0])
                );
            });
        });
        $this->should('use LookupBankAccountUseCase', function (): void {
            $this->lookupBankAccountUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->users[16]->bankAccountId)
                ->andReturn(Seq::from($this->examples->bankAccounts[20]));

            $this->interactor->handle(
                $this->context,
                $this->examples->users[16],
                $this->examples->offices[24],
                Carbon::now(),
                Option::from($this->examples->dwsBillingStatements[18]),
                Option::from($this->examples->ltcsBillingStatements[9]),
                Option::from($this->examples->dwsProvisionReports[8]),
                Option::from($this->examples->ltcsProvisionReports[7])
            );
        });
        $this->should('use LookupOwnExpenseProgramUseCase', function (): void {
            $ids = array_unique([
                ...Seq::fromArray($this->examples->dwsProvisionReports[8]->results)
                    ->filter(fn (DwsProvisionReportItem $x): bool => $x->ownExpenseProgramId !== null)
                    ->map(fn (DwsProvisionReportItem $x): int => $x->ownExpenseProgramId),
                ...Seq::fromArray($this->examples->ltcsProvisionReports[7]->entries)
                    ->filter(fn (LtcsProvisionReportEntry $x): bool => $x->ownExpenseProgramId !== null)
                    ->map(fn (LtcsProvisionReportEntry $x): int => $x->ownExpenseProgramId),
            ]);
            $this->lookupOwnExpenseProgramUseCase
                ->expects('handle')
                ->with($this->context, Permission::createUserBillings(), ...$ids)
                ->andReturn(Seq::from(
                    $this->examples->ownExpensePrograms[0],
                    $this->examples->ownExpensePrograms[6],
                    $this->examples->ownExpensePrograms[7],
                    $this->examples->ownExpensePrograms[8],
                    $this->examples->ownExpensePrograms[9],
                    $this->examples->ownExpensePrograms[10]
                ));

            $this->interactor->handle(
                $this->context,
                $this->examples->users[16],
                $this->examples->offices[24],
                Carbon::now(),
                Option::from($this->examples->dwsBillingStatements[18]),
                Option::from($this->examples->ltcsBillingStatements[9]),
                Option::from($this->examples->dwsProvisionReports[8]),
                Option::from($this->examples->ltcsProvisionReports[7])
            );
        });
        $this->should('use ComputeDwsBillingHomeHelpServiceMedicalDeductionScoreUseCase', function (): void {
            $this->interactor->handle(
                $this->context,
                $this->examples->users[16],
                $this->examples->offices[24],
                Carbon::now(),
                Option::from($this->examples->dwsBillingStatements[18]),
                Option::from($this->examples->ltcsBillingStatements[9]),
                Option::from($this->examples->dwsProvisionReports[8]),
                Option::from($this->examples->ltcsProvisionReports[7])
            );
        });
        $this->should('1 dwsBillingStatement only', function (): void {
            $actual = $this->interactor->handle(
                $this->context,
                $this->examples->users[16],
                $this->examples->offices[24],
                Carbon::now(),
                Option::from($this->examples->dwsBillingStatements[18]),
                Option::none(),
                Option::from($this->examples->dwsProvisionReports[0]),
                Option::none()
            );

            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('2 LtcsBillingStatement only', function (): void {
            $actual = $this->interactor->handle(
                $this->context,
                $this->examples->users[16],
                $this->examples->offices[24],
                Carbon::now(),
                Option::none(),
                Option::from($this->examples->ltcsBillingStatements[9]),
                Option::none(),
                Option::from($this->examples->ltcsProvisionReports[0])
            );

            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('return correct UserBilling when it has own expense services', function (): void {
            $actual = $this->interactor->handle(
                $this->context,
                $this->examples->users[16],
                $this->examples->offices[24],
                Carbon::now(),
                Option::from($this->examples->dwsBillingStatements[18]),
                Option::from($this->examples->ltcsBillingStatements[9]),
                Option::from($this->examples->dwsProvisionReports[8]),
                Option::from($this->examples->ltcsProvisionReports[7])
            );
            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('return correct UserBilling when it has only own expense services', function (): void {
            $actual = $this->interactor->handle(
                $this->context,
                $this->examples->users[16],
                $this->examples->offices[24],
                Carbon::now(),
                Option::none(),
                Option::none(),
                Option::from($this->examples->dwsProvisionReports[9]),
                Option::from($this->examples->ltcsProvisionReports[7])
            );

            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should(
            'return correct UserBilling when dwsProvisionReports has only own expense services',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    $this->examples->users[16],
                    $this->examples->offices[24],
                    Carbon::now(),
                    Option::none(),
                    Option::none(),
                    Option::from($this->examples->dwsProvisionReports[10]),
                    Option::none()
                );

                $this->assertMatchesModelSnapshot($actual);
            }
        );
        $this->should(
            'return correct UserBilling when ltcsProvisionReports has only own expense services',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    $this->examples->users[16],
                    $this->examples->offices[24],
                    Carbon::now(),
                    Option::none(),
                    Option::none(),
                    Option::none(),
                    Option::from($this->examples->ltcsProvisionReports[8])
                );

                $this->assertMatchesModelSnapshot($actual);
            }
        );
        $this->should(
            'return correct UserBilling when it has own expense services amount rounding up',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    $this->examples->users[16],
                    $this->examples->offices[24],
                    Carbon::now(),
                    Option::none(),
                    Option::none(),
                    Option::from($this->examples->dwsProvisionReports[17]),
                    Option::from($this->examples->ltcsProvisionReports[9])
                );

                // snapshot だけで良い気もするが、念の為 1 ケースだけ確認しておく
                $this->assertSame(UserBillingResult::pending(), $actual->result);
                $this->assertMatchesModelSnapshot($actual);
            }
        );
        $this->should('return correct UserBilling when it has no billing amount', function (): void {
            $dwsReport = $this->examples->dwsProvisionReports[18];
            $ltcsReport = $this->examples->ltcsProvisionReports[10];
            $ownExpenseProgramIds = [
                $dwsReport->results[0]->ownExpenseProgramId,
                $ltcsReport->entries[0]->ownExpenseProgramId,
            ];
            $this->lookupOwnExpenseProgramUseCase
                ->allows('handle')
                ->with($this->context, Permission::createUserBillings(), ...$ownExpenseProgramIds)
                ->andReturn(Seq::from(
                    $this->examples->ownExpensePrograms[11],
                    $this->examples->ownExpensePrograms[12],
                ));
            $dwsStatement = $this->examples->dwsBillingStatements[23];
            $ltcsStatement = $this->examples->ltcsBillingStatements[10];

            $actual = $this->interactor->handle(
                $this->context,
                $this->examples->users[16],
                $this->examples->offices[24],
                Carbon::now(),
                Option::from($dwsStatement),
                Option::from($ltcsStatement),
                Option::from($dwsReport),
                Option::from($ltcsReport)
            );

            $this->assertMatchesModelSnapshot($actual);
            $this->assertSame(UserBillingResult::none(), $actual->result);
        });
    }
}
