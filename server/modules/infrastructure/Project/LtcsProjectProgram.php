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
use Domain\Project\LtcsProjectAmount as DomainLtcsProjectAmount;
use Domain\Project\LtcsProjectContent as DomainLtcsProjectContent;
use Domain\Project\LtcsProjectProgram as DomainLtcsProjectProgram;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\Timeframe;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Common\CastsRecurrence;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\Shift\ServiceOptionsHolder;
use Infrastructure\Shift\SyncServiceOptions;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：計画：週間サービス計画 Eloquent モデル.
 *
 * @property int $id 介護保険サービス：計画：週間サービス計画ID
 * @property int $ltcs_project_attr_id 介護保険サービス：計画属性ID
 * @property null|int $own_expense_program_id 自費サービス情報ID
 * @property int $sort_order 表示順
 * @property int $program_index 週間サービス計画番号
 * @property \Domain\Project\LtcsProjectServiceCategory $category サービス区分
 * @property \Domain\Common\Recurrence $recurrence 繰り返し周期
 * @property int $headcount 提供人数
 * @property \Domain\ServiceCode\ServiceCode $service_code サービスコード
 * @property string $note 備考
 * @property-read \Domain\Common\DayOfWeek[] $day_of_weeks 曜日
 * @property-read \Domain\ServiceCodeDictionary\Timeframe $slot 時間帯
 * @property-read \Domain\Common\TimeRange $timeframe 算定時間帯
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\Project\LtcsProjectProgramAmount[] $amounts サービス提供量
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\Project\LtcsProjectContent[] $contents サービス詳細
 */
final class LtcsProjectProgram extends Model implements Domainable
{
    use ServiceOptionsHolder;
    use SyncServiceOptions;

    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_project_program';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'ltcs_project_attr_id',
        'own_expense_program_id',
        'sort_order',
        'program_index',
        'category',
        'recurrence',
        'day_of_weeks',
        'slot',
        'timeframe',
        'headcount',
        'service_code',
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
        'category' => CastsLtcsProjectServiceCategory::class,
        'recurrence' => CastsRecurrence::class,
    ];

    /**
     * HasMany: LtcsProjectProgramToServiceOption.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function options(): HasMany
    {
        return $this->hasMany(LtcsProjectProgramServiceOption::class);
    }

    /**
     * HasMany: {@link \Infrastructure\Project\LtcsProjectProgramAmount}
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function amounts(): HasMany
    {
        return $this->hasMany(LtcsProjectProgramAmount::class);
    }

    /**
     * HasMany: {@link \Infrastructure\Project\LtcsProjectContent}
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contents(): HasMany
    {
        return $this->hasMany(LtcsProjectContent::class);
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Project\LtcsProjectProgram $domain
     * @param array $additional
     * @return \Infrastructure\Project\LtcsProjectProgram
     */
    public static function fromDomain(DomainLtcsProjectProgram $domain, array $additional): self
    {
        $keys = [
            'own_expense_program_id',
            'program_index',
            'category',
            'recurrence',
            'day_of_weeks',
            'slot',
            'timeframe',
            'amounts',
            'headcount',
            'service_code',
            'note',
            'options',
            'contents',
        ];
        $values = self::getDomainValues($domain, $keys);
        return self::firstOrNew($additional, $additional + $values)->fill($additional + $values);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainLtcsProjectProgram
    {
        $hasGetMutatorAttrs = [
            'amounts',
            'contents',
            'dayOfWeeks',
            'options',
            'slot',
            'timeframe',
        ];
        return DomainLtcsProjectProgram::create($this->only($hasGetMutatorAttrs) + $this->toDomainValues());
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
     * Get mutator for timeframe attribute.
     *
     * @return \Domain\ServiceCodeDictionary\Timeframe
     * @noinspection PhpUnused
     */
    public function getTimeframeAttribute(): Timeframe
    {
        return Timeframe::from($this->attributes['timeframe']);
    }

    /**
     * Set mutator for timeframe attribute.
     *
     * @param \Domain\ServiceCodeDictionary\Timeframe $timeframe
     * @return void
     * @noinspection PhpUnused
     */
    public function setTimeframeAttribute(Timeframe $timeframe): void
    {
        $this->attributes['timeframe'] = $timeframe->value();
    }

    /**
     * Get mutator for amounts.
     *
     * @noinspection PhpUnused
     */
    protected function getAmountsAttribute(): array
    {
        return $this->mapSortRelation(
            'amounts',
            'sort_order',
            fn (LtcsProjectProgramAmount $x): DomainLtcsProjectAmount => $x->toDomain()
        );
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
            fn (LtcsProjectContent $x): DomainLtcsProjectContent => $x->toDomain()
        );
    }

    /**
     * Get mutator for name attribute.
     *
     * @return \Domain\ServiceCode\ServiceCode
     * @noinspection PhpUnused
     */
    protected function getServiceCodeAttribute(): ServiceCode
    {
        return ServiceCode::fromString($this->attributes['service_code']);
    }

    /**
     * Set mutator for service_code attribute.
     *
     * @param \Domain\ServiceCode\ServiceCode $serviceCode
     * @return void
     * @noinspection PhpUnused
     */
    protected function setServiceCodeAttribute(ServiceCode $serviceCode): void
    {
        $this->attributes['service_code'] = $serviceCode->toString();
    }
}
