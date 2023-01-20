<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Attributes\JsonIgnore;
use Domain\Common\Carbon;
use Domain\Common\MimeType;
use Domain\Polite;

/**
 * 介護保険サービス：請求：ファイル
 */
final class LtcsBillingFile extends Polite
{
    /**
     * {@link \Domain\Billing\LtcsBillingFile} constructor.
     *
     * @param string $name ファイル名
     * @param string $path パス
     * @param string $token トークン
     * @param \Domain\Common\MimeType $mimeType MimeType
     * @param \Domain\Common\Carbon $createdAt 作成日時
     * @param null|\Domain\Common\Carbon $downloadedAt 最終ダウンロード日時
     */
    public function __construct(
        public readonly string $name,
        #[JsonIgnore] public readonly string $path,
        public readonly string $token,
        public readonly MimeType $mimeType,
        public readonly Carbon $createdAt,
        public readonly ?Carbon $downloadedAt
    ) {
    }
}
