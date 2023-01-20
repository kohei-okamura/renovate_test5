<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Contract;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Contract\ContractStatus;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\ContractRepositoryMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupContractUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Contract\EditContractInteractor;

class EditContractInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupContractUseCaseMixin;
    use MockeryMixin;
    use ContractRepositoryMixin;
    use OrganizationRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private EditContractInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (EditContractInteractorTest $self): void {
            $self->lookupContractUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->contracts[0]))
                ->byDefault();
            $self->contractRepository
                ->allows('store')
                ->andReturn($self->examples->contracts[0])
                ->byDefault();
            $self->contractRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->organizationRepository
                ->allows('lookupOptionByCode')
                ->andReturn(Seq::from($self->examples->organizations[0]))
                ->byDefault();
            $self->logger
                ->allows('info')
                ->byDefault();
            $self->interactor = app(EditContractInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('edit the contract after transaction begun', function (): void {
            $this->lookupContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateLtcsContracts(),
                    $this->examples->contracts[0]->userId,
                    $this->examples->contracts[0]->id
                )
                ->andReturn(Seq::from($this->examples->contracts[0]));
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に編集処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に編集処理が行われないことの検証は（恐らく）できない
                    $this->contractRepository
                        ->expects('store')
                        ->andReturn($this->examples->contracts[0]);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                Permission::updateLtcsContracts(),
                $this->examples->contracts[0]->userId,
                $this->examples->contracts[0]->id,
                $this->getEditValue()
            );
        });
        $this->should('return the Contract', function (): void {
            $this->assertModelStrictEquals(
                $this->examples->contracts[0],
                $this->interactor->handle(
                    $this->context,
                    Permission::updateDwsContracts(),
                    $this->examples->contracts[0]->userId,
                    $this->examples->contracts[0]->id,
                    $this->getEditValue()
                )
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('契約が更新されました', ['id' => $this->examples->contracts[0]->id] + $context);

            $this->interactor->handle(
                $this->context,
                Permission::updateLtcsContracts(),
                $this->examples->contracts[0]->userId,
                $this->examples->contracts[0]->id,
                $this->getEditValue()
            );
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupContractUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateDwsContracts(), self::NOT_EXISTING_ID, self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        Permission::updateDwsContracts(),
                        self::NOT_EXISTING_ID,
                        self::NOT_EXISTING_ID,
                        $this->getEditValue()
                    );
                }
            );
        });
    }

    /**
     * 編集情報を取得する.
     *
     * @return array
     */
    public function getEditValue(): array
    {
        $contract = $this->examples->contracts[0];
        return [
            'officeId' => $contract->officeId,
            'status' => ContractStatus::terminated()->value(),
            'contractedOn' => '2020-01-01',
            'terminatedOn' => '2020-12-31',
            'dwsPeriods' => [
                DwsServiceDivisionCode::homeHelpService()->value() => [
                    'start' => '2020-01-05',
                    'end' => '2020-05-31',
                ],
                DwsServiceDivisionCode::visitingCareForPwsd()->value() => [
                    'start' => '2020-06-01',
                    'end' => '2020-12-25',
                ],
            ],
            'note' => 'だるまさんが転んだ',
        ];
    }
}
