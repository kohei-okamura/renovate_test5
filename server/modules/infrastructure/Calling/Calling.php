<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Calling;

use Domain\Calling\Calling as DomainCalling;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\Shift\Shift;

/**
 * 出勤確認 Eloquent モデル.
 *
 * @property int $id 出勤確認ID
 * @property int $staff_id スタッフID
 * @property string $token トークン
 * @property \Domain\Common\Carbon $expired_at 有効期限
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\Shift\Shift[] $shifts
 * @property-read null|int $shifts_count
 * @method static \Illuminate\Database\Eloquent\Builder|Calling newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Calling newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Calling query()
 * @method static \Illuminate\Database\Eloquent\Builder|Calling whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Calling whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Calling whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Calling whereStaffId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Calling whereToken($value)
 * @mixin \Eloquent
 */
final class Calling extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'calling';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'staff_id',
        'token',
        'expired_at',
        'created_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'expired_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * BelongsToMany: {@link \Infrastructure\Shift\Shift}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function shifts(): BelongsToMany
    {
        return $this->belongsToMany(Shift::class, 'calling_to_shift');
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainCalling
    {
        $shiftIds = $this->shifts()->allRelatedIds()->toArray();
        return DomainCalling::create($this->toDomainValues() + compact('shiftIds'));
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Calling\Calling $domain
     * @return \Infrastructure\Calling\Calling
     */
    public static function fromDomain(DomainCalling $domain): self
    {
        $keys = ['id', 'staff_id', 'token', 'expired_at', 'created_at'];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }
}
