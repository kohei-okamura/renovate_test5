<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Project;

use Infrastructure\Model;
use Infrastructure\Shift\ServiceOptionHolder;
use Infrastructure\Shift\ServiceOptionProvider;

/**
 * サービスオプション（勤務シフト・勤務実績） Eloquent モデル.
 *
 * @property int $ltcs_project_program_id 介護保険サービス：計画：週間サービス計画ID
 */
final class LtcsProjectProgramServiceOption extends Model implements ServiceOptionProvider
{
    use ServiceOptionHolder;

    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_project_program_service_option';

    /** {@inheritdoc} */
    public $timestamps = false;

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'ltcs_project_program_id',
        'service_option',
    ];
}
