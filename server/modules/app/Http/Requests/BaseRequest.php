<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Context\Context;
use Domain\Context\ContextProvider;
use Laravel\Lumen\Http\Request as LumenRequest;
use Lib\Exceptions\LogicException;

/**
 * リクエスト基底クラス.
 */
abstract class BaseRequest extends LumenRequest implements ContextProvider
{
    private ?Context $context = null;

    /**
     * {@inheritdoc}
     *
     * このメソッドを final にするとテストが通らなくなるので注意.
     */
    public function context(): Context
    {
        if ($this->context === null) {
            $this->context = $this->createContext();
        }
        return $this->context;
    }

    /** {@inheritdoc} */
    final public function toArray()
    {
        throw new LogicException('Do not call Request::toArray(), use Request::toAssoc() instead');
    }

    /**
     * リクエスト内容を連想配列として取得する.
     *
     * @return array
     */
    final public function toAssoc(): array
    {
        return parent::toArray();
    }

    /**
     * コンテキストを生成する.
     *
     * @return \Domain\Context\Context
     */
    abstract protected function createContext(): Context;
}
