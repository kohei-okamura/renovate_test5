<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Contract;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Billing\LtcsExpiredReason;
use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Contract\Contract;
use Domain\Contract\Contract as DomainContract;
use Domain\Contract\ContractPeriod;
use Domain\Contract\ContractStatus;
use Infrastructure\Contract\ContractRepositoryEloquentImpl;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Fixtures\OrganizationFixture;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * ContractRepositoryEloquentImpl のテスト.
 */
class ContractRepositoryEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use OrganizationFixture;
    use UnitSupport;

    private ContractRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ContractRepositoryEloquentImplTest $self): void {
            $self->repository = app(ContractRepositoryEloquentImpl::class);
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
            $expected = $this->examples->contracts[0];
            $actual = $this->repository->lookup($this->examples->contracts[0]->id);

            $this->assertEquals(1, $actual->size());
            $this->assertModelStrictEquals($expected, $actual->head());
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
                'organizationId' => $this->examples->organizations[0]->id,
                'userId' => $this->examples->users[0]->id,
                'officeId' => $this->examples->offices[0]->id,
                'contractId' => $this->examples->contracts[0]->id,
                'serviceSegment' => ServiceSegment::ownExpense(),
                'status' => ContractStatus::provisional(),
                'contractedOn' => Carbon::now(),
                'terminatedOn' => Carbon::now(),
                'dwsPeriods' => [
                    DwsServiceDivisionCode::homeHelpService()->value() => ContractPeriod::create([
                        'start' => Carbon::create(2020, 1, 1),
                        'end' => Carbon::create(2020, 12, 31),
                    ]),
                    DwsServiceDivisionCode::visitingCareForPwsd()->value() => ContractPeriod::create([
                        'start' => Carbon::create(2021, 1, 1),
                        'end' => Carbon::create(2021, 12, 31),
                    ]),
                ],
                'ltcsPeriod' => ContractPeriod::create([
                    'start' => Carbon::create(2019, 1, 1),
                    'end' => Carbon::create(2019, 12, 31),
                ]),
                'expiredReason' => LtcsExpiredReason::hospitalized(),
                'note' => 'だるまさんがころんだ',
                'isEnabled' => true,
                'version' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $entity = DomainContract::create($attrs);
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $stored,
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals(Carbon::now(), $this->examples->contracts[0]->createdAt);
            $contract = $this->examples->contracts[0]->copy(['version' => 2]);
            $this->repository->store($contract);
            $actual = $this->repository->lookup($this->examples->contracts[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $contract,
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
            $this->repository->removeById($this->examples->contracts[0]->id, $this->examples->contracts[1]->id);
            $contract0 = $this->repository->lookup($this->examples->contracts[0]->id);
            $this->assertCount(0, $contract0);
            $contract1 = $this->repository->lookup($this->examples->contracts[1]->id);
            $this->assertCount(0, $contract1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->contracts[0]->id);
            $contract0 = $this->repository->lookup($this->examples->contracts[0]->id);
            $this->assertCount(0, $contract0);
            $contract1 = $this->repository->lookup($this->examples->contracts[1]->id);
            $contract2 = $this->repository->lookup($this->examples->contracts[2]->id);
            $this->assertCount(1, $contract1);
            $this->assertModelStrictEquals($this->examples->contracts[1], $contract1->head());
            $this->assertCount(1, $contract2);
            $this->assertModelStrictEquals($this->examples->contracts[2], $contract2->head());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_lookupByUserId(): void
    {
        $this->should('return Map of Seq with User ID of key', function () {
            $ids = [
                $this->examples->users[0]->id,
                $this->examples->users[1]->id,
            ];
            $actual = $this->repository->lookupByUserId(...$ids);

            $this->assertInstanceOf(Map::class, $actual);
            $actual->each(function (Seq $x, int $key) use ($ids): void {
                $this->assertTrue(in_array($key, $ids, true));
                $this->assertForAll($x, fn (Contract $contract): bool => $contract->userId === $key);
            });
        });
    }
}
