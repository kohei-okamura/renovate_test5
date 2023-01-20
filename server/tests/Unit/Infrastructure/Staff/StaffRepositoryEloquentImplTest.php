<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Staff;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Location;
use Domain\Common\Password;
use Domain\Common\Prefecture;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\Staff\Certification;
use Domain\Staff\Staff as DomainStaff;
use Domain\Staff\StaffStatus;
use Infrastructure\Staff\StaffRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * StaffRepositoryEloquentImpl のテスト.
 */
final class StaffRepositoryEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private StaffRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->repository = app(StaffRepositoryEloquentImpl::class);
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
            $actual = $this->repository->lookup($this->examples->staffs[0]->id);
            $this->assertSame(1, $actual->size());
            $this->assertModelStrictEquals(
                $this->examples->staffs[0],
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
                'organizationId' => $this->examples->organizations[0]->id,
                'employeeNumber' => '123457',
                'name' => new StructuredName(
                    familyName: '内藤',
                    givenName: '勇介',
                    phoneticFamilyName: 'ナイトウ',
                    phoneticGivenName: 'ユウスケ',
                ),
                'sex' => Sex::male(),
                'birthday' => Carbon::create(1985, 2, 24),
                'addr' => new Addr(
                    postcode: '133-0051',
                    prefecture: Prefecture::tokyo(),
                    city: '江戸川区',
                    street: '北小岩 XX-XX-XX',
                    apartment: 'XXX XXX号室',
                ),
                'location' => Location::create([
                    'lat' => 12.345678,
                    'lng' => 123.456789,
                ]),
                'tel' => '03-1234-5678',
                'fax' => '06-2525-2525',
                'email' => 'sample1@example.com',
                'password' => Password::fromString('PassWoRD'),
                'bankAccountId' => $this->examples->bankAccounts[0]->id,
                'roleIds' => [
                    $this->examples->roles[0]->id,
                    $this->examples->roles[1]->id,
                ],
                'officeIds' => [
                    $this->examples->offices[0]->id,
                    $this->examples->offices[1]->id,
                ],
                'certifications' => [
                    Certification::masseur(),
                ],
                'isVerified' => true,
                'status' => StaffStatus::active(),
                'isEnabled' => true,
                'version' => 1,
                'createdAt' => Carbon::create(2019, 1, 1, 1, 1, 1),
                'updatedAt' => Carbon::create(2019, 2, 2, 2, 2, 2),
            ];
            $entity = DomainStaff::create($attrs);

            $stored = $this->repository->store($entity);

            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $stored,
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals('河瀬', $this->examples->staffs[0]->name->familyName);
            $staff = $this->examples->staffs[0]->copy(['familyName' => '内藤', 'version' => 2]);
            $this->repository->store($staff);

            $actual = $this->repository->lookup($this->examples->staffs[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $staff,
                $actual->head()
            );
        });
        $this->should('return stored entity', function (): void {
            $entity = $this->examples->staffs[0]->copy(['familyName' => '河瀬', 'version' => 2]);
            $this->assertNotEquals('河瀬', $this->examples->staffs[0]->name->familyName);

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
    public function describe_remove(): void
    {
        $this->should('remove the entity', function (): void {
            $this->repository->remove($this->examples->staffs[3]);

            $actual = $this->repository->lookup($this->examples->staffs[3]->id);
            $this->assertCount(0, $actual);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->remove($this->examples->staffs[3]);

            $this->assertTrue($this->repository->lookup($this->examples->staffs[1]->id)->nonEmpty());
            $this->assertTrue($this->repository->lookup($this->examples->staffs[2]->id)->nonEmpty());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_removeById(): void
    {
        $this->should('remove entities', function (): void {
            $this->repository->removeById($this->examples->staffs[2]->id, $this->examples->staffs[3]->id);

            $staffs0 = $this->repository->lookup($this->examples->staffs[2]->id);
            $staffs1 = $this->repository->lookup($this->examples->staffs[3]->id);
            $this->assertCount(0, $staffs0);
            $this->assertCount(0, $staffs1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->staffs[3]->id);

            $this->assertTrue($this->repository->lookup($this->examples->staffs[1]->id)->nonEmpty());
            $this->assertTrue($this->repository->lookup($this->examples->staffs[2]->id)->nonEmpty());
        });
    }
}
