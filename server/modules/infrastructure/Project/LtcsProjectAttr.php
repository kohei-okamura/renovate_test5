<?php
/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Project;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Project\LtcsProject as DomainLtcsProject;
use Domain\Project\LtcsProjectProgram as DomainLtcsProjectProgram;
use Domain\Project\Objective;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Model;

/**
 * 介護保険サービス：計画属性 Eloquent モデル.
 *
 * @property int $id 介護保険サービス：計画属性ID
 * @property int $contract_id 契約ID
 * @property int $office_id 事業所ID
 * @property int $user_id 利用者ID
 * @property int $staff_id 作成者ID
 * @property \Domain\Common\Carbon $written_on 作成日
 * @property \Domain\Common\Carbon $effectivated_on 適用日
 * @property string $request_from_user ご本人の希望
 * @property string $request_from_family ご家族の希望
 * @property string $problem 解決すべき課題
 * @property \Domain\Project\Objective $long_term_objective 長期目標
 * @property \Domain\Project\Objective $short_term_objective 短期目標
 * @property bool $is_enabled 有効フラグ
 * @property int $version バージョン
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\Project\LtcsProjectProgram[] $programs 週間サービス計画
 */
final class LtcsProjectAttr extends Model
{
    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_project_attr';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'ltcs_project_id',
        'contract_id',
        'office_id',
        'user_id',
        'staff_id',
        'written_on',
        'effectivated_on',
        'request_from_user',
        'request_from_family',
        'problem',
        'long_term_objective',
        'short_term_objective',
        'is_enabled',
        'version',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'written_on' => 'date',
        'effectivated_on' => 'date',
        'long_term_objective_term_start' => 'date',
        'long_term_objective_term_end' => 'date',
        'short_term_objective_term_start' => 'date',
        'short_term_objective_term_end' => 'date',
        'is_enabled' => 'boolean',
        'updated_at' => 'datetime',
    ];

    /**
     * HasMany: {@link \Infrastructure\Project\LtcsProjectProgram}
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function programs(): HasMany
    {
        return $this->hasMany(LtcsProjectProgram::class);
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Project\LtcsProject $domain
     * @return \Infrastructure\Project\LtcsProjectAttr
     */
    public static function fromDomain(DomainLtcsProject $domain): self
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
            'problem',
            'long_term_objective',
            'short_term_objective',
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
            'longTermObjective',
            'shortTermObjective',
        ];
        return $this->only($hasGetMutatorAttrs) + parent::toDomainValues();
    }

    /**
     * Get mutator for amounts.
     *
     * @noinspection PhpUnused
     */
    protected function getProgramsAttribute(): array
    {
        return $this->mapSortRelation(
            'programs',
            'sort_order',
            fn (LtcsProjectProgram $x): DomainLtcsProjectProgram => $x->toDomain()
        );
    }

    /**
     * Get mutator from long_term_objective attribute.
     *
     * @return \Domain\Project\Objective
     * @noinspection PhpUnused
     */
    protected function getLongTermObjectiveAttribute(): Objective
    {
        return Objective::create([
            'term' => CarbonRange::create([
                'start' => Carbon::parse($this->long_term_objective_term_start),
                'end' => Carbon::parse($this->long_term_objective_term_end),
            ]),
            'text' => $this->attributes['long_term_objective_text'],
        ]);
    }

    /**
     * Set mutator for long_term_objective attributes.
     *
     * @param \Domain\Project\Objective $objective
     * @noinspection PhpUnused
     */
    protected function setLongTermObjectiveAttribute(Objective $objective): void
    {
        $this->attributes['long_term_objective_text'] = $objective->text;
        $this->attributes['long_term_objective_term_start'] = $objective->term->start;
        $this->attributes['long_term_objective_term_end'] = $objective->term->end;
    }

    /**
     * Get mutator from short_term_objective attribute.
     *
     * @return \Domain\Project\Objective
     * @noinspection PhpUnused
     */
    protected function getShortTermObjectiveAttribute(): Objective
    {
        return Objective::create([
            'term' => CarbonRange::create([
                'start' => Carbon::parse($this->short_term_objective_term_start),
                'end' => Carbon::parse($this->short_term_objective_term_end),
            ]),
            'text' => $this->attributes['short_term_objective_text'],
        ]);
    }

    /**
     * Set mutator for short_term_objective attributes.
     *
     * @param \Domain\Project\Objective $objective
     * @noinspection PhpUnused
     */
    protected function setShortTermObjectiveAttribute(Objective $objective): void
    {
        $this->attributes['short_term_objective_text'] = $objective->text;
        $this->attributes['short_term_objective_term_start'] = $objective->term->start;
        $this->attributes['short_term_objective_term_end'] = $objective->term->end;
    }
}
