<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\User;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Contact;
use Domain\Common\ContactRelationship;
use Domain\Common\Location;
use Domain\Common\Prefecture;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\User\BillingDestination;
use Domain\User\PaymentMethod;
use Domain\User\User as DomainUser;
use Domain\User\UserBillingDestination;
use Infrastructure\User\UserRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * UserRepositoryEloquentImpl のテスト.
 */
final class UserRepositoryEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private UserRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->repository = app(UserRepositoryEloquentImpl::class);
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
            $actual = $this->repository->lookup($this->examples->users[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $this->examples->users[0],
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
                'bankAccountId' => $this->examples->bankAccounts[0]->id,
                'name' => new StructuredName(
                    familyName: '新垣',
                    givenName: '栄作',
                    phoneticFamilyName: 'シンガキ',
                    phoneticGivenName: 'エイサク',
                ),
                'sex' => Sex::male(),
                'birthday' => Carbon::create(1982, 5, 9),
                'addr' => new Addr(
                    postcode: '351-0106',
                    prefecture: Prefecture::saitama(),
                    city: '和光市',
                    street: '広沢 XX-XX-XX',
                    apartment: 'XXX XXX号室',
                ),
                'location' => Location::create([
                    'lat' => 12.345678,
                    'lng' => 123.456789,
                ]),
                'contacts' => [
                    Contact::create([
                        'tel' => '01-2345-6789',
                        'relationship' => ContactRelationship::family(),
                        'name' => '田中花子',
                    ]),
                ],
                'email' => 'sample1@example.com',
                'billingDestination' => UserBillingDestination::create([
                    'destination' => BillingDestination::agent(),
                    'paymentMethod' => PaymentMethod::withdrawal(),
                    'contractNumber' => '0123456789',
                    'corporationName' => 'ユースタイルラボラトリー株式会社',
                    'agentName' => '山田太郎',
                    'addr' => new Addr(
                        postcode: '164-0011',
                        prefecture: Prefecture::tokyo(),
                        city: '中野区',
                        street: '中央1-35-6',
                        apartment: 'レッチフィールド中野坂上ビル6F',
                    ),
                    'tel' => '03-1234-5678',
                ]),
                'isEnabled' => true,
                'version' => 1,
                'createdAt' => Carbon::create(2019, 1, 1, 1, 1, 1),
                'updatedAt' => Carbon::create(2019, 2, 2, 2, 2, 2),
            ];
            $entity = DomainUser::create($attrs);
            $stored = $this->repository->store($entity);

            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $stored,
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals('河瀬', $this->examples->users[0]->name->familyName);
            $user = $this->examples->users[0]->copy(['familyName' => '新垣', 'version' => 2]);
            $this->repository->store($user);

            $actual = $this->repository->lookup($this->examples->users[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $user,
                $actual->head()
            );
        });
        $this->should('return stored entity', function (): void {
            $entity = $this->examples->users[0]->copy(['familyName' => '河瀬', 'version' => 2]);
            $this->assertNotEquals('河瀬', $this->examples->users[0]->name->familyName);

            $this->assertModelStrictEquals(
                $entity,
                $this->repository->store($entity)
            );
        });
        $this->should('store the entity when destination is theirself', function (): void {
            $attrs = [
                'id' => self::NOT_EXISTING_ID,
                'organizationId' => $this->examples->organizations[0]->id,
                'bankAccountId' => $this->examples->bankAccounts[0]->id,
                'name' => new StructuredName(
                    familyName: '新垣',
                    givenName: '栄作',
                    phoneticFamilyName: 'シンガキ',
                    phoneticGivenName: 'エイサク',
                ),
                'sex' => Sex::male(),
                'birthday' => Carbon::create(1982, 5, 9),
                'addr' => new Addr(
                    postcode: '351-0106',
                    prefecture: Prefecture::saitama(),
                    city: '和光市',
                    street: '広沢 XX-XX-XX',
                    apartment: 'XXX XXX号室',
                ),
                'location' => Location::create([
                    'lat' => 12.345678,
                    'lng' => 123.456789,
                ]),
                'contacts' => [
                    Contact::create([
                        'tel' => '01-2345-6789',
                        'relationship' => ContactRelationship::family(),
                        'name' => '田中花子',
                    ]),
                ],
                'email' => 'sample1@example.com',
                'billingDestination' => UserBillingDestination::create([
                    'destination' => BillingDestination::theirself(),
                    'paymentMethod' => PaymentMethod::withdrawal(),
                    'contractNumber' => '0123456789',
                    'corporationName' => 'ユースタイルラボラトリー株式会社',
                    'agentName' => '山田太郎',
                    'addr' => null,
                    'tel' => '03-1234-5678',
                ]),
                'isEnabled' => true,
                'version' => 1,
                'createdAt' => Carbon::create(2019, 1, 1, 1, 1, 1),
                'updatedAt' => Carbon::create(2019, 2, 2, 2, 2, 2),
            ];
            $entity = DomainUser::create($attrs);
            $stored = $this->repository->store($entity);

            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $stored,
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
            $this->repository->remove($this->examples->users[12]);

            $actual = $this->repository->lookup($this->examples->users[12]->id);
            $this->assertCount(0, $actual);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->remove($this->examples->users[12]);

            $this->assertTrue($this->repository->lookup($this->examples->users[1]->id)->nonEmpty());
            $this->assertTrue($this->repository->lookup($this->examples->users[2]->id)->nonEmpty());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_removeById(): void
    {
        $this->should('remove entities', function (): void {
            $this->repository->removeById($this->examples->users[12]->id, $this->examples->users[13]->id);

            $users0 = $this->repository->lookup($this->examples->users[12]->id);
            $users1 = $this->repository->lookup($this->examples->users[13]->id);
            $this->assertCount(0, $users0);
            $this->assertCount(0, $users1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->users[12]->id);

            $actual = $this->repository->lookup($this->examples->users[12]->id);
            $this->assertCount(0, $actual);

            $this->assertTrue($this->repository->lookup($this->examples->users[1]->id)->nonEmpty());
            $this->assertTrue($this->repository->lookup($this->examples->users[2]->id)->nonEmpty());
        });
    }
}
