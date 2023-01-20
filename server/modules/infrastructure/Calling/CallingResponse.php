<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Calling;

use Domain\Calling\CallingResponse as DomainCallingResponse;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 出勤確認応答 Eloquent モデル.
 *
 * @property int $id 出勤確認応答ID
 * @property int $calling_id 出勤確認ID
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @method static \Illuminate\Database\Eloquent\Builder|CallingResponse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CallingResponse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CallingResponse query()
 * @method static \Illuminate\Database\Eloquent\Builder|CallingResponse whereCallingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CallingResponse whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CallingResponse whereId($value)
 * @mixin \Eloquent
 */
final class CallingResponse extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'calling_response';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'calling_id',
        'created_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /** {@inheritdoc} */
    public function toDomain(): DomainCallingResponse
    {
        return DomainCallingResponse::create($this->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Calling\CallingResponse $domain
     * @return \Infrastructure\Calling\CallingResponse
     */
    public static function fromDomain(DomainCallingResponse $domain): self
    {
        $keys = ['id', 'calling_id', 'created_at'];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }
}
