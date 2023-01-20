<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Project;

use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Project\LtcsProjectServiceMenuFinder;

/**
 * 介護保険サービス：計画：サービス内容一覧取得ユースケース実装.
 */
class GetLtcsProjectServiceMenuListInteractor implements GetLtcsProjectServiceMenuListUseCase
{
    private LtcsProjectServiceMenuFinder $finder;

    /**
     * Constructor.
     *
     * @param \Domain\Project\LtcsProjectServiceMenuFinder $finder
     */
    public function __construct(LtcsProjectServiceMenuFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, bool $all): FinderResult
    {
        return $this->finder->find([], ['all' => $all, 'sortBy' => 'id']);
    }
}
