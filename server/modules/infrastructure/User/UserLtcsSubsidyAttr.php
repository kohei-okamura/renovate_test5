<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\User;

use Domain\User\UserLtcsSubsidy as DomainUserLtcsSubsidy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Infrastructure\Common\PeriodHolder;
use Infrastructure\Model;

/**
 * 公費情報属性 Eloquent モデル.
 *
 * @property int $id 公費情報属性ID
 * @property int $user_ltcs_subsidy_id 公費情報ID
 * @property string $defrayer_number 負担者番号
 * @property string $recipient_number 受給者番号
 * @property int $benefit_rate 給付率
 * @property int $copay 本人負担額
 * @property bool $is_enabled 有効フラグ
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @property-read \Infrastructure\User\UserLtcsSubsidy $userLtcsSubsidy 公費情報
 * @method static \Illuminate\Database\Eloquent\Builder|static whereUserLtcsSubsidyId($value)
 * @mixin \Eloquent
 */
final class UserLtcsSubsidyAttr extends Model
{
    use PeriodHolder;

    /**
     * テーブル名
     */
    public const TABLE = 'user_ltcs_subsidy_attr';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'user_ltcs_subsidy_id',
        'period',
        'defrayer_category',
        'defrayer_number',
        'recipient_number',
        'benefit_rate',
        'copay',
        'is_enabled',
        'version',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'is_enabled' => 'boolean',
        'period_start' => 'date',
        'period_end' => 'date',
        'updated_at' => 'datetime',
        'defrayer_category' => CastsDefrayerCategory::class,
    ];

    /**
     * BelongsTo: {@link \Infrastructure\User\UserLtcsSubsidy}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @codeCoverageIgnore リレーションの定義のため
     */
    public function userLtcsSubsidy(): BelongsTo
    {
        return $this->belongsTo(UserLtcsSubsidy::class);
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\User\UserLtcsSubsidy $domain
     * @return \Infrastructure\User\UserLtcsSubsidyAttr
     */
    public static function fromDomain(DomainUserLtcsSubsidy $domain): self
    {
        $keys = [
            'period',
            'defrayer_category',
            'defrayer_number',
            'recipient_number',
            'benefit_rate',
            'copay',
            'is_enabled',
            'version',
            'updated_at',
        ];
        $attrs = self::getDomainValues($domain, $keys);
        return self::newModelInstance($attrs);
    }

    /** {@inheritdoc} */
    public function toDomainValues(): array
    {
        $hasGetMutatorAttrs = [
            'period',
        ];
        return $this->only($hasGetMutatorAttrs) + parent::toDomainValues();
    }
}
