<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Shift;

use Domain\Shift\Assignee;
use Domain\Shift\Duration;
use Domain\Shift\Shift as DomainShift;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Common\ScheduleHolder;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\Office\Office;
use Infrastructure\ServiceCode\ServiceCodeHolder;
use Infrastructure\User\User;

/**
 * 勤務シフト Eloquent モデル.
 *
 * @property int $id 勤務シフトID
 * @property int $organization_id 事業者ID
 * @property int $office_id 事業所ID
 * @property int $shift_import_id 勤務シフトインポートID
 * @property null|int $user_id 利用者ID
 * @property int $assigner_id 管理スタッフ
 * @property int $task 勤務区分
 * @property string $schedule_start 開始日時
 * @property string $schedule_end 終了日時
 * @property string $schedule_date 勤務日
 * @property string $note 備考
 * @property bool $is_confirmed 確定フラグ
 * @property bool $is_canceled キャンセルフラグ
 * @property string $reason キャンセル理由
 * @property \Illuminate\Support\Carbon $updated_at 更新日時
 * @property \Illuminate\Support\Carbon $created_at 登録日時
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\Shift\ShiftAssignee[] $assignees
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\Shift\ShiftDuration[] $durations
 * @property-read \Domain\Shift\ServiceOption[] $options
 * @property-read null|int $assignees_count
 * @property-read \Infrastructure\Office\Office $office
 * @property-read null|\Infrastructure\User\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Shift newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Shift newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Shift query()
 * @method static \Illuminate\Database\Eloquent\Builder|Shift whereAssignerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shift whereIsConfirmed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shift whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shift whereOfficeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shift whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shift whereShiftImportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shift whereScheduleDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shift whereScheduleEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shift whereScheduleStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shift whereTask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shift whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shift whereUserId($value)
 */
final class Shift extends Model implements Domainable
{
    use ScheduleHolder;
    use ServiceCodeHolder;
    use ServiceOptionsHolder;
    use SyncServiceOptions;

    /**
     * テーブル名.
     */
    public const TABLE = 'shift';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'organization_id',
        'office_id',
        'contract_id',
        'shift_import_id',
        'user_id',
        'assigner_id',
        'headcount',
        'task',
        'service_code',
        'schedule',
        'note',
        'is_confirmed',
        'is_canceled',
        'reason',
        'updated_at',
        'created_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'is_confirmed' => 'boolean',
        'is_canceled' => 'boolean',
        'schedule_start' => 'datetime',
        'schedule_end' => 'datetime',
        'schedule_date' => 'date',
        'updated_at' => 'datetime',
        'created_at' => 'datetime',
        'task' => CastsTask::class,
    ];

    /**
     * BelongsTo: User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @codeCoverageIgnore リレーションの定義のため
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * BelongsTo: Office.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @codeCoverageIgnore リレーションの定義のため
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    /**
     * HasMany: ShiftAssignee.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assignees(): HasMany
    {
        return $this->hasMany(ShiftAssignee::class)->orderBy('sort_order');
    }

    /**
     * HasMany: ShiftDuration.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function durations(): HasMany
    {
        return $this->hasMany(ShiftDuration::class);
    }

    /**
     * HasMany: ShiftToServiceOption.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function options(): HasMany
    {
        return $this->hasMany(ShiftServiceOption::class);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainShift
    {
        return DomainShift::create($this->toDomainValues());
    }

    /** {@inheritdoc} */
    public function toDomainValues(): array
    {
        $hasGetMutatorAttrs = [
            'service_code',
            'assignees',
            'schedule',
            'durations',
            'options',
        ];
        return $this->only($hasGetMutatorAttrs) + parent::toDomainValues();
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Shift\Shift $domain
     * @return \Infrastructure\Shift\Shift
     */
    public static function fromDomain(DomainShift $domain): self
    {
        $keys = [
            'organization_id',
            'contract_id',
            'office_id',
            'user_id',
            'assigner_id',
            'task',
            'service_code',
            'headcount',
            'assignees',
            'schedule',
            'durations',
            'options',
            'note',
            'is_confirmed',
            'is_canceled',
            'reason',
            'created_at',
            'updated_at',
        ];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill(
            [
                'schedule_start' => $domain->schedule->get('start'),
                'schedule_end' => $domain->schedule->get('end'),
                'schedule_date' => $domain->schedule->get('date'),
            ] + $values
        );
    }

    /**
     * Get mutator for durations.
     *
     * @return array|\Domain\Shift\Duration[]
     * @noinspection PhpUnused
     */
    protected function getDurationsAttribute(): array
    {
        return $this->mapRelation(
            'durations',
            fn (ShiftDuration $x) => Duration::create([
                'activity' => $x->activity,
                'duration' => $x->duration,
            ])
        );
    }

    /**
     * Get mutator for assignees.
     *
     * @return array
     * @noinspection PhpUnused
     */
    protected function getAssigneesAttribute(): array
    {
        return $this->mapSortRelation(
            'assignees',
            'sort_order',
            fn (ShiftAssignee $x) => Assignee::create([
                'staffId' => $x->staff_id,
                'isUndecided' => $x->staff_id === null,
                'isTraining' => $x->is_training,
            ])
        );
    }
}
