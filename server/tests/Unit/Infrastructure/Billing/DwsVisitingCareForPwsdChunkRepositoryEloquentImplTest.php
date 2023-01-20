<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Billing;

use App\Concretes\TemporaryDatabaseTransactionManager;
use Domain\Billing\DwsVisitingCareForPwsdChunkImpl;
use Domain\Billing\DwsVisitingCareForPwsdChunkImpl as DomainDwsVisitingCareForPwsdChunkImpl;
use Domain\Billing\DwsVisitingCareForPwsdFragment;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Infrastructure\Billing\DwsVisitingCareForPwsdChunk;
use Infrastructure\Billing\DwsVisitingCareForPwsdChunkRepositoryEloquentImpl;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\Billing\DwsVisitingCareForPwsdChunkRepositoryEloquentImpl} Test.
 */
class DwsVisitingCareForPwsdChunkRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private DomainDwsVisitingCareForPwsdChunkImpl $exampleDomain;
    private DwsVisitingCareForPwsdChunkRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsVisitingCareForPwsdChunkRepositoryEloquentImplTest $self): void {
            $self->exampleDomain = DomainDwsVisitingCareForPwsdChunkImpl::create([
                'userId' => $self->examples->users[0]->id,
                'category' => DwsServiceCodeCategory::visitingCareForPwsd1(),
                'isEmergency' => false,
                'isFirst' => false,
                'isBehavioralDisorderSupportCooperation' => false,
                'providedOn' => Carbon::now()->startOfDay(),
                'range' => CarbonRange::create([
                    'start' => Carbon::now(),
                    'end' => Carbon::now()->addDay(),
                ]),
                'fragments' => Seq::from(
                    DwsVisitingCareForPwsdFragment::create([
                        'isCoaching' => false,
                        'isMoving' => false,
                        'isSecondary' => false,
                        'movingDurationMinutes' => 0,
                        'range' => CarbonRange::create([
                            'start' => Carbon::now(),
                            'end' => Carbon::now()->addMinute(),
                        ]),
                        'headcount' => 1,
                    ]),
                ),
            ]);
            $self->repository = app(DwsVisitingCareForPwsdChunkRepositoryEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_transactionManager(): void
    {
        $this->should('return a class name of DatabaseTransactionManager', function (): void {
            $this->assertSame(TemporaryDatabaseTransactionManager::class, $this->repository->transactionManager());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_lookup(): void
    {
        $this->should('return an entity when the id exists in db', function (): void {
            // Lookupするデータを保管
            $entity = DwsVisitingCareForPwsdChunk::fromDomain($this->exampleDomain);
            $entity->save();

            // テスト実施
            $actual = $this->repository->lookup($entity->id);

            // アサート
            $this->assertCount(1, $actual);
            $actualDomain = $actual->head();
            assert($actualDomain instanceof DomainDwsVisitingCareForPwsdChunkImpl);
            // Seqが含まれるので個別にassert
            $this->assertSame($this->exampleDomain->userId, $actualDomain->userId);
            $this->assertSame($this->exampleDomain->category, $actualDomain->category);
            $this->assertSame($this->exampleDomain->isEmergency, $actualDomain->isEmergency);
            $this->assertEquals($this->exampleDomain->providedOn, $actualDomain->providedOn);
            $this->assertModelStrictEquals($this->exampleDomain->range, $actualDomain->range);
            $this->assertArrayStrictEquals(
                $this->exampleDomain->fragments->toArray(),
                $actualDomain->fragments->toArray()
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
        $this->should('return the entity with id when original entity not specified id', function (): void {
            $entity = DomainDwsVisitingCareForPwsdChunkImpl::create([
                'userId' => $this->examples->users[0]->id,
                'category' => DwsServiceCodeCategory::housework(),
                'isEmergency' => false,
                'isFirst' => false,
                'isBehavioralDisorderSupportCooperation' => false,
                'providedOn' => Carbon::now()->startOfDay(),
                'range' => CarbonRange::create([
                    'start' => Carbon::now(),
                    'end' => Carbon::now()->addMinute(),
                ]),
                'fragments' => Seq::from(
                    DwsVisitingCareForPwsdFragment::create([
                        'isCoaching' => false,
                        'isMoving' => false,
                        'isSecondary' => false,
                        'movingDurationMinutes' => 0,
                        'range' => CarbonRange::create([
                            'start' => Carbon::now(),
                            'end' => Carbon::now()->addMinute(),
                        ]),
                        'headcount' => 1,
                    ]),
                ),
            ]);

            $stored = $this->repository->store($entity);

            $this->assertModelStrictEquals(
                $entity->copy(['id' => $stored->id]),
                $stored
            );
        });
        $this->should('update the entity', function () {
            // 事前にデータ登録
            $entity = DwsVisitingCareForPwsdChunk::fromDomain($this->exampleDomain);
            $entity->save();

            // 更新するEntity
            $update = $this->exampleDomain->copy([
                'id' => $entity->id,
                'isEmergency' => true,
            ]);

            // テスト実施
            $this->repository->store($update);

            // アサート
            $actual = $this->repository->lookup($entity->id);
            $this->assertCount(1, $actual);
            $actualChunk = $actual->head();
            assert($actualChunk instanceof DwsVisitingCareForPwsdChunkImpl);
            $actualChunk->fragments->toArray(); // fragments が Seq なので toArray() して、computed=trueにする
            $this->assertModelStrictEquals($update, $actualChunk);
        });
        $this->should('return stored entity', function (): void {
            // テスト実施
            $stored = $this->repository->store($this->exampleDomain);

            // 保存したIDをLookup (DBの値）
            $expected = $this->repository->lookup($stored->id);

            // アサート（Lookup と storeの戻り値一致）
            assert($stored instanceof DwsVisitingCareForPwsdChunkImpl);
            $stored->fragments->toArray();
            $expectedChunk = $expected->head();
            assert($expectedChunk instanceof DwsVisitingCareForPwsdChunkImpl);
            $expectedChunk->fragments->toArray();
            $this->assertModelStrictEquals($expectedChunk, $stored);
        });
    }

    /**
     * @test
     * @throws \Throwable
     * @return void
     */
    public function describe_remove(): void
    {
        $this->should('not lookup by removed Id', function (): void {
            // 事前にデータ登録
            $entity = DwsVisitingCareForPwsdChunk::fromDomain($this->exampleDomain);
            $entity->save();

            // テスト実行
            $this->repository->removeById($entity->id);

            // アサート（取得0件）
            $this->assertEmpty($this->repository->lookup($entity->id));
        });
    }
}
