<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Project;

use Domain\Context\Context;
use Domain\FinderResult;

/**
 * 介護保険サービス：計画：サービス内容一覧取得ユースケース.
 */
interface GetLtcsProjectServiceMenuListUseCase
{
    /**
     * 介護保険サービス：計画：サービス内容を一覧取得する.
     *
     * @param \Domain\Context\Context $context
     * @param bool $all
     * @return \Domain\FinderResult
     */
    public function handle(Context $context, bool $all): FinderResult;
}
