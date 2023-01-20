<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingInvoice;
use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementCopayCoordination;
use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Carbon;
use Domain\DwsCertification\DwsCertification;
use Domain\Office\Office;
use Domain\Permission\Permission;
use function equalTo;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildDwsBillingStatementForUpdateUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingStatementRepositoryMixin;
use Tests\Unit\Mixins\DwsCertificationRepositoryMixin;
use Tests\Unit\Mixins\GetDwsBillingStatementInfoUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupDwsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\UpdateDwsBillingInvoiceUseCaseMixin;
use Tests\Unit\Test;
use UseCase\Billing\UpdateDwsBillingStatementCopayCoordinationInteractor;

/**
 * {@link \UseCase\Billing\UpdateDwsBillingStatementCopayCoordinationInteractor} のテスト.
 */
final class UpdateDwsBillingStatementCopayCoordinationInteractorTest extends Test
{
    use BuildDwsBillingStatementForUpdateUseCaseMixin;
    use CarbonMixin;
    use ContextMixin;
    use DwsBillingStatementRepositoryMixin;
    use DwsCertificationRepositoryMixin;
    use ExamplesConsumer;
    use GetDwsBillingStatementInfoUseCaseMixin;
    use LoggerMixin;
    use LookupDwsBillingStatementUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;
    use UpdateDwsBillingInvoiceUseCaseMixin;

    private DwsBilling $billing;
    private DwsBillingBundle $billingBundle;
    private DwsBillingStatement $billingStatement;
    private DwsCertification $certification;
    private DwsBillingInvoice $invoice;
    private Office $office;
    private array $infoArray;
    private Option $inputOption;

    private UpdateDwsBillingStatementCopayCoordinationInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->billing = $self->examples->dwsBillings[0];
            $self->billingBundle = $self->examples->dwsBillingBundles[1];
            $self->billingStatement = $self->examples->dwsBillingStatements[2];
            $self->certification = $self->examples->dwsCertifications[0];
            $self->office = $self->examples->offices[0];
            $self->invoice = $self->examples->dwsBillingInvoices[0];

            $self->infoArray = ['response-able' => true];
            $self->inputOption = Option::some([
                'result' => CopayCoordinationResult::appropriated(),
                'amount' => 0,
            ]);

            $self->lookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->billingStatement))
                ->byDefault();
            $self->dwsBillingStatementRepository
                ->allows('store')
                ->andReturn($self->billingStatement)
                ->byDefault();
            $self->dwsBillingStatementRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->getDwsBillingStatementInfoUseCase
                ->allows('handle')
                ->andReturn($self->infoArray)
                ->byDefault();
            $self->dwsCertificationRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->certification))
                ->byDefault();
            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->office))
                ->byDefault();
            $self->buildDwsBillingStatementForUpdateUseCase
                ->allows('handle')
                ->andReturn($self->billingStatement)
                ->byDefault();
            $self->updateDwsBillingInvoiceUseCase
                ->allows('handle')
                ->andReturn($self->invoice)
                ->byDefault();

            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(UpdateDwsBillingStatementCopayCoordinationInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return response-able array if the values exists', function (): void {
            $expected = $this->infoArray;

            $actual = $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingStatement->id,
                    $this->inputOption
                );

            $this->assertEquals($expected, $actual);
        });
        $this->should('return response-able array if the values is none', function (): void {
            $expected = $this->infoArray;

            $actual = $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingStatement->id,
                    Option::none()
                );

            $this->assertEquals($expected, $actual);
        });
        $this->should('use LookupDwsBillingStatementUseCase', function (): void {
            $this->lookupDwsBillingStatementUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateBillings(),
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingStatement->id
                )
                ->andReturn(Seq::from($this->billingStatement));
            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingStatement->id,
                    $this->inputOption
                );
        });
        $this->should('throw NotFoundException when LookupUseCase return none', function (): void {
            $this->lookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Option::none())
                ->byDefault();

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor
                        ->handle(
                            $this->context,
                            $this->billing->id,
                            $this->billingBundle->id,
                            $this->billingStatement->id,
                            $this->inputOption
                        );
                }
            );
        });
        $this->should('use DwsBillingStatementRepository for updating entity', function (): void {
            $this->dwsBillingStatementRepository
                ->expects('store')
                ->andReturnUsing(function (DwsBillingStatement $actual): DwsBillingStatement {
                    $this->assertModelStrictEquals($this->billingStatement, $actual);
                    return $actual;
                });
            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingStatement->id,
                    $this->inputOption
                );
        });
        $this->should('build CopayCoordination parameter', function (): void {
            $statement = $this->examples->dwsBillingStatements[7];
            $this->lookupDwsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::from($statement)); // null の Example

            $expected = $statement->copy([
                'copayCoordination' => DwsBillingStatementCopayCoordination::create([
                    'office' => DwsBillingOffice::from($this->office),
                ] + $this->inputOption->get()),
                'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::fulfilled(),
                'status' => DwsBillingStatus::ready(),
                'updatedAt' => Carbon::now(),
            ]);

            $this->buildDwsBillingStatementForUpdateUseCase
                ->expects('handle')
                ->with($this->context, equalTo($expected))
                ->andReturn($this->billingStatement);

            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingStatement->id,
                    $this->inputOption
                );
        });
        $this->should('use DwsCertificationRepository', function (): void {
            $statement = $this->examples->dwsBillingStatements[7];
            $this->lookupDwsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::from($statement));
            $this->dwsCertificationRepository
                ->expects('lookup')
                ->with($statement->user->dwsCertificationId)
                ->andReturn(Seq::from($this->certification));

            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingStatement->id,
                    $this->inputOption
                );
        });
        $this->should('use OfficeRepository', function (): void {
            $statement = $this->examples->dwsBillingStatements[7];
            $this->lookupDwsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::from($statement));
            $this->officeRepository
                ->expects('lookup')
                ->with($this->certification->copayCoordination->officeId)
                ->andReturn(Seq::from($this->office));

            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingStatement->id,
                    $this->inputOption
                );
        });
        $this->should('use UpdateDwsBillingInvoiceUseCase', function (): void {
            $this->updateDwsBillingInvoiceUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->billing->id,
                    $this->billingBundle->id,
                )
                ->andReturn($this->invoice);
            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingStatement->id,
                    $this->inputOption
                );
        });
        $this->should('throw NotFoundException when UpdateDwsBillingInvoiceUseCase throw NotFoundException', function (): void {
            $this->updateDwsBillingInvoiceUseCase
                ->expects('handle')
                ->andThrow(NotFoundException::class);

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor
                        ->handle(
                            $this->context,
                            $this->billing->id,
                            $this->billingBundle->id,
                            $this->billingStatement->id,
                            $this->inputOption
                        );
                }
            );
        });
        $this->should('run in transaction', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturn($this->billingStatement);
            $this->dwsBillingStatementRepository->expects('store')->never();
            $this->updateDwsBillingInvoiceUseCase->expects('handle')->never();
            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingStatement->id,
                    $this->inputOption
                );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with(
                    '障害福祉サービス：明細書が更新されました',
                    ['id' => $this->billingStatement->id] + $context
                )
                ->andReturnNull();
            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingStatement->id,
                    $this->inputOption
                );
        });
    }
}
