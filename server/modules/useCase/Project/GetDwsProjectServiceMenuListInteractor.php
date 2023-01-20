<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Project;

use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Project\DwsProjectServiceMenuFinder;

/**
 * 障害福祉サービス：計画：サービス内容一覧取得ユースケース実装.
 */
class GetDwsProjectServiceMenuListInteractor implements GetDwsProjectServiceMenuListUseCase
{
    private DwsProjectServiceMenuFinder $finder;

    /**
     * Constructor.
     *
     * @param \Domain\Project\DwsProjectServiceMenuFinder $finder
     */
    public function __construct(DwsProjectServiceMenuFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, bool $all): FinderResult
    {
        return $this->finder->find([], ['all' => $all, 'sortBy' => 'id']);
    }
}
