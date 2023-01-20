<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Exchange;

use Domain\Attributes\JsonIgnore;
use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatementInsurance;
use Domain\Billing\LtcsExpiredReason;
use Domain\Common\Carbon;
use Domain\Common\Sex;
use Domain\LtcsInsCard\LtcsCarePlanAuthorType;
use Domain\LtcsInsCard\LtcsLevel;
use Lib\Exceptions\RuntimeException;

/**
 * 介護保険サービス：伝送：データレコード：介護給付費明細書：基本情報.
 */
final class LtcsBillingStatementSummaryRecord extends LtcsBillingStatementRecord
{
    /** @var string 要介護状態区分等：事業対象者 */
    private const LTCS_LEVEL_CODE_TARGET = '06';

    /** @var string 要介護状態区分等：要支援1 */
    private const LTCS_LEVEL_CODE_SUPPORT_LEVEL1 = '12';

    /** @var string 要介護状態区分等：要支援2 */
    private const LTCS_LEVEL_CODE_SUPPORT_LEVEL2 = '13';

    /** @var string 要介護状態区分等：要介護1 */
    private const LTCS_LEVEL_CODE_CARE_LEVEL1 = '21';

    /** @var string 要介護状態区分等：要介護2 */
    private const LTCS_LEVEL_CODE_CARE_LEVEL2 = '22';

    /** @var string 要介護状態区分等：要介護3 */
    private const LTCS_LEVEL_CODE_CARE_LEVEL3 = '23';

    /** @var string 要介護状態区分等：要介護4 */
    private const LTCS_LEVEL_CODE_CARE_LEVEL4 = '24';

    /** @var string 要介護状態区分等：要介護5 */
    private const LTCS_LEVEL_CODE_CARE_LEVEL5 = '25';

    /**
     * {@link \Domain\Exchange\LtcsBillingStatementSummaryRecord} constructor.
     *
     * @param \Domain\Common\Carbon $providedIn サービス提供年月
     * @param string $officeCode 事業所番号
     * @param string $insurerNumber 証記載保険者番号
     * @param string $insNumber 被保険者番号
     * @param \Domain\Common\Carbon $birthday 被保険者情報：生年月日
     * @param \Domain\Common\Sex $sex 被保険者情報：性別コード
     * @param \Domain\LtcsInsCard\LtcsLevel $level 被保険者情報：要介護状態区分コード
     * @param \Domain\Common\Carbon $activatedOn 被保険者情報：認定有効期間：開始年月日
     * @param \Domain\Common\Carbon $deactivatedOn 被保険者情報：認定有効期間：終了年月日
     * @param \Domain\LtcsInsCard\LtcsCarePlanAuthorType $carePlanAuthorType 居宅サービス計画：居宅サービス計画作成区分コード
     * @param string $carePlanAuthorCode 居宅サービス計画：事業所番号（居宅介護支援事業所）
     * @param null|\Domain\Common\Carbon $agreedOn 開始年月日
     * @param null|\Domain\Common\Carbon $expiredOn 中止年月日
     * @param \Domain\Billing\LtcsExpiredReason $expiredReason 中止理由・入所（院）前の状況コード
     * @param \Domain\Billing\LtcsBillingStatementInsurance $insurance 保険請求内容
     * @param \Domain\Billing\LtcsBillingStatementSubsidy[] $subsidies 公費請求内容
     */
    public function __construct(
        Carbon $providedIn,
        string $officeCode,
        string $insurerNumber,
        string $insNumber,
        #[JsonIgnore] public readonly Carbon $birthday,
        #[JsonIgnore] public readonly Sex $sex,
        #[JsonIgnore] public readonly LtcsLevel $level,
        #[JsonIgnore] public readonly Carbon $activatedOn,
        #[JsonIgnore] public readonly Carbon $deactivatedOn,
        #[JsonIgnore] public readonly LtcsCarePlanAuthorType $carePlanAuthorType,
        #[JsonIgnore] public readonly string $carePlanAuthorCode,
        #[JsonIgnore] public readonly ?Carbon $agreedOn,
        #[JsonIgnore] public readonly ?Carbon $expiredOn,
        #[JsonIgnore] public readonly LtcsExpiredReason $expiredReason,
        #[JsonIgnore] public readonly LtcsBillingStatementInsurance $insurance,
        #[JsonIgnore] public readonly array $subsidies
    ) {
        parent::__construct(
            recordFormat: self::RECORD_FORMAT_SUMMARY,
            providedIn: $providedIn,
            officeCode: $officeCode,
            insurerNumber: $insurerNumber,
            insNumber: $insNumber
        );
    }

    /**
     * インスタンスを生成する.
     *
     * @param \Domain\Billing\LtcsBilling $billing
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @param \Domain\Billing\LtcsBillingStatement $statement
     * @return self
     */
    public static function from(LtcsBilling $billing, LtcsBillingBundle $bundle, LtcsBillingStatement $statement): self
    {
        return new self(
            providedIn: $bundle->providedIn,
            officeCode: $billing->office->code,
            insurerNumber: $statement->insurerNumber,
            insNumber: $statement->user->insNumber,
            birthday: $statement->user->birthday,
            sex: $statement->user->sex,
            level: $statement->user->ltcsLevel,
            activatedOn: $statement->user->activatedOn,
            deactivatedOn: $statement->user->deactivatedOn,
            carePlanAuthorType: $statement->carePlanAuthor->authorType,
            carePlanAuthorCode: $statement->carePlanAuthor->code,
            agreedOn: $statement->agreedOn,
            expiredOn: $statement->expiredOn,
            expiredReason: $statement->expiredReason,
            insurance: $statement->insurance,
            subsidies: $statement->subsidies,
        );
    }

