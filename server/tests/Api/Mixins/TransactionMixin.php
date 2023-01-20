<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Mixins;

use ApiTester;

/**
 * APIテスト用のトランザクションMixin
 */
trait TransactionMixin
{
    /**
     * トランザクションを開始する
     *
     * @param \ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     * @noinspection PhpUnused
     */
    public function _beforeMixinTransaction(ApiTester $I)
    {
        $I->haveTransaction();
    }

    /**
     * トランザクションを終了する
     *
     * @param \ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     * @noinspection PhpUnused
     */
    public function _afterMixinTransaction(ApiTester $I)
    {
        $I->cleanUpTransaction();
    }
}
