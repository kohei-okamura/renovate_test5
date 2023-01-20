<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Concerns;

use Lib\Exceptions\RuntimeException;
use ScalikePHP\Seq;
use UseCase\Contracts\TokenMaker;

/**
 * ユニークなトークンの生成をサポートする.
 */
trait UniqueTokenSupport
{
    protected TokenMaker $tokenMaker;

    /**
     * ユニークなトークンを生成する.
     *
     * @param int $length
     * @param int $maxRetryCount
     * @return string
     */
    public function createUniqueToken(int $length, int $maxRetryCount): string
    {
        $g = call_user_func(function () use ($length) {
            while (true) {
                yield $this->tokenMaker->make($length);
            }
        });
        return Seq::fromArray($g)
            ->take($maxRetryCount)
            ->find(fn ($token) => $this->isUnique($token))
            ->getOrElse(function (): void {
                throw new RuntimeException('Failed to create unique token');
            });
    }

    /**
     * トークンがユニークかを検査する.
     *
     * @param string $token
     * @return bool
     */
    abstract protected function isUnique(string $token): bool;
}