    /** {@inheritdoc} */
    public function toArray(int $recordNumber): array
    {
        assert(count($this->subsidies) === 3);
        return [
            ...parent::toArray($recordNumber),
            // 公費1：負担者番号
            $this->subsidies[0]->defrayerNumber,
            // 公費1：受給者番号
            $this->subsidies[0]->recipientNumber,
            // 公費2：負担者番号
            $this->subsidies[1]->defrayerNumber,
            // 公費2：受給者番号
            $this->subsidies[1]->recipientNumber,
            // 公費3：負担者番号
            $this->subsidies[2]->defrayerNumber,
            // 公費3：受給者番号
            $this->subsidies[2]->recipientNumber,
            // 被保険者情報：生年月日
            self::formatDate($this->birthday),
            // 被保険者情報：性別コード
            $this->sex->value(),
            // 被保険者情報：要介護状態区分コード
            self::getLevelCode($this->level),
            // 被保険者情報：旧措置入所者特例コード
            self::UNUSED,
            // 被保険者情報：認定有効期間：開始年月日
            self::formatDate($this->activatedOn),
            // 被保険者情報：認定有効期間：終了年月日
            self::formatDate($this->deactivatedOn),
            // 居宅サービス計画：居宅サービス計画作成区分コード
            $this->carePlanAuthorType->value(),
            // 居宅サービス計画：事業所番号（居宅介護支援事業所）
            $this->carePlanAuthorCode,
            // 開始年月日
            self::formatDate($this->agreedOn),
            // 中止年月日
            self::formatDate($this->expiredOn),
            // 中止理由・入所（院）前の状況コード
            $this->expiredReason === LtcsExpiredReason::unspecified() ? '' : $this->expiredReason->value(),
            // 入所（院）年月日
            self::UNUSED,
            // 退所（院）年月日
            self::UNUSED,
            // 入所（院）実日数
            self::UNUSED,
            // 外泊日数
            self::UNUSED,
            // 退所（院）後の状況コード
            self::UNUSED,
            // 保険給付率
            $this->insurance->benefitRate,
            // 公費1給付率
            $this->subsidies[0]->benefitRate ?? '',
            // 公費2給付率
            $this->subsidies[1]->benefitRate ?? '',
            // 公費3給付率
            $this->subsidies[2]->benefitRate ?? '',
            // 合計情報：保険：サービス単位数
            $this->insurance->totalScore,
            // 合計情報：保険：請求額
            $this->insurance->claimAmount,
            // 合計情報：保険：利用者負担額
            $this->insurance->copayAmount,
            // 合計情報：保険：緊急時施設療養費請求額
            self::UNUSED,
            // 合計情報：保険：特定診療費請求額
            self::UNUSED,
            // 合計情報：保険：特定入所者介護サービス費等請求額
            self::UNUSED,
            // 合計情報：公費1：サービス単位数
            $this->subsidies[0]->totalScore,
            // 合計情報：公費1：請求額
            $this->subsidies[0]->claimAmount,
            // 合計情報：公費1：利用者負担額
            $this->subsidies[0]->copayAmount,
            // 合計情報：公費1：緊急時施設療養費請求額
            self::UNUSED,
            // 合計情報：公費1：特定診療費請求額
            self::UNUSED,
            // 合計情報：公費1：特定入所者介護サービス費等請求額
            self::UNUSED,
            // 合計情報：公費2：サービス単位数
            $this->subsidies[1]->totalScore,
            // 合計情報：公費2：請求額
            $this->subsidies[1]->claimAmount,
            // 合計情報：公費2：利用者負担額
            $this->subsidies[1]->copayAmount,
            // 合計情報：公費2：緊急時施設療養費請求額
            self::UNUSED,
            // 合計情報：公費2：特定診療費請求額
            self::UNUSED,
            // 合計情報：公費2：特定入所者介護サービス費等請求額
            self::UNUSED,
            // 合計情報：公費3：サービス単位数
            $this->subsidies[2]->totalScore,
            // 合計情報：公費3：請求額
            $this->subsidies[2]->claimAmount,
            // 合計情報：公費3：利用者負担額
            $this->subsidies[2]->copayAmount,
            // 合計情報：公費3：緊急時施設療養費請求額
            self::UNUSED,
            // 合計情報：公費3：特定診療費請求額
            self::UNUSED,
            // 合計情報：公費3：特定入所者介護サービス費等請求額
            self::UNUSED,
        ];
    }

    /**
     * 要介護状態区分コードを取得する.
     *
     * @param \Domain\LtcsInsCard\LtcsLevel $level
     * @return string
     */
    private static function getLevelCode(LtcsLevel $level): string
    {
        return match ($level) {
            LtcsLevel::target() => self::LTCS_LEVEL_CODE_TARGET,
            LtcsLevel::supportLevel1() => self::LTCS_LEVEL_CODE_SUPPORT_LEVEL1,
            LtcsLevel::supportLevel2() => self::LTCS_LEVEL_CODE_SUPPORT_LEVEL2,
            LtcsLevel::careLevel1() => self::LTCS_LEVEL_CODE_CARE_LEVEL1,
            LtcsLevel::careLevel2() => self::LTCS_LEVEL_CODE_CARE_LEVEL2,
            LtcsLevel::careLevel3() => self::LTCS_LEVEL_CODE_CARE_LEVEL3,
            LtcsLevel::careLevel4() => self::LTCS_LEVEL_CODE_CARE_LEVEL4,
            LtcsLevel::careLevel5() => self::LTCS_LEVEL_CODE_CARE_LEVEL5,
            default => throw new RuntimeException("Invalid LtcsLevel given: {$level}") // @codeCoverageIgnore
        };
    }
}
