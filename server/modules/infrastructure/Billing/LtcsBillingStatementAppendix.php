<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\ProvisionReport\LtcsProvisionReportSheetAppendix as DomainAppendix;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry as DomainAppendixEntry;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Common\CastsDecimal;
use Infrastructure\Domainable;
use Infrastructure\Model;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：明細書：サービス提供票別表 Eloquent モデル.
 *
 * @property int $id サービス提供票別表 ID
 * @property int $statement_id 明細書 ID
 * @property \Domain\Common\Carbon $provided_in サービス提供年月
 * @property string $ins_number 被保険者証番号
 * @property string $user_name 利用者氏名
 * @property-read \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry[]&\ScalikePHP\Seq $unmanaged_entries サービス情報（支給限度対象外）
 * @property-read \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry[]&\ScalikePHP\Seq $managed_entries サービス情報（支給限度対象）
 * @property int $max_benefit 区分支給限度基準額（単位）
 * @property int $insurance_claim_amount 保険請求分
 * @property int $subsidy_claim_amount 公費請求額
 * @property int $copay_amount 利用者請求額
 * @property \Domain\Common\Decimal $unit_cost 単位数単価
 */
final class LtcsBillingStatementAppendix extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_billing_statement_appendix';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'provided_in',
        'ins_number',
        'user_name',
        'max_benefit',
        'insurance_claim_amount',
        'subsidy_claim_amount',
        'copay_amount',
        'unit_cost',
    ];

    /**
     * ドメインモデルに定義されている属性.
     */
    private const DOMAIN_ATTRIBUTES = [
        ...self::ATTRIBUTES,
        'unmanaged_entries',
        'managed_entries',
    ];

    /**
     * 小数部の桁数.
     */
    private const FRACTION_DIGITS = 4;

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'statement_id',
        ...self::ATTRIBUTES,
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'provided_in' => 'date',
        'unit_cost' => CastsDecimal::class,
    ];

    /** {@inheritdoc} */
    protected $with = [
        'entries',
    ];

    /**
     * ドメインモデルからインスタンスを生成する.
     *
     * @param \Domain\ProvisionReport\LtcsProvisionReportSheetAppendix $domain
     * @param int $statementId
     * @return static
     */
    public static function fromDomain(DomainAppendix $domain, int $statementId): self
    {
        $keys = [
            'statement_id' => $statementId,
        ];
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::firstOrNew($keys, $attrs)->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainAppendix
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainAppendix::fromAssoc($attrs);
    }

    /**
     * HasMany: {@link \Infrastructure\Billing\LtcsBillingStatementAppendixEntry}
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function entries(): HasMany
    {
        return $this
            ->hasMany(LtcsBillingStatementAppendixEntry::class, 'statement_appendix_id')
            ->orderBy('sort_order');
    }

    /**
     * Get mutator for `unmanaged_entries` attribute.
     *
     * @return \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry[]&\ScalikePHP\Seq
     * @noinspection PhpUnused
     */
    protected function getUnmanagedEntriesAttribute(): Seq
    {
        return $this->getEntries(LtcsBillingStatementAppendixEntry::ENTRY_TYPE_UNMANAGED);
    }

    /**
     * Get mutator for `managed_entries` attribute.
     *
     * @return \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry[]&\ScalikePHP\Seq
     * @noinspection PhpUnused
     */
    protected function getManagedEntriesAttribute(): Seq
    {
        return $this->getEntries(LtcsBillingStatementAppendixEntry::ENTRY_TYPE_MANAGED);
    }

    /**
     * サービス情報の一覧を取得する.
     *
     * @param int $entryType
     * @return \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry[]&\ScalikePHP\Seq
     */
    private function getEntries(int $entryType): Seq
    {
        $xs = $this->getRelationValue('entries');
        return $xs === null
            ? Seq::empty()
            : Seq::from(...$xs)
                ->filter(fn (LtcsBillingStatementAppendixEntry $x): bool => $x->entry_type === $entryType)
                ->map(fn (LtcsBillingStatementAppendixEntry $x): DomainAppendixEntry => $x->toDomain())
                ->computed();
    }
}
