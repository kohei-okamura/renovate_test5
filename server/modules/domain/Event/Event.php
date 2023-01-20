<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Event;

use Domain\Context\Context;
use Domain\Context\ContextProvider;
use Lib\Exceptions\LogicException;

/**
 * イベント.
 */
abstract class Event implements ContextProvider
{
    private Context $context;

    /**
     * {@\Domain\Event\Event} constructor.
     *
     * @param \Domain\Context\Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /** {@inheritdoc} */
    public function context(): Context
    {
        return $this->context;
    }

    /**
     * @codeCoverageIgnore 実装誤り検出目的のメソッドのため
     * @param mixed $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        throw new LogicException('Event should immutable');
    }
}
