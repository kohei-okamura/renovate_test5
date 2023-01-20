<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Context\Context;
use Domain\UserBilling\ZenginRecord;
use SplFileInfo;

/**
 * 全銀フォーマットファイルパースユースケース実装.
 */
class ParseZenginFormatInteractor implements ParseZenginFormatUseCase
{
    /** {@inheritdoc} */
    public function handle(Context $context, SplFileInfo $file): ZenginRecord
    {
        $content = $this->getFileContent($file);
        return ZenginRecord::parse($content);
    }

    /**
     * 全銀ファイルを読み込む.
     *
     * @param \SplFileInfo $file
     * @return iterable|string[]
     */
    private function getFileContent(SplFileInfo $file): string
    {
        return file_get_contents($file->getPathname());
    }
}
