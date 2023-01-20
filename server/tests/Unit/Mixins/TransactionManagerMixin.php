<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Closure;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Mockery;

/**
 * TransactionManager Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait TransactionManagerMixin
{
    /**
     * @var \Domain\TransactionManager|\Mockery\MockInterface
     */
    protected $transactionManager;

    /**
     * @var \Domain\TransactionManagerFactory|\Mockery\MockInterface
     */
    protected $transactionManagerFactory;

    /**
     * Mixin Transaction Manager.
     *
     * @return void
     */
    public static function mixinTransactionManager(): void
    {
        static::beforeEachSpec(function ($self): void {
            app()->bind(TransactionManagerFactory::class, fn () => $self->transactionManagerFactory);
        });
        static::beforeEachSpec(function ($self): void {
            $self->transactionManager = Mockery::mock(TransactionManager::class);
            $self->transactionManager
                ->allows('run')
                ->andReturnUsing(fn (Closure $f) => $f())
                ->byDefault();

            $self->transactionManagerFactory = Mockery::mock(TransactionManagerFactory::class);
            $self->transactionManagerFactory
                ->allows('factory')
                ->andReturn($self->transactionManager)
                ->byDefault();
        });
    }
}
