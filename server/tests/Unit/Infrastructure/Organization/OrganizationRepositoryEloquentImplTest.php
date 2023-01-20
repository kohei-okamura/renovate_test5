<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Organization;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Prefecture;
use Domain\Organization\Organization as DomainOrganization;
use Infrastructure\Organization\OrganizationRepositoryEloquentImpl;
use ScalikePHP\None;
use ScalikePHP\Some;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * OrganizationRepositoryEloquentImpl のテスト.
 */
final class OrganizationRepositoryEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    public const NOT_EXISTING_ORGANIZATION_CODE = 'nobody';

    private OrganizationRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->repository = app(OrganizationRepositoryEloquentImpl::class);
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
            $actual = $this->repository->lookup($this->examples->organizations[0]->id);

            $this->assertSame(1, $actual->size());
            $this->assertModelStrictEquals($this->examples->organizations[0], $actual->head());
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
                'code' => 'eustyle',
                'name' => 'ユースタイルラボラトリー株式会社',
                'addr' => new Addr(
                    postcode: '164-0011',
                    prefecture: Prefecture::tokyo(),
                    city: '中野区',
                    street: '中央1-35-6',
                    apartment: 'レッチフィールド中野坂上ビル6F',
                ),
                'tel' => '03-5937-6825',
                'fax' => '03-5937-6828',
                'isEnabled' => 1,
                'version' => 1,
                'createdAt' => Carbon::create(2019, 5, 5, 5, 5, 5),
                'updatedAt' => Carbon::create(2019, 6, 6, 6, 6, 6),
            ];
            $entity = DomainOrganization::create($attrs);

            $stored = $this->repository->store($entity);

            $actual = $this->repository->lookup($stored->id);
            $this->assertSame(1, $actual->size());
            $this->assertModelStrictEquals($stored, $actual->head());
        });
        $this->should('update the entity', function (): void {
            $organization = $this->examples->organizations[0]->copy([
                'name' => 'ユースタイルラボラトリー株式会社',
                'version' => 2,
            ]);
            $this->assertNotEquals('ユースタイルラボラトリー株式会社', $this->examples->organizations[0]->name);

            $this->repository->store($organization);

            $actual = $this->repository->lookup($this->examples->organizations[0]->id);
            $this->assertSame(1, $actual->size());
            $this->assertModelStrictEquals($organization, $actual->head());
        });
        $this->should('return stored entity', function (): void {
            $entity = $this->examples->organizations[0]->copy(['name' => 'ユースタイルカレッジ', 'version' => 2]);
            $this->assertNotEquals('ユースタイルカレッジ', $this->examples->organizations[0]->name);

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
            $this->repository->remove($this->examples->organizations[2]);
            $actual = $this->repository->lookup($this->examples->organizations[2]->id);
            $this->assertCount(0, $actual);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->remove($this->examples->organizations[2]);

            $this->assertTrue($this->repository->lookup($this->examples->organizations[0]->id)->nonEmpty());
            $this->assertTrue($this->repository->lookup($this->examples->organizations[1]->id)->nonEmpty());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_removeById(): void
    {
        $this->should('remove entities', function (): void {
            $this->repository->removeById($this->examples->organizations[2]->id, $this->examples->organizations[3]->id);
            $actual = $this->repository->lookup($this->examples->organizations[2]->id);

            $this->assertCount(0, $actual);
            $actual = $this->repository->lookup($this->examples->organizations[3]->id);
            $this->assertCount(0, $actual);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->organizations[2]->id);

            $this->assertTrue($this->repository->lookup($this->examples->organizations[0]->id)->nonEmpty());
            $this->assertTrue($this->repository->lookup($this->examples->organizations[1]->id)->nonEmpty());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_lookupOptionByCode(): void
    {
        $this->should('return a some of entity when the code exists in db', function (): void {
            $expected = $this->examples->organizations[0];
            $option = $this->repository->lookupOptionByCode($expected->code);

            $this->assertInstanceOf(Some::class, $option);
            $this->assertModelStrictEquals($expected, $option->get());
        });
        $this->should('return none when the code not exists in db', function (): void {
            $this->assertInstanceOf(
                None::class,
                $this->repository->lookupOptionByCode(self::NOT_EXISTING_ORGANIZATION_CODE)
            );
        });
    }
}
