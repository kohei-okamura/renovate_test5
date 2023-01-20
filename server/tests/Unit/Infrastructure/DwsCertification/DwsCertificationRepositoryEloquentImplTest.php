<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\DwsCertification;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Common\Carbon;
use Domain\DwsCertification\DwsCertification;
use Domain\DwsCertification\DwsCertification as DomainDwsCertification;
use Domain\DwsCertification\DwsCertificationAgreement;
use Domain\DwsCertification\DwsCertificationAgreementType;
use Domain\DwsCertification\DwsCertificationGrant;
use Domain\DwsCertification\DwsCertificationServiceType;
use Infrastructure\DwsCertification\DwsCertificationRepositoryEloquentImpl;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\DwsCertification\DwsCertificationRepositoryEloquentImpl} のテスト.
 */
final class DwsCertificationRepositoryEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private DwsCertificationRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->repository = app(DwsCertificationRepositoryEloquentImpl::class);
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
            $expected = $this->examples->dwsCertifications[0];
            $actual = $this->repository->lookup($this->examples->dwsCertifications[0]->id);
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
        $this->should(
            'return an entity when entity has multiple DwsCertificationAgreement and DwsCertificationGrant',
            function (): void {
                $dwsCertification = $this->examples->dwsCertifications[0]->copy([
                    'agreements' => [
                        DwsCertificationAgreement::create([
                            'indexNumber' => 1,
                            'officeId' => $this->examples->offices[2]->id,
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::physicalCare(),
                            'paymentAmount' => 100,
                            'agreedOn' => Carbon::parse('2020-10-28'),
                            'expiredOn' => Carbon::parse('2020-10-28'),
                        ]),
                        DwsCertificationAgreement::create([
                            'indexNumber' => 2,
                            'officeId' => $this->examples->offices[2]->id,
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::physicalCare(),
                            'paymentAmount' => 100,
                            'agreedOn' => Carbon::parse('2020-10-28'),
                            'expiredOn' => Carbon::parse('2020-10-28'),
                        ]),
                    ],
                    'grants' => [
                        DwsCertificationGrant::create([
                            'dwsCertificationServiceType' => DwsCertificationServiceType::physicalCare(),
                            'grantedAmount' => 'test',
                            'activatedOn' => Carbon::parse('2020-10-28'),
                            'deactivatedOn' => Carbon::parse('2020-10-28'),
                        ]),
                        DwsCertificationGrant::create([
                            'dwsCertificationServiceType' => DwsCertificationServiceType::housework(),
                            'grantedAmount' => 'test',
                            'activatedOn' => Carbon::parse('2020-10-28'),
                            'deactivatedOn' => Carbon::parse('2020-10-28'),
                        ]),
                    ],
                    'version' => $this->examples->dwsCertifications[0]->version + 1,
                ]);
                $this->repository->store($dwsCertification);
                $actual = $this->repository->lookup($this->examples->dwsCertifications[0]->id);
                $this->assertModelStrictEquals(
                    $dwsCertification,
                    $actual->head()
                );
            }
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_store(): void
    {
        $this->should('add the entity to repository when it does not exist in repository', function (): void {
            $x = $this->examples->dwsCertifications[0];
            $attrs = [
                'id' => self::NOT_EXISTING_ID,
                'userId' => $this->examples->users[0]->id,
                'dwsLevel' => $x->dwsLevel,
                'status' => $x->status,
                'dwsTypes' => $x->dwsTypes,
                'copayCoordination' => $x->copayCoordination,
                'child' => $x->child,
                'dwsNumber' => $x->dwsNumber,
                'cityCode' => $x->cityCode,
                'cityName' => $x->cityName,
                'copayRate' => $x->copayRate,
                'copayLimit' => $x->copayLimit,
                'isSubjectOfComprehensiveSupport' => $x->isSubjectOfComprehensiveSupport,
                'agreements' => $x->agreements,
                'grants' => $x->grants,
                'issuedOn' => $x->issuedOn,
                'effectivatedOn' => $x->effectivatedOn,
                'activatedOn' => $x->activatedOn,
                'deactivatedOn' => $x->deactivatedOn,
                'copayActivatedOn' => $x->copayActivatedOn,
                'copayDeactivatedOn' => $x->copayDeactivatedOn,
                'isEnabled' => true,
                'version' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $entity = DomainDwsCertification::create($attrs);
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $stored,
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals(Carbon::now(), $this->examples->dwsCertifications[0]->createdAt);
            $dwsCertification = $this->examples->dwsCertifications[0]->copy([
                'grants' => $this->examples->dwsCertifications[8]->grants,
                'agreements' => $this->examples->dwsCertifications[9]->agreements,
                'version' => 2,
            ]);

            $actual = $this->repository->store($dwsCertification);
            $stored = $this->repository->lookup($this->examples->dwsCertifications[0]->id);

            $this->assertCount(1, $stored);
            $this->assertModelStrictEquals($dwsCertification, $actual);
            $this->assertModelStrictEquals($dwsCertification, $stored->head());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_remove(): void
    {
        $this->should('remove the entity', function (): void {
            $dwsCertification = $this->examples->dwsCertifications[0];
            $this->repository->remove($dwsCertification);
            $actual = $this->repository->lookup($this->examples->dwsCertifications[0]->id);
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
            $this->repository->removeById(
                $this->examples->dwsCertifications[0]->id,
                $this->examples->dwsCertifications[1]->id
            );
            $dwsCertification0 = $this->repository->lookup($this->examples->dwsCertifications[0]->id);
            $this->assertCount(0, $dwsCertification0);
            $dwsCertification1 = $this->repository->lookup($this->examples->dwsCertifications[1]->id);
            $this->assertCount(0, $dwsCertification1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->dwsCertifications[0]->id);
            $dwsCertification0 = $this->repository->lookup($this->examples->dwsCertifications[0]->id);
            $this->assertCount(0, $dwsCertification0);
            $dwsCertification1 = $this->repository->lookup($this->examples->dwsCertifications[1]->id);
            $dwsCertification2 = $this->repository->lookup($this->examples->dwsCertifications[2]->id);
            $this->assertCount(1, $dwsCertification1);
            $this->assertModelStrictEquals($this->examples->dwsCertifications[1], $dwsCertification1->head());
            $this->assertCount(1, $dwsCertification2);
            $this->assertModelStrictEquals($this->examples->dwsCertifications[2], $dwsCertification2->head());
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
                $this->assertForAll($x, fn (DwsCertification $bundle): bool => $bundle->userId === $key);
            });
        });
    }
}
