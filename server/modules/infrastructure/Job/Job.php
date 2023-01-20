<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Job;

use Domain\Job\Job as DomainJob;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\Organization\BelongsToOrganization;

/**
 * ジョブ Eloquent モデル.
 *
 * @property int $id
 * @property array $data データ
 * @property int $organization_id 事業者ID
 * @property int $staff_id スタッフID
 * @property \Domain\Job\JobStatus $status ジョブ状態
 * @property string $token トークン
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @method static \Illuminate\Database\Eloquent\Builder|\Infrastructure\Job\Job newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Infrastructure\Job\Job newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Infrastructure\Job\Job query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Infrastructure\Job\Job whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Infrastructure\Job\Job whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Infrastructure\Job\Job whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Infrastructure\Job\Job whereJobStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Infrastructure\Job\Job whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Infrastructure\Job\Job whereUpdatedAt($value)
 * @mixin \Eloquent
 */
final class Job extends Model implements Domainable
{
    use BelongsToOrganization;

    /**
     * テーブル名.
     */
    public const TABLE = 'job';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'organization_id',
        'staff_id',
        'data',
        'status',
        'token',
        'created_at',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'data' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'status' => CastsJobStatus::class,
    ];

    /** {@inheritdoc} */
    public function toDomain(): DomainJob
    {
        return DomainJob::create($this->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Job\Job $domain
     * @return \Infrastructure\Job\Job
     */
    public static function fromDomain(DomainJob $domain): self
    {
        $keys = [
            'id',
            'organization_id',
            'staff_id',
            'data',
            'status',
            'token',
            'created_at',
            'updated_at',
        ];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }
}
