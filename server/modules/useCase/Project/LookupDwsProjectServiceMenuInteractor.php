<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Project;

use Domain\Context\Context;
use Domain\Project\DwsProjectServiceMenuRepository;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：計画：サービス内容取得実装.
 */
final class LookupDwsProjectServiceMenuInteractor implements LookupDwsProjectServiceMenuUseCase
{
    private DwsProjectServiceMenuRepository $repository;

    /**
     * Constructor.
     *
     * @param \Domain\Project\DwsProjectServiceMenuRepository $repository
     */
    public function __construct(DwsProjectServiceMenuRepository $repository)
    {
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int ...$ids): Seq
    {
        return $this->repository->lookup(...$ids);
    }
}
