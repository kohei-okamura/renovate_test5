<?php
/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Project;

use Domain\Project\DwsProject as DomainDwsProject;
use Domain\Project\DwsProjectProgram as DomainDwsProjectProgram;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Model;

/**
 * 障害福祉サービス：計画属性 Eloquent モデル.
 *
 * @property int $id 障害福祉サービス：計画属性ID
 * @property int $contract_id 契約ID
 * @property int $office_id 事業所ID
 * @property int $user_id 利用者ID
 * @property int $staff_id 作成者ID
 * @property \Domain\Common\Carbon $written_on 作成日
 * @property \Domain\Common\Carbon $effectivated_on 適用日
 * @property string $request_from_user ご本人の希望
 * @property string $request_from_family ご家族の希望
 * @property string $objective 援助目標
 * @property bool $is_enabled 有効フラグ
 * @property int $version バージョン
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\Project\DwsProjectProgram[] $programs 週間サービス計画
 */
final class DwsProjectAttr extends Model
{
    /**
     * テーブル名.
     */
    public const TABLE = 'dws_project_attr';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'dws_project_id',
        'contract_id',
        'office_id',
        'user_id',
        'staff_id',
        'written_on',
        'effectivated_on',
        'request_from_user',
        'request_from_family',
        'objective',
        'is_enabled',
        'version',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'written_on' => 'date',
        'effectivated_on' => 'date',
        'is_enabled' => 'boolean',
        'updated_at' => 'datetime',
    ];

    /**
     * HasMany: {@link \Infrastructure\Project\DwsProjectProgram}
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function programs(): HasMany
    {
        return $this->hasMany(DwsProjectProgram::class);
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Project\DwsProject $domain
     * @return \Infrastructure\Project\DwsProjectAttr
     */
    public static function fromDomain(DomainDwsProject $domain): self
    {
        $keys = [
            'contract_id',
            'office_id',
            'user_id',
            'staff_id',
            'written_on',
            'effectivated_on',
            'request_from_user',
            'request_from_family',
            'objective',
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
            'programs',
        ];
        return $this->only($hasGetMutatorAttrs) + parent::toDomainValues();
    }

    /**
     * Get mutator for programs.
     *
     * @noinspection PhpUnused
     */
    protected function getProgramsAttribute(): array
    {
        return $this->mapSortRelation(
            'programs',
            'sort_order',
            fn (DwsProjectProgram $x): DomainDwsProjectProgram => $x->toDomain()
        );
    }
}
