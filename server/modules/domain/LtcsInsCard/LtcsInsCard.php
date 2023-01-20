<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\LtcsInsCard;

use Domain\Entity;
use Domain\Versionable;
use JetBrains\PhpStorm\Pure;
use ScalikePHP\Option;

/**
 * 介護保険被保険者証.
 *
 * @property-read int $userId 利用者ID
 * @property-read \Domain\Common\Carbon $effectivatedOn 適用日
 * @property-read \Domain\LtcsInsCard\LtcsInsCardStatus $status 介護保険認定区分
 * @property-read string $insNumber 被保険者証番号
 * @property-read \Domain\Common\Carbon $issuedOn 交付日
 * @property-read string $insurerNumber 保険者番号
 * @property-read string $insurerName 保険者名
 * @property-read \Domain\LtcsInsCard\LtcsLevel $ltcsLevel 要介護度・要介護状態区分等
 * @property-read \Domain\Common\Carbon $certificatedOn 認定日
 * @property-read \Domain\Common\Carbon $activatedOn 認定の有効期間（開始）
 * @property-read \Domain\Common\Carbon $deactivatedOn 認定の有効期間（終了）
 * @property-read array|LtcsInsCardMaxBenefitQuota[] $maxBenefitQuotas 種類支給限度基準額
 * @property-read int $copayRate 利用者負担割合（原則）
 * @property-read \Domain\Common\Carbon $copayActivatedOn 利用者負担適用期間（開始）
 * @property-read \Domain\Common\Carbon $copayDeactivatedOn 利用者負担適用期間（終了）
 * @property-read string $careManagerName 居宅介護支援事業所：担当者
 * @property-read \Domain\LtcsInsCard\LtcsCarePlanAuthorType $carePlanAuthorType 居宅サービス計画作成区分
 * @property-read null|int $communityGeneralSupportCenterId 地域包括支援センター ID
 * @property-read null|int $carePlanAuthorOfficeId 居宅介護支援事業所 ID
 * @property-read int $isEnabled 有効フラグ
 * @property-read int $version バージョン
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class LtcsInsCard extends Entity
{
    use Versionable;

    /**
     * 月の途中で要介護度が変更されたかどうか判定する.
     *
     * @param \Domain\LtcsInsCard\LtcsInsCard[]&\ScalikePHP\Option $insCardAtFirstOfMonth
     * @param \Domain\LtcsInsCard\LtcsInsCard $insCardAtLastOfMonth
     * @return bool
     */
    public static function levelHasBeenChanged(Option $insCardAtFirstOfMonth, LtcsInsCard $insCardAtLastOfMonth): bool
    {
        return $insCardAtFirstOfMonth->exists(
            fn (LtcsInsCard $x): bool => $x->id !== $insCardAtLastOfMonth->id
                && $x->ltcsLevel !== $insCardAtLastOfMonth->ltcsLevel
        );
    }

    /**
     * 要介護度が指定した介護保険被保険者証より高いかどうか判定する.
     *
     * @param \Domain\LtcsInsCard\LtcsInsCard $that
     * @return bool
     */
    #[Pure]
    public function greaterThanForLevel(LtcsInsCard $that): bool
    {
        return $this->ltcsLevel->value() > $that->ltcsLevel->value();
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'userId',
            'effectivatedOn',
            'status',
            'insNumber',
            'issuedOn',
            'insurerNumber',
            'insurerName',
            'ltcsLevel',
            'certificatedOn',
            'activatedOn',
            'deactivatedOn',
            'maxBenefitQuotas',
            'copayRate',
            'copayActivatedOn',
            'copayDeactivatedOn',
            'careManagerName',
            'carePlanAuthorType',
            'communityGeneralSupportCenterId',
            'carePlanAuthorOfficeId',
            'isEnabled',
            'version',
            'createdAt',
            'updatedAt',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'userId' => true,
            'effectivatedOn' => true,
            'status' => true,
            'insNumber' => true,
            'issuedOn' => true,
            'insurerNumber' => true,
            'insurerName' => true,
            'ltcsLevel' => true,
            'certificatedOn' => true,
            'activatedOn' => true,
            'deactivatedOn' => true,
            'maxBenefitQuotas' => true,
            'copayRate' => true,
            'copayActivatedOn' => true,
            'copayDeactivatedOn' => true,
            'careManagerName' => true,
            'carePlanAuthorType' => true,
            'communityGeneralSupportCenterId' => true,
            'carePlanAuthorOfficeId' => true,
            'isEnabled' => false,
            'version' => false,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}
