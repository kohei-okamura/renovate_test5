<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Concretes;

use Closure;
use Domain\TransactionManager;
use ScalikePHP\Seq;

/**
 * Composed Transaction Manager.
 */
final class ComposedTransactionManager implements TransactionManager
{
    /**
     * @var \Domain\TransactionManager[]|\ScalikePHP\Seq
     */
    private $managers;

    /**
     * Constructor.
     *
     * @param \Domain\TransactionManager[]|\ScalikePHP\Seq $managers
     */
    public function __construct(Seq $managers)
    {
        $this->managers = $managers;
    }

    /**
     * Compose two TransactionManagers.
     *
     * @param \Domain\TransactionManager $a
     * @param \Domain\TransactionManager $b
     * @return \Domain\TransactionManager
     */
    public static function compose(TransactionManager $a, TransactionManager $b): TransactionManager
    {
        $managers = Seq::from($a, $b)
            ->flatMap(fn (TransactionManager $x) => $x instanceof self ? $x->managers : [$x])
            ->distinctBy(fn (TransactionManager $x) => get_class($x));
        return new static($managers);
    }

    /** {@inheritdoc} */
    public function run(Closure $f)
    {
        $composed = $this->managers->fold($f, fn (Closure $z, TransactionManager $x) => fn () => $x->run($z));
        return $composed();
    }

    /** {@inheritdoc} */
    public function rollback(Closure $f)
    {
        $composed = $this->managers->fold($f, fn (Closure $z, TransactionManager $x) => fn () => $x->rollback($z));
        return $composed();
    }
}
