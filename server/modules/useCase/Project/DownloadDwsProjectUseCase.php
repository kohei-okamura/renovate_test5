<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Project;

use Domain\Context\Context;

/**
 * 障害福祉サービス：計画ダウンロードユースケース.
 */
interface DownloadDwsProjectUseCase
{
    /**
     * 障害福祉サービス：計画をダウンロードする.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @param int $id
     * @return array
     */
    public function handle(Context $context, int $userId, int $id): array;
}
