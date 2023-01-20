<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Concretes;

use App\Concretes\ComposedTransactionManager;
use App\Concretes\DefaultTransactionManager;
use App\Concretes\PermanentDatabaseTransactionManager;
use App\Concretes\TransactionManagerFactoryImpl;
use Domain\Repository;
use Mockery;
use ReflectionClass;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * TransactionManagerFactory のテスト.
 */
class TransactionManagerFactoryTest extends Test
{
    use MockeryMixin;
    use UnitSupport;

    private TransactionManagerFactoryImpl $factory;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (TransactionManagerFactoryTest $self): void {
            $self->factory = app(TransactionManagerFactoryImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_factory(): void
    {
        $this->should(
            'return the TransactionManager specified by repository',
            function (): void {
                $repository = Mockery::mock(Repository::class);
                $repository->expects('transactionManager')->andReturn(DefaultTransactionManager::class);

                $this->assertInstanceOf(
                    DefaultTransactionManager::class,
                    $this->factory->factory($repository)
                );
            }
        );
        $this->should(
            'return the TransactionManager specified by repositories',
            function (): void {
                $a = Mockery::mock(Repository::class);
                $b = Mockery::mock(Repository::class);
                $a->expects('transactionManager')->andReturn(PermanentDatabaseTransactionManager::class);
                $b->expects('transactionManager')->andReturn(PermanentDatabaseTransactionManager::class);

                $this->assertInstanceOf(
                    PermanentDatabaseTransactionManager::class,
                    $this->factory->factory($a, $b)
                );
            }
        );
        $this->should(
            'return a ComposedTransactionManager when repositories expect different TransactionManagers',
            function (): void {
                $a = Mockery::mock(Repository::class);
                $b = Mockery::mock(Repository::class);
                $c = Mockery::mock(Repository::class);
                $a->expects('transactionManager')->andReturn(DefaultTransactionManager::class);
                $b->expects('transactionManager')->andReturn(PermanentDatabaseTransactionManager::class);
                $c->expects('transactionManager')->andReturn(DefaultTransactionManager::class);

                $manager = $this->factory->factory($a, $b, $c);
                $reflection = new ReflectionClass($manager);
                $property = $reflection->getProperty('managers');
                $property->setAccessible(true);
                $managers = $property->getValue($manager)->map(fn ($x) => get_class($x))->toArray();

                $this->assertInstanceOf(ComposedTransactionManager::class, $manager);
                $this->assertSame(
                    [DefaultTransactionManager::class, PermanentDatabaseTransactionManager::class],
                    $managers
                );
            }
        );
    }
}
