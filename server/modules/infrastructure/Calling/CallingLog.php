<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Calling;

use Domain\Calling\CallingLog as DomainCallingLog;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 出勤確認送信履歴 Eloquent モデル.
 *
 * @property int $id 出勤確認送信履歴ID
 * @property int $calling_id 出勤確認ID
 * @property \Domain\Calling\CallingType $calling_type 送信タイプ
 * @property bool $is_succeeded 送信成功フラグ
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @method static \Illuminate\Database\Eloquent\Builder|CallingLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CallingLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CallingLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|CallingLog whereCallingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CallingLog whereCallingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CallingLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CallingLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CallingLog whereIsSucceeded($value)
 * @mixin \Eloquent
 */
final class CallingLog extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'calling_log';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'calling_id',
        'calling_type',
        'is_succeeded',
        'created_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'is_succeeded' => 'boolean',
        'created_at' => 'datetime',
        'calling_type' => CastsCallingType::class,
    ];

    /** {@inheritdoc} */
    public function toDomain(): DomainCallingLog
    {
        return DomainCallingLog::create($this->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Calling\CallingLog $domain
     * @return \Infrastructure\Calling\CallingLog
     */
    public static function fromDomain(DomainCallingLog $domain): self
    {
        $keys = ['id', 'calling_id', 'calling_type', 'is_succeeded', 'created_at'];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }
}
