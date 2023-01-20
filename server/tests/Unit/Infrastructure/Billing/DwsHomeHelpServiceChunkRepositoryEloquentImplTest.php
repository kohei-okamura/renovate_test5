<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Billing;

use App\Concretes\TemporaryDatabaseTransactionManager;
use Domain\Billing\DwsHomeHelpServiceChunkImpl as DomainDwsHomeHelpServiceChunk;
use Domain\Billing\DwsHomeHelpServiceFragment;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceBuildingType;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Infrastructure\Billing\DwsHomeHelpServiceChunk;
use Infrastructure\Billing\DwsHomeHelpServiceChunkRepositoryEloquentImpl;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\Billing\DwsHomeHelpServiceChunkRepositoryEloquentImpl} Test.
 */
class DwsHomeHelpServiceChunkRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private DomainDwsHomeHelpServiceChunk $exampleDomain;
    private DwsHomeHelpServiceChunkRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsHomeHelpServiceChunkRepositoryEloquentImplTest $self): void {
            $self->exampleDomain = DomainDwsHomeHelpServiceChunk::create([
                'userId' => $self->examples->users[0]->id,
                'category' => DwsServiceCodeCategory::housework(),
                'buildingType' => DwsHomeHelpServiceBuildingType::none(),
                'isEmergency' => false,
                'isPlannedByNovice' => false,
                'isFirst' => false,
                'isWelfareSpecialistCooperation' => false,
                'range' => CarbonRange::create([
                    'start' => Carbon::now(),
                    'end' => Carbon::now()->addMinute(),
                ]),
                'fragments' => Seq::fromArray([
                    DwsHomeHelpServiceFragment::create([
                        'providerType' => DwsHomeHelpServiceProviderType::none(),
                        'isSecondary' => false,
                        'range' => CarbonRange::create([
                            'start' => Carbon::now(),
                            'end' => Carbon::now()->addMinute(),
                        ]),
                        'headcount' => 1,
                    ]),
                ]),
            ]);
            $self->repository = app(DwsHomeHelpServiceChunkRepositoryEloquentImpl::class);
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
            $entity = DwsHomeHelpServiceChunk::fromDomain($this->exampleDomain);
            $entity->save();

            // テスト実施
            $actual = $this->repository->lookup($entity->id);

            // アサート
            $this->assertCount(1, $actual);
            $actualDomain = $actual->head();
            assert($actualDomain instanceof DomainDwsHomeHelpServiceChunk);
            // Seqが含まれるので個別にassert
            $this->assertEquals($this->exampleDomain->userId, $actualDomain->userId);
            $this->assertEquals($this->exampleDomain->category, $actualDomain->category);
            $this->assertEquals($this->exampleDomain->buildingType, $actualDomain->buildingType);
            $this->assertEquals($this->exampleDomain->isEmergency, $actualDomain->isEmergency);
            $this->assertEquals($this->exampleDomain->isPlannedByNovice, $actualDomain->isPlannedByNovice);
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
            $entity = DomainDwsHomeHelpServiceChunk::create([
                'userId' => $this->examples->users[0]->id,
                'category' => DwsServiceCodeCategory::housework(),
                'buildingType' => DwsHomeHelpServiceBuildingType::none(),
                'isEmergency' => false,
                'isPlannedByNovice' => false,
                'isFirst' => false,
                'isWelfareSpecialistCooperation' => false,
                'range' => CarbonRange::create([
                    'start' => Carbon::now(),
                    'end' => Carbon::now()->addMinute(),
                ]),
                'fragments' => Seq::fromArray([
                    DwsHomeHelpServiceFragment::create([
                        'providerType' => DwsHomeHelpServiceProviderType::none(),
                        'isSecondary' => false,
                        'range' => CarbonRange::create([
                            'start' => Carbon::now(),
                            'end' => Carbon::now()->addMinute(),
                        ]),
                        'headcount' => 1,
                    ]),
                ]),
            ]);

            $stored = $this->repository->store($entity);

            $this->assertModelStrictEquals(
                $entity->copy(['id' => $stored->id]),
                $stored
            );
        });
        $this->should('update the entity', function () {
            // 事前にデータ登録
            $entity = DwsHomeHelpServiceChunk::fromDomain($this->exampleDomain);
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
            $this->assertModelStrictEquals($update, $actual->head());
        });
        $this->should('return stored entity', function (): void {
            // テスト実施
            $stored = $this->repository->store($this->exampleDomain);

            // 保存したIDをLookup (DBの値）
            $expected = $this->repository->lookup($stored->id);

            // アサート（Lookup と storeの戻り値一致）
            $this->assertModelStrictEquals($expected->head(), $stored);
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
            $entity = DwsHomeHelpServiceChunk::fromDomain($this->exampleDomain);
            $entity->save();

            // テスト実行
            $this->repository->removeById($entity->id);

            // アサート（取得0件）
            $this->assertEmpty($this->repository->lookup($entity->id));
        });
    }
}
