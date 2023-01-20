<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Concretes;

use App\Concretes\PermanentDatabaseTransactionManager;
use Illuminate\Database\DatabaseManager;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * DatabaseTransactionManager のテスト.
 */
class DatabaseTransactionManagerTest extends Test
{
    use MockeryMixin;
    use UnitSupport;

    /** @var \Illuminate\Database\DatabaseManager|\Mockery\MockInterface */
    private $db;

    private PermanentDatabaseTransactionManager $manager;
    private DatabaseManager $originalDb;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
//        static::beforeEachTest(function (DatabaseTransactionManagerTest $self): void {
//            $self->originalDb = app('db');
//            app()->bind('db', fn () => $self->db);
//        });
//        static::beforeEachSpec(function (DatabaseTransactionManagerTest $self): void {
//            $self->db = Mockery::mock(DatabaseManager::class);
//            $self->manager = app(DatabaseTransactionManager::class);
//        });
//        static::afterEachSpec(function (DatabaseTransactionManagerTest $self): void {
//            // DatabaseManager をモックに差し替えたままだとテストスペックの終了時に
//            // DatabaseManager::disconnect() を呼び出して異常終了してしまうため
//            // 本来の DatabaseManager を参照するように戻す
//            app()->instance('db', $self->originalDb);
//        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_run(): void
    {
        $this->should('run callback in transaction', function (): void {
//            $this->db->expects('transaction')->andReturnUsing(fn (Closure $callback) => $callback());
//            $f = Mockery::spy(fn () => 'RUN CALLBACK');
//            $g = fn () => call_user_func($f);
//
//            $actual = $this->manager->run($g);
//
//            $this->assertSame('RUN CALLBACK', $actual);
//            $f->shouldHaveBeenCalled();
        });
    }

//    /**
//     * @test
//     * @return void
//     */
//    public function describe_transaction(): void
//    {
//        $this->should('process normally with call callback', function (): void {
//            $this->db
//                ->expects('beginTransaction')
//                ->andReturnNull();
//            $this->db
//                ->expects('rollback')
//                ->andReturnNull();
//            $this->db
//                ->allows('disconnect');
//
//            $f = Mockery::spy(fn () => 'RUN CALLBACK');
//            $g = fn () => call_user_func($f);
//
//            $actual = $this->manager->rollback($g);
//
//            $this->assertSame('RUN CALLBACK', $actual);
//        });
//    }
}
