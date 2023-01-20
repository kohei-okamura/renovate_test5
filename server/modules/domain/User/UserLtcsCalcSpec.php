<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\User;

use Domain\Attributes\JsonIgnore;
use Domain\Common\Carbon;
use Domain\PoliteEntity;

/**
 * 介護保険サービス：利用者別算定情報
 */
final class UserLtcsCalcSpec extends PoliteEntity
{
    /**
     * {@link \Domain\User\UserLtcsCalcSpec} constructor.
     *
     * @param null|int $id 利用者別算定情報 ID
     * @param int $userId 利用者 ID
     * @param \Domain\Common\Carbon $effectivatedOn 適用日
     * @param \Domain\User\LtcsUserLocationAddition $locationAddition 地域加算
     * @param bool $isEnabled 有効フラグ
     * @param int $version バージョン
     * @param \Domain\Common\Carbon $createdAt 作成日時
     * @param \Domain\Common\Carbon $updatedAt 更新日時
     */
    public function __construct(
        ?int $id,
        public readonly int $userId,
        public readonly Carbon $effectivatedOn,
        public readonly LtcsUserLocationAddition $locationAddition,
        public readonly bool $isEnabled,
        #[JsonIgnore] public readonly int $version,
        public readonly Carbon $createdAt,
        public readonly Carbon $updatedAt
    ) {
        parent::__construct($id);
    }
}
