<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\User;

use Domain\Common\Addr;
use Domain\Common\Contact as DomainContact;
use Domain\Common\Prefecture;
use Domain\User\BillingDestination;
use Domain\User\PaymentMethod;
use Domain\User\User as DomainUser;
use Domain\User\UserBillingDestination;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Common\AddrHolder;
use Infrastructure\Common\CastsSex;
use Infrastructure\Common\LocationHolder;
use Infrastructure\Common\NameHolder;
use Infrastructure\Model;

/**
 * 利用者属性 Eloquent モデル.
 *
 * @property int $id 利用者属性ID
 * @property \Domain\Common\Carbon $birthday 生年月日
 * @property int $billing_destination_destination 請求先
 * @property int $billing_destination_payment_method 支払方法
 * @property string $billing_destination_contract_number 契約者番号
 * @property string $billing_destination_corporation_name 請求先法人名・団体名
 * @property string $billing_destination_agent_name 請求先氏名・担当者名
 * @property string $billing_destination_addr_postcode 請求先：郵便番号
 * @property int $billing_destination_addr_prefecture 請求先：都道府県
 * @property string $billing_destination_addr_city 請求先：市区町村
 * @property string $billing_destination_addr_street 請求先：町名・番地
 * @property string $billing_destination_addr_apartment 請求先：建物名など
 * @property string $billing_destination_tel 請求先電話番号
 * @property-read \Domain\User\UserBillingDestination $billing_destination 請求先情報
 * @property bool $is_enabled 有効フラグ
 * @property int $version バージョン
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\User\UserContact[] $contacts 連絡先電話番号
 * @method static \Illuminate\Database\Eloquent\Builder|UserAttr newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserAttr newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserAttr query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserAttr whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAttr whereFax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAttr whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAttr whereIsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAttr whereLocationLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAttr whereLocationLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAttr whereTel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAttr whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAttr whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAttr whereVersion($value)
 * @mixin \Eloquent
 */
final class UserAttr extends Model
{
    use AddrHolder;
    use BelongsToUser;
    use LocationHolder;
    use NameHolder;

    /**
     * テーブル名.
     */
    public const TABLE = 'user_attr';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'user_id',
        'name',
        'sex',
        'birthday',
        'addr',
        'location',
        'billing_destination',
        'is_enabled',
        'version',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'is_enabled' => 'boolean',
        'birthday' => 'date',
        'updated_at' => 'datetime',
        'sex' => CastsSex::class,
    ];

    /**
     * HasMany: {@link \Infrastructure\User\UserContact}
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(UserContact::class);
    }

    /** {@inheritdoc} */
    public function toDomainValues(): array
    {
        $hasGetMutatorAttrs = [
            'name',
            'addr',
            'location',
            'contacts',
            'billingDestination',
        ];
        return $this->only($hasGetMutatorAttrs) + parent::toDomainValues();
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\User\User $domain
     * @return \Infrastructure\User\UserAttr
     */
    public static function fromDomain(DomainUser $domain): self
    {
        $keys = [
            'name',
            'sex',
            'birthday',
            'addr',
            'location',
            'billing_destination',
            'is_enabled',
            'version',
            'updated_at',
        ];
        $attrs = self::getDomainValues($domain, $keys);
        return self::newModelInstance($attrs);
    }

    /**
     * Get mutator for contacts.
     *
     * @noinspection PhpUnused
     */
    protected function getContactsAttribute(): array
    {
        return $this->mapSortRelation(
            'contacts',
            'sort_order',
            fn (UserContact $x): DomainContact => $x->toDomain()
        );
    }

    /**
     * Get mutator for billing_destination attribute.
     *
     * @noinspection PhpUnused
     */
    protected function getBillingDestinationAttribute(): UserBillingDestination
    {
        $destination = BillingDestination::from($this->billing_destination_destination);
        return UserBillingDestination::create([
            'destination' => $destination,
            'paymentMethod' => PaymentMethod::from($this->billing_destination_payment_method),
            'contractNumber' => $this->billing_destination_contract_number,
            'corporationName' => $this->billing_destination_corporation_name,
            'agentName' => $this->billing_destination_agent_name,
            'addr' => $destination === BillingDestination::theirself()
                ? null
                : new Addr(
                    postcode: $this->billing_destination_addr_postcode,
                    prefecture: Prefecture::from($this->billing_destination_addr_prefecture),
                    city: $this->billing_destination_addr_city,
                    street: $this->billing_destination_addr_street,
                    apartment: $this->billing_destination_addr_apartment,
                ),
            'tel' => $this->billing_destination_tel,
        ]);
    }

    /**
     * Set mutator for billing_destination attribute.
     *
     * @param \Domain\User\UserBillingDestination $x
     * @return void
     * @noinspection PhpUnused
     */
    protected function setBillingDestinationAttribute(UserBillingDestination $x): void
    {
        $this->attributes['billing_destination_destination'] = $x->destination->value();
        $this->attributes['billing_destination_payment_method'] = $x->paymentMethod->value();
        $this->attributes['billing_destination_contract_number'] = $x->contractNumber;
        $this->attributes['billing_destination_corporation_name'] = $x->corporationName;
        $this->attributes['billing_destination_agent_name'] = $x->agentName;
        if ($x->destination === BillingDestination::theirself()) {
            $this->attributes['billing_destination_addr_postcode'] = '';
            $this->attributes['billing_destination_addr_prefecture'] = Prefecture::none();
            $this->attributes['billing_destination_addr_city'] = '';
            $this->attributes['billing_destination_addr_street'] = '';
            $this->attributes['billing_destination_addr_apartment'] = '';
        } else {
            $this->attributes['billing_destination_addr_postcode'] = $x->addr->postcode;
            $this->attributes['billing_destination_addr_prefecture'] = $x->addr->prefecture->value();
            $this->attributes['billing_destination_addr_city'] = $x->addr->city;
            $this->attributes['billing_destination_addr_street'] = $x->addr->street;
            $this->attributes['billing_destination_addr_apartment'] = $x->addr->apartment;
        }
        $this->attributes['billing_destination_tel'] = $x->tel;
    }
}
