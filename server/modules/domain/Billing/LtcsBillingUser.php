<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Common\Carbon;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\LtcsInsCard\LtcsLevel;
use Domain\Polite;
use Domain\User\User;

/**
 * 介護保険サービス：請求：利用者.
 */
final class LtcsBillingUser extends Polite
{
    /**
     * {@link \Domain\Billing\LtcsBillingUser} constructor.
     *
     * @param int $userId 利用者 ID
     * @param int $ltcsInsCardId 介護保険被保険者証 ID
     * @param string $insNumber 被保険者証番号
     * @param \Domain\Common\StructuredName $name 氏名
     * @param \Domain\Common\Sex $sex 性別
     * @param \Domain\Common\Carbon $birthday 生年月日
     * @param \Domain\LtcsInsCard\LtcsLevel $ltcsLevel 要介護状態区分
     * @param \Domain\Common\Carbon $activatedOn 認定の有効期間（開始）
     * @param \Domain\Common\Carbon $deactivatedOn 認定の有効期間（終了）
     */
    public function __construct(
        public readonly int $userId,
        public readonly int $ltcsInsCardId,
        public readonly string $insNumber,
        public readonly StructuredName $name,
        public readonly Sex $sex,
        public readonly Carbon $birthday,
        public readonly LtcsLevel $ltcsLevel,
        public readonly Carbon $activatedOn,
        public readonly Carbon $deactivatedOn
    ) {
    }

    /**
     * 利用者モデル＆介護保険被保険者証モデルからインスタンスを生成する.
     *
     * @param \Domain\User\User $user
     * @param \Domain\LtcsInsCard\LtcsInsCard $insCard
     * @return static
     */
    public static function from(User $user, LtcsInsCard $insCard): self
    {
        return new self(
            userId: $user->id,
            ltcsInsCardId: $insCard->id,
            insNumber: $insCard->insNumber,
            name: $user->name,
            sex: $user->sex,
            birthday: $user->birthday,
            ltcsLevel: $insCard->ltcsLevel,
            activatedOn: $insCard->activatedOn,
            deactivatedOn: $insCard->deactivatedOn,
        );
    }
}
