<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\BankAccount;

use Domain\BankAccount\BankAccount as DomainBankAccount;
use Infrastructure\Model;

/**
 * 銀行口座属性 Eloquent モデル.
 *
 * @property int $id 銀行口座属性ID
 * @property string $bank_name 銀行名
 * @property string $bank_code 銀行コード
 * @property string $bank_branch_name 銀行支店名
 * @property string $bank_branch_code 銀行支店コード
 * @property \Domain\BankAccount\BankAccountType $bank_account_type 銀行口座種別
 * @property string $bank_account_number 銀行口座番号
 * @property string $bank_account_holder 銀行口座名義
 * @property int $version バージョン
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccountAttr newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccountAttr newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccountAttr query()
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccountAttr whereBankAccountHolder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccountAttr whereBankAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccountAttr wherebankAccountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccountAttr whereBankBranchCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccountAttr whereBankBranchName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccountAttr whereBankCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccountAttr whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccountAttr whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccountAttr whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccountAttr whereVersion($value)
 * @mixin \Eloquent
 */
final class BankAccountAttr extends Model
{
    use BelongsToBankAccount;

    /**
     * テーブル名.
     */
    public const TABLE = 'bank_account_attr';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'bank_account_id',
        'bank_name',
        'bank_code',
        'bank_branch_name',
        'bank_branch_code',
        'bank_account_type',
        'bank_account_number',
        'bank_account_holder',
        'version',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'updated_at' => 'datetime',
        'bank_account_type' => CastsBankAccountType::class,
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\BankAccount\BankAccount $domain
     * @return \Infrastructure\BankAccount\BankAccountAttr
     */
    public static function fromDomain(DomainBankAccount $domain): self
    {
        $keys = [
            'bank_name',
            'bank_code',
            'bank_branch_name',
            'bank_branch_code',
            'bank_account_type',
            'bank_account_number',
            'bank_account_holder',
            'version',
            'updated_at',
        ];
        $attrs = self::getDomainValues($domain, $keys);
        return self::newModelInstance($attrs);
    }
}
