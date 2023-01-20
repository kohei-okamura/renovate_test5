<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Concretes;

use App\Concretes\ComposedTransactionManager;
use Closure;
use Domain\TransactionManager;
use Lib\Exceptions\RuntimeException;
use Mockery;
use ReflectionClass;
use ScalikePHP\Seq;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Concretes\ComposedTransactionManager} Test.
 */
class ComposedTransactionManagerTest extends Test
{
    use MockeryMixin;
    use UnitSupport;

    /** @var \Domain\TransactionManager|\Mockery\MockInterface */
    private $firstTransactionManager;
    /** @var \Domain\TransactionManager|\Mockery\MockInterface */
    private $secondTransactionManager;
    private ComposedTransactionManager $manager;
    /** @var \Mockery\MockInterface */
//    private $firstSpy;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ComposedTransactionManagerTest $self): void {
            $self->firstTransactionManager = Mockery::mock(TransactionManager::class);
            $self->firstSpy = Mockery::spy(function (): void {
            });
            $self->secondTransactionManager = Mockery::mock(TransactionManager::class);
            $self->manager = new ComposedTransactionManager(Seq::from(
                $self->firstTransactionManager,
                $self->secondTransactionManager
            ));
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_compose(): void
    {
        $this->should('distinct manager when pass same transactions', function (): void {
            $manager = ComposedTransactionManager::compose(
                $this->firstTransactionManager,
                $this->secondTransactionManager
            );

            // private メンバに格納されたデータを検証する、リフレクションする
            $reflection = new ReflectionClass($manager);
            $property = $reflection->getProperty('managers');
            $property->setAccessible(true);
            $actuals = $property->getValue($manager)->toArray();

            $this->assertCount(1, $actuals);
            $this->assertEquals([$this->secondTransactionManager], $actuals);
        });
        $this->should('store all managers passed by arguments', function (): void {
            $spy = Mockery::spy(function (): void {
            });
            $manager = ComposedTransactionManager::compose(
                $this->firstTransactionManager,
                new class($spy) implements TransactionManager {
                    // Mock を使うと同じクラスのインスタンスなのでdistinctされてしまうため、無名クラスを実装
                    private $spy;

                    public function __construct($spy)
                    {
                        $this->spy = $spy;
                    }

                    public function run(Closure $f)
                    {
                        $this->spy->run($f);
                    }

                    public function rollback(Closure $f)
                    {
                    }
                }
            );

            $reflection = new ReflectionClass($manager);
            $property = $reflection->getProperty('managers');
            $property->setAccessible(true);
            $actuals = $property->getValue($manager)->toArray();

            $this->assertCount(2, $actuals);
            $this->assertEquals($this->firstTransactionManager, $actuals[0]);

            // [1] は用意した無名関数であることを検証するために Spy を使ってメソッドが呼び出されていることを検証
            $f = function (): void {
            };
            $spy->expects('run')
                ->with($f)
                ->andReturnNull();
            $actuals[1]->run($f);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_run(): void
    {
        $this->should('call all run method', function (): void {
            $this->firstTransactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $x) {
                    return $x();
                });
            $this->secondTransactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $x) {
                    return $x();
                });

            $this->manager->run(function (): void {
            });
        });
        $this->should('call closure by first manager', function (): void {
            $expectedClosure = function (): void {
            };

            $this->firstTransactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $x) use ($expectedClosure): void {
                    $this->assertSame($expectedClosure, $x);
                    $x();
                });
            $this->secondTransactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $x) {
                    return $x();
                });

            $this->manager->run($expectedClosure);
        });
        $this->should('throw exception that Closure throws', function (): void {
            $this->firstTransactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $x) {
                    return $x();
                });
            $this->secondTransactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $x) {
                    return $x();
                });
            $this->assertThrows(RuntimeException::class, function (): void {
                $this->manager->run(function (): void {
                    throw new RuntimeException();
                });
            });
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_rollback(): void
    {
        $this->should('call all rollback method', function (): void {
            $this->firstTransactionManager
                ->expects('rollback')
                ->andReturnUsing(function (Closure $x) {
                    return $x();
                });
            $this->secondTransactionManager
                ->expects('rollback')
                ->andReturnUsing(function (Closure $x) {
                    return $x();
                });

            $this->manager->rollback(function (): void {
            });
        });
        $this->should('call closure by first manager', function (): void {
            $expectedClosure = function (): void {
            };

            $this->firstTransactionManager
                ->expects('rollback')
                ->andReturnUsing(function (Closure $x) use ($expectedClosure): void {
                    $this->assertSame($expectedClosure, $x);
                    $x();
                });
            $this->secondTransactionManager
                ->expects('rollback')
                ->andReturnUsing(function (Closure $x) {
                    return $x();
                });

            $this->manager->rollback($expectedClosure);
        });
        $this->should('throw exception that Closure throws', function (): void {
            $this->firstTransactionManager
                ->expects('rollback')
                ->andReturnUsing(function (Closure $x) {
                    return $x();
                });
            $this->secondTransactionManager
                ->expects('rollback')
                ->andReturnUsing(function (Closure $x) {
                    return $x();
                });
            $this->assertThrows(RuntimeException::class, function (): void {
                $this->manager->rollback(function (): void {
                    throw new RuntimeException();
                });
            });
        });
    }
}
