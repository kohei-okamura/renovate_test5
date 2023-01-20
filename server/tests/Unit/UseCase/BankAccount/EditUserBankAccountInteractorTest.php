<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\BankAccount;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\BankAccount\BankAccountType;
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
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\BankAccount\EditUserBankAccountInteractor;

/**
 * EditUserBankAccountInteractor のテスト.
 */
class EditUserBankAccountInteractorTest extends Test
{
    use BankAccountRepositoryMixin;
    use CarbonMixin;
    use ContextMixin;
    use LookupUserUseCaseMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupBankAccountUseCaseMixin;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private EditUserBankAccountInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (EditUserBankAccountInteractorTest $self): void {
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();
            $self->lookupBankAccountUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->bankAccounts[0]))
                ->byDefault();
            $self->bankAccountRepository
                ->allows('store')
                ->andReturn($self->examples->bankAccounts[0])
                ->byDefault();
            $self->bankAccountRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(EditUserBankAccountInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use EnsureUserUseCase', function (): void {
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateUsers(), $this->examples->users[0]->id)
                ->andReturn(Seq::from($this->examples->users[0]));

            $this->interactor->handle(
                $this->context,
                $this->examples->users[0]->id,
                $this->getEditValue(),
            );
        });
        $this->should('throw a NotFoundException when the bankAccountId not exists in db', function (): void {
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateUsers(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        self::NOT_EXISTING_ID,
                        $this->getEditValue()
                    );
                }
            );
        });
        $this->should('throw a NotFoundException when the bankAccountId not exists in db', function (): void {
            $this->lookupBankAccountUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->users[0]->bankAccountId)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->examples->users[0]->id,
                        $this->getEditValue()
                    );
                }
            );
        });
        $this->should('edit the BankAccount after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に編集処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に編集処理が行われないことの検証は（恐らく）できない
                    $this->bankAccountRepository->expects('store')->andReturn($this->examples->bankAccounts[0]);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->examples->users[0]->id,
                $this->getEditValue()
            );
        });
        $this->should('return the BankAccount', function (): void {
            $this->assertModelStrictEquals(
                $this->examples->bankAccounts[0],
                $this->interactor->handle(
                    $this->context,
                    $this->examples->users[0]->id,
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
                ->with('利用者の銀行口座が更新されました', ['id' => $this->examples->bankAccounts[0]->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->examples->users[0]->id,
                $this->getEditValue()
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
            'bankName' => 'ユースタイル銀行',
            'bankCode' => '0123',
            'bankBranchName' => '中野ハーモニータワー支店',
            'bankBranchCode' => '456',
            'bankAccountType' => BankAccountType::ordinaryDeposit(),
            'bankAccountNumber' => '0123456',
            'bankAccountHolder' => 'ﾕｰｽﾀｲﾙﾗﾎﾞﾗﾄﾘｰ-ABC().-/',
        ];
    }
}
