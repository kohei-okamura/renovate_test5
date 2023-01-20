<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\User;

use Domain\User\UserDwsSubsidy as DomainUserDwsSubsidy;
use Domain\User\UserDwsSubsidyType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Infrastructure\Common\CastsRounding;
use Infrastructure\Common\PeriodHolder;
use Infrastructure\Model;

/**
 * 自治体助成情報属性 Eloquent モデル.
 *
 * @property int $id 自治体助成情報属性ID
 * @property int $user_dws_subsidy_id 自治体助成情報ID
 * @property \Domain\Common\Carbon $period 適用期間
 * @property string $city_name 助成自治体名
 * @property string $city_code 助成自治体番号
 * @property-read  \Domain\User\UserDwsSubsidyType $dws_subsidy_type 給付方式
 * @property-read \Domain\User\UserDwsSubsidyFactor $factor 基準値種別
 * @property int $benefit_rate 給付率
 * @property int $copay_rate 給付率
 * @property-read \Domain\Common\Rounding $rounding 端数処理区分
 * @property int $benefit_amount 給付額
 * @property int $copay_amount 本人負担額
 * @property string $note 備考
 * @property bool $is_enabled 有効フラグ
 * @property int $version バージョン
 * @property \Domain\Common\Carbon $updated_at 更新日時
 */
final class UserDwsSubsidyAttr extends Model
{
    use PeriodHolder;

    /**
     * テーブル名
     */
    public const TABLE = 'user_dws_subsidy_attr';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'user_dws_subsidy_id',
        'period',
        'city_name',
        'city_code',
        'dws_subsidy_type',
        'factor',
        'benefit_rate',
        'copay_rate',
        'rounding',
        'benefit_amount',
        'copay_amount',
        'note',
        'is_enabled',
        'version',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'factor' => CastsUserDwsSubsidyFactor::class,
        'rounding' => CastsRounding::class,
        'is_enabled' => 'boolean',
        'updated_at' => 'datetime',
    ];

    /**
     * BelongsTo: {@link \Infrastructure\User\UserDwsSubsidy}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @codeCoverageIgnore リレーションの定義のため
     */
    public function subsidy(): BelongsTo
    {
        return $this->belongsTo(UserDwsSubsidy::class);
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\User\UserDwsSubsidy $domain
     * @return \Infrastructure\User\UserDwsSubsidyAttr
     */
    public static function fromDomain(DomainUserDwsSubsidy $domain): self
    {
        $subsidyType = ['dws_subsidy_type' => $domain->subsidyType];
        $keys = [
            'period',
            'city_name',
            'city_code',
            'factor',
            'benefit_rate',
            'copay_rate',
            'rounding',
            'benefit_amount',
            'copay_amount',
            'note',
            'is_enabled',
            'version',
            'updated_at',
        ];
        $attrs = self::getDomainValues($domain, $keys) + $subsidyType;
        return self::newModelInstance($attrs);
    }

    /** {@inheritdoc} */
    public function toDomainValues(): array
    {
        $subsidyType = ['subsidyType' => UserDwsSubsidyType::from($this->attributes['dws_subsidy_type'])];
        $hasGetMutatorAttrs = [
            'period',
        ];
        return $subsidyType + $this->only($hasGetMutatorAttrs) + parent::toDomainValues();
    }
}
