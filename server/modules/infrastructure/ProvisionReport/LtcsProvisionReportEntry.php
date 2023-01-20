<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ProvisionReport;

use Domain\Common\Carbon;
use Domain\Common\TimeRange;
use Domain\Project\LtcsProjectAmount as DomainLtcsProjectAmount;
use Domain\ProvisionReport\LtcsProvisionReportEntry as DomainLtcsProvisionReportEntry;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\Project\CastsLtcsProjectServiceCategory;
use Infrastructure\Project\LtcsProjectAmount;
use Infrastructure\ServiceCode\ServiceCodeHolder;
use Infrastructure\ServiceCodeDictionary\CastsTimeframe;
use Infrastructure\Shift\ServiceOptionsHolder;
use Infrastructure\Shift\SyncServiceOptions;

/**
 * 介護保険サービス：予実：サービス情報 Eloquent モデル.
 *
 * @property int $id 介護保険サービス：予実：サービス情報ID
 * @property int $ltcs_provision_report_id 介護保険サービス：予実ID
 * @property null|int $own_expense_program_id 自費サービス情報ID
 * @property int $sort_order 並び順
 * @property int $headcount 提供人数
 * @property string $note 備考
 * @property-read \Domain\Common\TimeRange $slot 時間帯
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\Project\LtcsProjectAmount[] $amounts サービス提供量
 * @property-read \Domain\Common\Carbon[]|\Illuminate\Database\Eloquent\Collection $plans 予定年月日
 * @property-read \Domain\Common\Carbon[]|\Illuminate\Database\Eloquent\Collection $results 実績年月日
 */
final class LtcsProvisionReportEntry extends Model implements Domainable
{
    use ServiceCodeHolder;
    use ServiceOptionsHolder;
    use SyncServiceOptions;

    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_provision_report_entry';

    /**
     * 属性.
     */
    private const ATTRIBUTES = [
        'id',
        'ltcs_provision_report_id',
        'own_expense_program_id',
        'sort_order',
        'slot',
        'timeframe',
        'category',
        'headcount',
        'service_code',
        'note',
    ];

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = self::ATTRIBUTES;

    protected $casts = [
        'timeframe' => CastsTimeframe::class,
        'category' => CastsLtcsProjectServiceCategory::class,
    ];

    /**
     * HasMany: ShiftToServiceOption.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function options(): HasMany
    {
        return $this->hasMany(LtcsProvisionReportEntryServiceOption::class);
    }

    /**
     * HasMany: {@link \Infrastructure\Project\LtcsProjectAmount}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @codeCoverageIgnore リレーションの定義のため
     */
    public function amounts(): HasMany
    {
        return $this->hasMany(LtcsProjectAmount::class);
    }

    /**
     * HasMany: {@link \Infrastructure\ProvisionReport\LtcsProvisionReportEntryPlan}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @codeCoverageIgnore リレーションの定義のため
     */
    public function plans(): HasMany
    {
        return $this->hasMany(LtcsProvisionReportEntryPlan::class);
    }

    /**
     * HasMany: {@link \Infrastructure\ProvisionReport\LtcsProvisionReportEntryResult}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @codeCoverageIgnore リレーションの定義のため
     */
    public function results(): HasMany
    {
        return $this->hasMany(LtcsProvisionReportEntryResult::class);
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\ProvisionReport\LtcsProvisionReportEntry $domain
     * @param array $additional
     * @return \Infrastructure\ProvisionReport\LtcsProvisionReportEntry
     */
    public static function fromDomain(DomainLtcsProvisionReportEntry $domain, array $additional): self
    {
        $keys = [
            'own_expense_program_id',
            'slot',
            'timeframe',
            'category',
            'amounts',
            'headcount',
            'service_code',
            'options',
            'note',
            'plans',
            'results',
        ];
        $values = self::getDomainValues($domain, $keys);
        return self::firstOrNew($additional, $additional + $values)->fill($additional + $values);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainLtcsProvisionReportEntry
    {
        $attrs = $this->toDomainAttributes([
            ...self::ATTRIBUTES,
            'amounts',
            'options',
            'plans',
            'results',
        ]);
        return DomainLtcsProvisionReportEntry::create($attrs);
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
     * Get mutator for amounts.
     *
     * @noinspection PhpUnused
     */
    protected function getAmountsAttribute(): array
    {
        return $this->mapSortRelation(
            'amounts',
            'sort_order',
            fn (LtcsProjectAmount $x): DomainLtcsProjectAmount => $x->toDomain()
        );
    }

    /**
     * Get mutator for plans.
     *
     * @noinspection PhpUnused
     */
    protected function getPlansAttribute(): array
    {
        return $this->mapSortRelation(
            'plans',
            'sort_order',
            fn (LtcsProvisionReportEntryPlan $x): Carbon => $x->date
        );
    }

    /**
     * Get mutator for results.
     *
     * @noinspection PhpUnused
     */
    protected function getResultsAttribute(): array
    {
        return $this->mapSortRelation(
            'results',
            'sort_order',
            fn (LtcsProvisionReportEntryResult $x): Carbon => $x->date
        );
    }
}
