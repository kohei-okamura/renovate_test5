<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\BankAccount;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\BankAccount\BankAccount as DomainBankAccount;
use Domain\BankAccount\BankAccountType;
use Domain\Common\Carbon;
use Infrastructure\BankAccount\BankAccountRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * BankAccountRepositoryEloquentImpl のテスト.
 */
final class BankAccountRepositoryEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private BankAccountRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (BankAccountRepositoryEloquentImplTest $self): void {
            $self->repository = app(BankAccountRepositoryEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_transactionManager(): void
    {
        $this->should('return a class name of DatabaseTransactionManager', function (): void {
            $this->assertSame(PermanentDatabaseTransactionManager::class, $this->repository->transactionManager());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_lookup(): void
    {
        $this->should('return an entity when the id exists in db', function (): void {
            $actual = $this->repository->lookup($this->examples->bankAccounts[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $this->examples->bankAccounts[0],
                $actual->head()
            );
        });
        $this->should('return empty seq NotFoundException when the id not exists in db', function (): void {
            $actual = $this->repository->lookup(self::NOT_EXISTING_ID);
            $this->assertCount(0, $actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_store(): void
    {
        $this->should('add the entity to repository when it does not exist in repository', function (): void {
            $attrs = [
                'id' => self::NOT_EXISTING_ID,
                'bankName' => 'ユースタイル銀行',
                'bankCode' => '0123',
                'bankBranchName' => '中野ハーモニータワー支店',
                'bankBranchCode' => '456',
                'bankAccountType' => BankAccountType::ordinaryDeposit(),
                'bankAccountNumber' => '0123456',
                'bankAccountHolder' => 'ﾕｰｽﾀｲﾙﾗﾎﾞﾗﾄﾘｰ-ABC().-/',
                'version' => 1,
                'createdAt' => Carbon::create(2019, 1, 1, 1, 1, 1),
                'updatedAt' => Carbon::create(2019, 2, 2, 2, 2, 2),
            ];
            $entity = DomainBankAccount::create($attrs);
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $stored,
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals('ラボラトリー銀行', $this->examples->bankAccounts[0]->bankName);
            $bankAccount = $this->examples->bankAccounts[0]->copy(['version' => 2]);
            $this->repository->store($bankAccount);
            $actual = $this->repository->lookup($this->examples->bankAccounts[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $bankAccount,
                $actual->head()
            );
        });
        $this->should('return stored entity', function (): void {
            $entity = $this->examples->bankAccounts[0]->copy(['bankName' => 'ラボラトリー銀行', 'version' => 2]);
            $this->assertNotEquals('ラボラトリー銀行', $this->examples->bankAccounts[0]->bankName);
            $this->assertModelStrictEquals(
                $entity,
                $this->repository->store($entity)
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_removeById(): void
    {
        $this->should('remove entities', function (): void {
            $this->repository->removeById($this->examples->bankAccounts[5]->id, $this->examples->bankAccounts[6]->id);
            $contract0 = $this->repository->lookup($this->examples->bankAccounts[5]->id);
            $this->assertCount(0, $contract0);
            $contract1 = $this->repository->lookup($this->examples->bankAccounts[6]->id);
            $this->assertCount(0, $contract1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->bankAccounts[5]->id);
            $contract0 = $this->repository->lookup($this->examples->bankAccounts[5]->id);
            $this->assertCount(0, $contract0);
            $contract1 = $this->repository->lookup($this->examples->bankAccounts[1]->id);
            $contract2 = $this->repository->lookup($this->examples->bankAccounts[2]->id);
            $this->assertCount(1, $contract1);
            $this->assertModelStrictEquals($this->examples->bankAccounts[1], $contract1->head());
            $this->assertCount(1, $contract2);
            $this->assertModelStrictEquals($this->examples->bankAccounts[2], $contract2->head());
        });
    }
}
