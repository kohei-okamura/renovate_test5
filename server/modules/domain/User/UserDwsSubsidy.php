<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\User;

use Domain\Common\Rounding;
use Domain\Entity;
use Domain\Versionable;
use Lib\Exceptions\LogicException;
use Lib\Math;

/**
 * 利用者：自治体助成情報.
 *
 * @property-read int $id 自治体助成情報ID
 * @property-read int $userId 利用者ID
 * @property-read \Domain\Common\CarbonRange $period 適用期間
 * @property-read string $cityName 助成自治体名
 * @property-read string $cityCode 助成自治体番号
 * @property-read \Domain\User\UserDwsSubsidyType $subsidyType 給付方式
 * @property-read \Domain\User\UserDwsSubsidyFactor $factor 基準値種別
 * @property-read int $benefitRate 給付率(%)
 * @property-read int $copayRate 本人負担率(%)
 * @property-read \Domain\Common\Rounding $rounding 端数処理区分
 * @property-read int $benefitAmount 給付額(円)
 * @property-read int $copayAmount 本人負担額(円)
 * @property-read string $note 備考
 * @property-read bool $isEnabled 有効フラグ
 * @property-read int $version
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class UserDwsSubsidy extends Entity
{
    use Versionable;

    /**
     * 自治体助成額を算出する.
     *
     * @param int $copay 決定利用者負担額
     * @param int $fee 総費用額
     * @return int
     */
    public function compute(int $copay, int $fee): int
    {
        return match ($this->subsidyType) {
            // 定率給付
            // ・基準値
            //   - 決定利用者負担額
            //   - 総費用額
            // ・端数処理
            //   - 切り捨て
            //   - 切り上げ
            //   - 四捨五入
            // 自治体ごとにルールが違うので上記を選択して下記の式で算出する.
            // ・《自治体助成額》 = 《基準値》[円] × 《給付率》[%]（端数処理）
            UserDwsSubsidyType::benefitRate() => $this->factor === UserDwsSubsidyFactor::fee()
                ? min($this->calcBenefitRate($fee), $copay)
                : $this->calcBenefitRate($copay),
            // 定率負担
            // ・基準値（以下から選択）
            //   - 決定利用者負担額
            //   - 総費用額
            // ・ 端数処理（以下から選択）
            //   - 切り捨て
            //   - 切り上げ
            //   - 四捨五入
            // 自治体ごとにルールが違うので上記を選択して下記の式で算出する.
            // ・自治体助成額 = 《決定利用者負担額》[円] - 《基準値》[円] × 《負担率》[%]（端数処理）
            UserDwsSubsidyType::copayRate() => $this->factor === UserDwsSubsidyFactor::fee()
                ? $this->calcCopayRate($copay, $fee)
                : $this->calcCopayRate($copay, $copay),
            // 定額給付
            UserDwsSubsidyType::benefitAmount() => min($copay, $this->benefitAmount),
            // 定額負担
            UserDwsSubsidyType::copayAmount() => $copay < $this->copayAmount ? 0 : $copay - $this->copayAmount,
            // @codeCoverageIgnoreStart
            // 到達不能コード（給付方式の区分値が増えない限り）
            default => throw new LogicException("Unexpected subsidyType: {$this->subsidyType}"),
            // @codeCoverageIgnoreEnd
        };
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'userId',
            'period',
            'cityName',
            'cityCode',
            'subsidyType',
            'factor',
            'benefitRate',
            'copayRate',
            'rounding',
            'benefitAmount',
            'copayAmount',
            'note',
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
            'period' => true,
            'cityName' => true,
            'cityCode' => true,
            'subsidyType' => true,
            'factor' => true,
            'benefitRate' => true,
            'copayRate' => true,
            'rounding' => true,
            'benefitAmount' => true,
            'copayAmount' => true,
            'note' => true,
            'isEnabled' => true,
            'version' => true,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }

    /**
     * 基準額から定率給付時の自治体助成額を計算する.
     *
     * @param int $amount 基準額
     * @return int
     */
    private function calcBenefitRate(int $amount): int
    {
        return match ($this->rounding) {
            Rounding::none(),
            Rounding::ceil() => Math::ceil($amount * $this->benefitRate / 100),
            Rounding::floor() => Math::floor($amount * $this->benefitRate / 100),
            Rounding::round() => Math::round($amount * $this->benefitRate / 100),
        };
    }

    /**
     * 基準額から定率負担時の自治体助成額を計算する.
     *
     * @param int $copay 決定利用者負担額
     * @param int $amount 基準額
     * @return int
     */
    private function calcCopayRate(int $copay, int $amount): int
    {
        $result = match ($this->rounding) {
            Rounding::none(),
            Rounding::ceil() => $copay - Math::ceil($amount * $this->copayRate / 100),
            Rounding::floor() => $copay - Math::floor($amount * $this->copayRate / 100),
            Rounding::round() => $copay - Math::round($amount * $this->copayRate / 100),
        };

        return max($result, 0);
    }
}
