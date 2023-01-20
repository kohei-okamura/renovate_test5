<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 *  UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\BankAccount;

use Closure;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BankAccountRepositoryMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupBankAccountUseCaseMixin;
use Tests\Unit\Mixins\LookupStaffUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\BankAccount\EditStaffBankAccountInteractor;

/**
 * EditStaffBankAccountInteractorのテスト。
 */
class EditStaffBankAccountInteractorTest extends Test
{
    use BankAccountRepositoryMixin;
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupBankAccountUseCaseMixin;
    use LookupStaffUseCaseMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private EditStaffBankAccountInteractor $interactor;

    /**
     * セットアップ処理。
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (EditStaffBankAccountInteractorTest $self): void {
            $self->lookupStaffUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->staffs[0]))
                ->byDefault();
            $self->lookupBankAccountUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->bankAccounts[0]))
                ->byDefault();
            $self->bankAccountRepository
                ->allows('store')
                ->andReturn($self->examples->bankAccounts[0])
                ->byDefault();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(EditStaffBankAccountInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('edit the StaffBankAccount after transaction begun', function (): void {
            $this->lookupStaffUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateStaffs(), $this->examples->staffs[0]->id)
                ->andReturn(Seq::from($this->examples->staffs[0]));
            $this->lookupBankAccountUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->staffs[0]->bankAccountId)
                ->andReturn(Seq::from($this->examples->bankAccounts[0]));
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に編集処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に編集処理が行われないことの検証は（恐らく）できない
                    $this->bankAccountRepository
                        ->expects('store')
                        ->andReturn($this->examples->bankAccounts[0]);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->examples->staffs[0]->id, $this->getEditValue());
        });
        $this->should('return the staffBankAccount', function (): void {
            $this->assertModelStrictEquals(
                $this->examples->bankAccounts[0],
                $this->interactor->handle($this->context, $this->examples->staffs[0]->id, $this->getEditValue())
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('スタッフの銀行口座が更新されました', ['id' => $this->examples->bankAccounts[0]->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->examples->staffs[0]->id,
                $this->getEditValue()
            );
        });
        $this->should('throw a NotFoundException when the staff not exists', function (): void {
            $this->lookupStaffUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateStaffs(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());
            $this->lookupBankAccountUseCase
                ->expects('handle')
                ->never();

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, self::NOT_EXISTING_ID, $this->getEditValue());
                }
            );
        });
        $this->should('throw a NotFoundException when the bankAccount not exists', function (): void {
            $this->lookupStaffUseCase
                ->allows('handle')
                ->with($this->context, Permission::updateStaffs(), $this->examples->staffs[0]->id)
                ->andReturn(Seq::from($this->examples->staffs[0]->copy(['bankAccountId' => self::NOT_EXISTING_ID])));
            $this->lookupBankAccountUseCase
                ->expects('handle')
                ->with($this->context, self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->examples->staffs[0]->id, $this->getEditValue());
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
        return [
            'bankName' => $this->examples->bankAccounts[0]->bankName,
            'bankCode' => $this->examples->bankAccounts[0]->bankCode,
            'bankBranchName' => $this->examples->bankAccounts[0]->bankBranchName,
            'bankBranchCode' => $this->examples->bankAccounts[0]->bankBranchCode,
            'bankAccountType' => $this->examples->bankAccounts[0]->bankAccountType,
            'bankAccountNumber' => $this->examples->bankAccounts[0]->bankAccountNumber,
            'bankAccountHolder' => $this->examples->bankAccounts[0]->bankAccountHolder,
        ];
    }
}
