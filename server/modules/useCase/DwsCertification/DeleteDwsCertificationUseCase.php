<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\DwsCertification;

use Domain\Context\Context;

/**
 * 障害福祉サービス受給者証削除ユースケース.
 */
interface DeleteDwsCertificationUseCase
{
    /**
     * 障害福祉サービス受給者証を削除する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @param int $id
     * @return void
     */
    public function handle(Context $context, int $userId, int $id): void;
}
