<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Organization;

use Domain\Organization\OrganizationSetting as DomainOrganizationSetting;
use Illuminate\Support\Str;
use Infrastructure\Domainable;
use Infrastructure\Model;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * 事業者別設定 Eloquent モデル.
 *
 * @property int $id 事業者別設定ID
 * @property int $organization_id 事業者ID
 * @property array $setting 設定
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property \Domain\Common\Carbon $updated_at 更新日時
 */
final class OrganizationSetting extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'organization_setting';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'organization_id',
        'setting',
        'created_at',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'setting' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** {@inheritdoc} */
    public function toDomain(): DomainOrganizationSetting
    {
        return DomainOrganizationSetting::create($this->setting + $this->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Organization\OrganizationSetting $domain
     * @return \Infrastructure\Organization\OrganizationSetting
     */
    public static function fromDomain(DomainOrganizationSetting $domain): self
    {
        $keys = [
            'id',
            'organization_id',
            'created_at',
            'updated_at',
        ];
        $attrs = self::getDomainValues($domain, $keys);
        // ドメインモデルの属性名が変更された場合はマイグレーションで対応が必要
        $setting = Map::from($domain->toAssoc())
            ->flatMap(fn ($v, $k): iterable => Seq::from(...$keys)->contains(Str::snake($k)) ? [] : yield $k => $v)
            ->toAssoc();

        return self::findOrNew($domain->id, ['id'])->fill($attrs + compact('setting'));
    }
}
