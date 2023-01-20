<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingInvoice;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementAggregate;
use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildDwsBillingStatementForUpdateUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingStatementRepositoryMixin;
use Tests\Unit\Mixins\EnsureDwsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\GetDwsBillingStatementInfoUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupDwsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\UpdateDwsBillingInvoiceUseCaseMixin;
use Tests\Unit\Test;
use UseCase\Billing\UpdateDwsBillingStatementInteractor;

/**
 * {@link \UseCase\Billing\UpdateDwsBillingStatementInteractor} Test.
 */
class UpdateDwsBillingStatementInteractorTest extends Test
{
    use BuildDwsBillingStatementForUpdateUseCaseMixin;
    use CarbonMixin;
    use ContextMixin;
    use DwsBillingStatementRepositoryMixin;
    use EnsureDwsBillingBundleUseCaseMixin;
    use ExamplesConsumer;
    use GetDwsBillingStatementInfoUseCaseMixin;
    use LoggerMixin;
    use LookupDwsBillingStatementUseCaseMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;
    use UpdateDwsBillingInvoiceUseCaseMixin;

    private DwsBilling $billing;
    private DwsBillingBundle $billingBundle;
    private DwsBillingInvoice $billingInvoice;
    private DwsBillingStatement $billingStatement;
    private array $infoArray;
    private array $inputArray;

    private UpdateDwsBillingStatementInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateDwsBillingStatementInteractorTest $self): void {
            $self->billing = $self->examples->dwsBillings[0];
            $self->billingBundle = $self->examples->dwsBillingBundles[1];
            $self->billingStatement = $self->examples->dwsBillingStatements[2];
            $self->billingInvoice = $self->examples->dwsBillingInvoices[0];

            $self->infoArray = ['response-able' => true];
            $self->inputArray = Seq::fromArray($self->billingStatement->aggregates)
                ->map(fn (DwsBillingStatementAggregate $x): array => [
                    'serviceDivisionCode' => $x->serviceDivisionCode,
                    'managedCopay' => $x->managedCopay + 100,
                    'subtotalSubsidy' => $x->subtotalSubsidy + 100,
                ])
                ->toArray();

            $self->ensureDwsBillingBundleUseCase
                ->allows('handle')
                ->byDefault();
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
            $self->buildDwsBillingStatementForUpdateUseCase
                ->allows('handle')
                ->andReturn($self->billingStatement)
                ->byDefault();
            $self->updateDwsBillingInvoiceUseCase
                ->allows('handle')
                ->andReturn($self->billingInvoice)
                ->byDefault();

            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(UpdateDwsBillingStatementInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return response-able array', function (): void {
            $expected = $this->infoArray;

            $actual = $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingStatement->id,
                    $this->inputArray
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
                    $this->inputArray
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
                            $this->inputArray
                        );
                }
            );
        });
        $this->should('use DwsBillingStatementRepository for updating entity', function (): void {
            $this->dwsBillingStatementRepository
                ->expects('store')
                ->andReturnUsing(function (DwsBillingStatement $actual): DwsBillingStatement {
                    $expected = $this->billingStatement->copy(['updatedAt' => Carbon::now()]);
                    $this->assertModelStrictEquals($expected, $actual);
                    return $actual;
                });
            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingStatement->id,
                    $this->inputArray
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
                    $this->inputArray
                );
        });
        $this->should('use UpdateDwsBillingInvoiceUseCase', function (): void {
            $this->updateDwsBillingInvoiceUseCase
                ->expects('handle')
                ->with($this->context, $this->billing->id, $this->billingBundle->id)
                ->andReturn($this->billingInvoice);
            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingStatement->id,
                    $this->inputArray
                );
        });
        $this->should('use BuildDwsBillingStatementForUpdateUseCase', function (): void {
            $aggregates = Seq::fromArray($this->billingStatement->aggregates)
                ->map(fn (DwsBillingStatementAggregate $x): DwsBillingStatementAggregate => $x->copy(
                    [
                        'managedCopay' => $x->managedCopay + 100,
                        'subtotalSubsidy' => $x->subtotalSubsidy + 100,
                    ]
                ))->toArray();
            $expects = $this->billingStatement->copy(['aggregates' => $aggregates]);

            $this->buildDwsBillingStatementForUpdateUseCase
                ->expects('handle')
                ->with($this->context, Mockery::capture($actual))
                ->andReturn($this->billingStatement);
            $this->interactor
                ->handle(
                    $this->context,
                    $this->billing->id,
                    $this->billingBundle->id,
                    $this->billingStatement->id,
                    $this->inputArray
                );

            $this->assertEquals($actual, $expects);
        });
    }
}
