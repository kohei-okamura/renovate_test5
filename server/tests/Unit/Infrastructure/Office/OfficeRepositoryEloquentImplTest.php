<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Office;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Location;
use Domain\Common\Prefecture;
use Domain\Office\Office as DomainOffice;
use Domain\Office\OfficeStatus;
use Domain\Office\Purpose;
use Infrastructure\Office\OfficeRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Fixtures\OfficeFixture;
use Tests\Unit\Fixtures\OrganizationFixture;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * OfficeRepositoryEloquentImpl のテスト.
 */
final class OfficeRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use OfficeFixture;
    use OrganizationFixture;
    use UnitSupport;

    private OfficeRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->repository = app(OfficeRepositoryEloquentImpl::class);
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
            $expected = $this->examples->offices[0];
            $actual = $this->repository->lookup($this->examples->offices[0]->id);
            $this->assertEquals(1, $actual->size());
            $this->assertModelStrictEquals(
                $expected,
                $actual->head()
            );
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
        $this->should('add the entity to repository when it does not exist in repository', function (): void {
            $attrs = [
                'id' => self::NOT_EXISTING_ID,
                'organizationId' => $this->examples->organizations[0]->id,
                'officeGroupId' => $this->examples->officeGroups[0]->id,
                'name' => '土屋訪問介護事務所',
                'abbr' => '本社',
                'phoneticName' => 'ツチヤホウモンカイゴジムショ',
                'corporationName' => '事業所テスト',
                'phoneticCorporationName' => 'ジギョウショテスト',
                'purpose' => Purpose::internal(),
                'addr' => new Addr(
                    postcode: '164-0011',
                    prefecture: Prefecture::tokyo(),
                    city: '中野区',
                    street: '中央1-35-6',
                    apartment: 'レッチフィールド中野坂上ビル6F',
                ),
                'location' => Location::create([
                    'lat' => 12.345678,
                    'lng' => 123.456789,
                ]),
                'tel' => '03-1234-5678',
                'fax' => '03-1234-5679',
                'email' => 'sample@example.com',
                'qualifications' => $this->examples->offices[0]->qualifications,
                'dwsGenericService' => $this->examples->offices[0]->dwsGenericService,
                'dwsCommAccompanyService' => $this->examples->offices[0]->dwsCommAccompanyService,
                'ltcsCareManagementService' => $this->examples->offices[0]->ltcsCareManagementService,
                'ltcsHomeVisitLongTermCareService' => $this->examples->offices[0]->ltcsHomeVisitLongTermCareService,
                'ltcsCompHomeVisitingService' => $this->examples->offices[0]->ltcsCompHomeVisitingService,
                'ltcsPreventionService' => $this->examples->offices[0]->ltcsPreventionService,
                'status' => OfficeStatus::inOperation(),
                'isEnabled' => true,
                'version' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $entity = DomainOffice::create($attrs);
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $stored,
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals(Carbon::now(), $this->examples->offices[0]->createdAt);
            $office = $this->examples->offices[0]->copy(['version' => 2]);
            $this->repository->store($office);
            $actual = $this->repository->lookup($this->examples->offices[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $office,
                $actual->head()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_remove(): void
    {
        $this->should('remove the entity', function (): void {
            $office = $this->examples->offices[5];
            $this->repository->remove($office);
            $actual = $this->repository->lookup($this->examples->offices[5]->id);
            $this->assertCount(0, $actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_removeById(): void
    {
        $this->should('remove entities', function (): void {
            // リレーションのないID
            $this->repository->removeById(
                $this->examples->offices[5]->id,
                $this->examples->offices[6]->id
            );
            $office0 = $this->repository->lookup($this->examples->offices[5]->id);
            $this->assertCount(0, $office0);
            $office1 = $this->repository->lookup($this->examples->offices[6]->id);
            $this->assertCount(0, $office1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->offices[5]->id);
            $office0 = $this->repository->lookup($this->examples->offices[5]->id);
            $this->assertCount(0, $office0);
            $office1 = $this->repository->lookup($this->examples->offices[1]->id);
            $office2 = $this->repository->lookup($this->examples->offices[2]->id);
            $this->assertCount(1, $office1);
            $this->assertModelStrictEquals($this->examples->offices[1], $office1->head());
            $this->assertCount(1, $office2);
            $this->assertModelStrictEquals($this->examples->offices[2], $office2->head());
        });
    }
}
