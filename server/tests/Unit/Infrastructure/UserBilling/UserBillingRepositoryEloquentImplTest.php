<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\UserBilling;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\BankAccount\BankAccountType;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\ConsumptionTaxRate;
use Domain\Common\Contact;
use Domain\Common\ContactRelationship;
use Domain\Common\Decimal;
use Domain\Common\Prefecture;
use Domain\Common\StructuredName;
use Domain\User\BillingDestination;
use Domain\User\PaymentMethod;
use Domain\User\UserBillingDestination;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingBankAccount;
use Domain\UserBilling\UserBillingDwsItem;
use Domain\UserBilling\UserBillingLtcsItem;
use Domain\UserBilling\UserBillingOffice;
use Domain\UserBilling\UserBillingOtherItem;
use Domain\UserBilling\UserBillingResult;
use Domain\UserBilling\UserBillingUser;
use Domain\UserBilling\WithdrawalResultCode;
use Infrastructure\UserBilling\UserBillingRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\UserBilling\UserBillingRepositoryEloquentImpl} のテスト.
 */
final class UserBillingRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private UserBillingRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->repository = app(UserBillingRepositoryEloquentImpl::class);
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
            $expected = $this->examples->userBillings[0];
            $actual = $this->repository->lookup($this->examples->userBillings[0]->id);

            $this->assertEquals(1, $actual->size());
            $this->assertModelStrictEquals($expected, $actual->head());
        });
        $this->should('return empty seq when the id not exists in db', function (): void {
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
        $this->should('add the entity', function (): void {
            $entity = $this->examples->userBillings[0]->copy(['id' => null]);
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertForAll($this->examples->userBillings, fn (UserBilling $x): bool => $x->id !== $stored->id);
            $this->assertModelStrictEquals(
                $entity->copy(['id' => $stored->id]),
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $newTax = ConsumptionTaxRate::zero();
            $this->assertNotEquals($newTax, $this->examples->userBillings[0]->dwsItem->tax);
            $userBilling = $this->examples->userBillings[0]->copy([
                'dwsItem' => $this->examples->userBillings[0]->dwsItem->copy(['tax' => $newTax]),
            ]);
            $this->repository->store($userBilling);
            $actual = $this->repository->lookup($this->examples->userBillings[0]->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $userBilling,
                $actual->head()
            );
        });
        $this->should('return stored entity', function (): void {
            $entity = $this->examples->userBillings[0]->copy(['id' => self::NOT_EXISTING_ID]);

            $stored = $this->repository->store($entity);
            $this->assertModelStrictEquals(
                $entity,
                $stored
            );
        });
        $this->should('store the entity when the dwsItem is null', function (): void {
            $entity = $this->examples->userBillings[0]->copy([
                'id' => self::NOT_EXISTING_ID,
                'dwsItem' => null,
            ]);
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $entity,
                $actual->head()
            );
        });
        $this->should('store the entity when the ltcsItem is null', function (): void {
            $entity = $this->examples->userBillings[0]->copy([
                'id' => self::NOT_EXISTING_ID,
                'ltcsItem' => null,
            ]);
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $entity,
                $actual->head()
            );
        });
        $this->should('store the entity when billingDestination of user is theirself', function (): void {
            $entity = UserBilling::create([
                'id' => 1,
                'organizationId' => $this->examples->organizations[0]->id,
                'userId' => $this->examples->users[0]->id,
                'officeId' => $this->examples->offices[0]->id,
                'user' => UserBillingUser::create([
                    'name' => new StructuredName(
                        familyName: 'てすと',
                        givenName: 'たろう',
                        phoneticFamilyName: 'テスト',
                        phoneticGivenName: 'タロウ',
                    ),
                    'addr' => new Addr(
                        postcode: '164-0011',
                        prefecture: Prefecture::tokyo(),
                        city: '中野区',
                        street: '中央1-35-6',
                        apartment: 'レッチフィールド中野坂上ビル6F',
                    ),
                    'contacts' => [
                        Contact::create([
                            'tel' => '01-2345-6789',
                            'relationship' => ContactRelationship::family(),
                            'name' => '田中花子',
                        ]),
                        Contact::create([
                            'tel' => '01-1111-2222',
                            'relationship' => ContactRelationship::lawyer(),
                            'name' => '佐藤太郎',
                        ]),
                    ],
                    'billingDestination' => UserBillingDestination::create([
                        'destination' => BillingDestination::theirself(),
                        'paymentMethod' => PaymentMethod::withdrawal(),
                        'contractNumber' => '0123456789',
                        'corporationName' => 'ユースタイルラボラトリー株式会社',
                        'agentName' => '山田太郎',
                        'addr' => null,
                        'tel' => '03-1234-5678',
                    ]),
                    'bankAccount' => UserBillingBankAccount::create([
                        'bankName' => 'ユースタイル銀行',
                        'bankCode' => '1234',
                        'bankBranchName' => '丸之内支店',
                        'bankBranchCode' => '005',
                        'bankAccountType' => BankAccountType::ordinaryDeposit(),
                        'bankAccountNumber' => '0123456',
                        'bankAccountHolder' => 'ﾕｰｽﾀｲﾙﾗﾎﾞﾗﾄﾘｰ-ABC().-/',
                    ]),
                ]),
                'office' => UserBillingOffice::create([
                    'name' => '事業所テスト',
                    'corporationName' => '事業所テスト',
                    'addr' => new Addr(
                        postcode: '164-0011',
                        prefecture: Prefecture::tokyo(),
                        city: '中野区',
                        street: '中央1-35-6',
                        apartment: 'レッチフィールド中野坂上ビル6F',
                    ),
                    'tel' => '012-245-6789',
                ]),
                'dwsItem' => UserBillingDwsItem::create([
                    'dwsStatementId' => $this->examples->dwsBillingStatements[0]->id,
                    'score' => 100,
                    'unitCost' => Decimal::fromInt(10_0000),
                    'subtotalCost' => 1000,
                    'tax' => ConsumptionTaxRate::ten(),
                    'medicalDeductionAmount' => 5000,
                    'benefitAmount' => 2000,
                    'subsidyAmount' => 1000,
                    'totalAmount' => 1000,
                    'copayWithoutTax' => 2000,
                    'copayWithTax' => 2200,
                ]),
                'ltcsItem' => UserBillingLtcsItem::create([
                    'ltcsStatementId' => $this->examples->ltcsBillingStatements[5]->id,
                    'score' => 100,
                    'unitCost' => Decimal::fromInt(10_0000),
                    'subtotalCost' => 1000,
                    'tax' => ConsumptionTaxRate::ten(),
                    'medicalDeductionAmount' => 5000,
                    'benefitAmount' => 2000,
                    'subsidyAmount' => 1000,
                    'totalAmount' => 1000,
                    'copayWithoutTax' => 2000,
                    'copayWithTax' => 2200,
                ]),
                'otherItems' => [
                    UserBillingOtherItem::create([
                        'score' => 100,
                        'unitCost' => Decimal::fromInt(10_0000),
                        'subtotalCost' => 1000,
                        'tax' => ConsumptionTaxRate::ten(),
                        'medicalDeductionAmount' => 5000,
                        'totalAmount' => 1000,
                        'copayWithoutTax' => 2000,
                        'copayWithTax' => 2200,
                    ]),
                    UserBillingOtherItem::create([
                        'score' => 200,
                        'unitCost' => Decimal::fromInt(20_0000),
                        'subtotalCost' => 2000,
                        'tax' => ConsumptionTaxRate::ten(),
                        'medicalDeductionAmount' => 10000,
                        'totalAmount' => 2000,
                        'copayWithoutTax' => 4000,
                        'copayWithTax' => 4400,
                    ]),
                ],
                'result' => UserBillingResult::paid(),
                'carriedOverAmount' => 1000,
                'withdrawalResultCode' => WithdrawalResultCode::done(),
                'providedIn' => Carbon::create(2021, 4),
                'issuedOn' => Carbon::create(2021, 4, 1),
                'depositedAt' => Carbon::now(),
                'transactedAt' => Carbon::now(),
                'deductedOn' => Carbon::create(2021, 4, 1),
                'dueDate' => Carbon::create(2021, 4, 1),
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]);
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $entity,
                $actual->head()
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
            $this->repository->removeById($this->examples->userBillings[0]->id, $this->examples->userBillings[1]->id);
            $userBilling0 = $this->repository->lookup($this->examples->userBillings[0]->id);
            $this->assertCount(0, $userBilling0);
            $userBilling1 = $this->repository->lookup($this->examples->userBillings[1]->id);
            $this->assertCount(0, $userBilling1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->userBillings[0]->id);
            $userBilling0 = $this->repository->lookup($this->examples->userBillings[0]->id);
            $this->assertCount(0, $userBilling0);
            $userBilling1 = $this->repository->lookup($this->examples->userBillings[1]->id);
            $this->assertCount(1, $userBilling1);
            $this->assertModelStrictEquals($this->examples->userBillings[1], $userBilling1->head());
        });
    }
}
