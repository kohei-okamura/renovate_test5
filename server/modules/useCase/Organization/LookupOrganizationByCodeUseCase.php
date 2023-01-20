<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Organization;

use ScalikePHP\Option;

/**
 * コードから事業者取得ユースケース.
 */
interface LookupOrganizationByCodeUseCase
{
    /**
     * コードを指定して事業者情報を取得する.
     *
     * @param string $code
     * @return \Domain\Organization\Organization|\ScalikePHP\Option
     */
    public function handle(string $code): Option;
}
