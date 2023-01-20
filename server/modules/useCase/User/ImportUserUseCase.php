<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Context\Context;

/**
 * 利用者 CSV 一括インポートユースケース.
 */
interface ImportUserUseCase
{
    /**
     * CSV ファイルから事業所を一括でインポート（登録）する.
     *
     * @param \Domain\Context\Context $context
     * @param string $filepath
     * @throws \Throwable
     * @return void
     */
    public function handle(Context $context, string $filepath): void;
}
