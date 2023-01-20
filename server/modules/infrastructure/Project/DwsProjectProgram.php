<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Project;

use Domain\Common\Carbon;
use Domain\Common\DayOfWeek;
use Domain\Common\TimeRange;
use Domain\Project\DwsProjectContent as DomainDwsProjectContent;
use Domain\Project\DwsProjectProgram as DomainDwsProjectProgram;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Common\CastsRecurrence;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\Shift\ServiceOptionsHolder;
use Infrastructure\Shift\SyncServiceOptions;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：計画：週間サービス計画 Eloquent モデル.
 *
 * @property int $id 障害福祉サービス：計画：週間サービス計画ID
 * @property int $dws_project_attr_id 障害福祉サービス：計画属性ID
 * @property null|int $own_expense_program_id 自費サービス情報ID
 * @property int $sort_order 表示順
 * @property int $summary_index 週間サービス計画番号
 * @property \Domain\Project\DwsProjectServiceCategory $category サービス区分
 * @property \Domain\Common\Recurrence $recurrence 繰り返し周期
 * @property int $headcount 提供人数
 * @property string $note 備考
 * @property-read \Domain\Common\DayOfWeek[] $day_of_weeks 曜日
 * @property-read \Domain\Common\TimeRange $slot 時間帯
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\Project\DwsProjectContent[] $contents サービス詳細
 */
final class DwsProjectProgram extends Model implements Domainable
{
    use ServiceOptionsHolder;
    use SyncServiceOptions;

    /**
     * テーブル名.
     */
    public const TABLE = 'dws_project_program';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'dws_project_attr_id',
        'own_expense_program_id',
        'sort_order',
        'summary_index',
        'category',
        'recurrence',
        'day_of_weeks',
        'slot',
        'headcount',
        'note',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'mon' => 'boolean',
        'tue' => 'boolean',
        'wed' => 'boolean',
        'thu' => 'boolean',
        'fri' => 'boolean',
        'sat' => 'boolean',
        'sun' => 'boolean',
        'category' => CastsDwsProjectServiceCategory::class,
        'recurrence' => CastsRecurrence::class,
    ];

    /**
     * HasMany: DwsProjectProgramToServiceOption.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function options(): HasMany
    {
        return $this->hasMany(DwsProjectProgramServiceOption::class);
    }

    /**
     * HasMany: {@link \Infrastructure\Project\DwsProjectContent}
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contents(): HasMany
    {
        return $this->hasMany(DwsProjectContent::class);
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Project\DwsProjectProgram $domain
     * @param array $additional
     * @return \Infrastructure\Project\DwsProjectProgram
     */
    public static function fromDomain(DomainDwsProjectProgram $domain, array $additional): self
    {
        $keys = [
            'own_expense_program_id',
            'summary_index',
            'category',
            'recurrence',
            'day_of_weeks',
            'slot',
            'headcount',
            'note',
            'options',
            'contents',
        ];
        $values = self::getDomainValues($domain, $keys);
        return self::firstOrNew($additional, $additional + $values)->fill($additional + $values);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainDwsProjectProgram
    {
        $hasGetMutatorAttrs = [
            'contents',
            'dayOfWeeks',
            'options',
            'slot',
        ];
        return DomainDwsProjectProgram::create($this->only($hasGetMutatorAttrs) + $this->toDomainValues());
    }

    /**
     * Get mutator for day_of_weeks attribute.
     *
     * @return \Domain\Common\DayOfWeek[]
     * @noinspection PhpUnused
     */
    public function getDayOfWeeksAttribute(): array
    {
        return Seq::fromArray(DayOfWeek::all())
            ->filter(fn (DayOfWeek $dayOfWeek) => $this->attributes[$dayOfWeek->key()])
            ->toArray();
    }

    /**
     * Set mutator for day_of_weeks attribute.
     *
     * @param \Domain\Common\DayOfWeek[] $dayOfWeeks
     * @noinspection PhpUnused
     */
    public function setDayOfWeeksAttribute(array $dayOfWeeks): void
    {
        foreach (DayOfWeek::all() as $dayOfWeek) {
            $this->attributes[$dayOfWeek->key()] = in_array($dayOfWeek, $dayOfWeeks, true);
        }
    }

    /**
     * Get mutator for slot attribute.
     *
     * @return \Domain\Common\TimeRange
     * @noinspection PhpUnused
     */
    public function getSlotAttribute(): TimeRange
    {
        return TimeRange::create([
            'start' => Carbon::parse($this->attributes['slot_start'])->format('H:i'),
            'end' => Carbon::parse($this->attributes['slot_end'])->format('H:i'),
        ]);
    }

    /**
     * Set mutator for slot attribute.
     *
     * @param \Domain\Common\TimeRange $slot
     * @return void
     * @noinspection PhpUnused
     */
    public function setSlotAttribute(TimeRange $slot): void
    {
        $this->attributes['slot_start'] = $slot->start;
        $this->attributes['slot_end'] = $slot->end;
    }

    /**
     * Get mutator for contents.
     *
     * @noinspection PhpUnused
     */
    protected function getContentsAttribute(): array
    {
        return $this->mapSortRelation(
            'contents',
            'sort_order',
            fn (DwsProjectContent $x): DomainDwsProjectContent => $x->toDomain()
        );
    }
}
