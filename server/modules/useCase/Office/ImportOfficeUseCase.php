<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;

/**
 * 事業所 CSV 一括インポートユースケース.
 */
interface ImportOfficeUseCase
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
