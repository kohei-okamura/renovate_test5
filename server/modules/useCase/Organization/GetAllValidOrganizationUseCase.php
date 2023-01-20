<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Organization;

use ScalikePHP\Seq;

/**
 * 有効な事業者取得ユースケース.
 */
interface GetAllValidOrganizationUseCase
{
    /**
     * 有効な事業者を取得する.
     * @return \Domain\Organization\Organization[]|\ScalikePHP\Seq
     */
    public function handle(): Seq;
}
