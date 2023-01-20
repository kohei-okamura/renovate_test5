<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\DwsCertification;

use Domain\DwsCertification\DwsCertificationAgreement as DomainDwsCertificationAgreement;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\Office\BelongsToOffice;

/**
 * 障害福祉サービス受給者証 訪問系サービス事業者記入欄 Eloquent モデル.
 *
 * @property int $id 訪問系サービス事業者記入欄ID
 * @property int $dws_certification_attr_id 障害福祉サービス受給者証属性ID
 * @property int $index_number 番号
 * @property \Domain\DwsCertification\DwsCertificationAgreementType $dws_certification_agreement_type サービス内容
 * @property int $payment_amount 契約支給量
 * @property \Domain\Common\Carbon $agreed_on 契約日
 * @property \Domain\Common\Carbon $expired_on 当該契約支給量によるサービス提供終了日
 * @property int $sort_order 表示順
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAgreement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAgreement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAgreement query()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAgreement whereAgreedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAgreement whereDwsCertificationAgreementType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAgreement whereDwsCertificationAttrId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAgreement whereExpiredOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAgreement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAgreement whereIndexNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAgreement whereOfficeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAgreement wherePaymentAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsCertificationAgreement whereSortOrder($value)
 * @mixin \Eloquent
 */
final class DwsCertificationAgreement extends Model implements Domainable
{
    use BelongsToOffice;

    /**
     * テーブル名.
     */
    public const TABLE = 'dws_certification_agreement';

    /** {@inheritdoc} */
    public $timestamps = false;

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'dws_certification_attr_id',
        'office_id',
        'index_number',
        'dws_certification_agreement_type',
        'payment_amount',
        'agreed_on',
        'expired_on',
        'sort_order',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'agreed_on' => 'date',
        'expired_on' => 'date',
        'dws_certification_agreement_type' => CastsDwsCertificationAgreementType::class,
    ];

    /** {@inheritdoc} */
    public function toDomain(): DomainDwsCertificationAgreement
    {
        return DomainDwsCertificationAgreement::create($this->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\DwsCertification\DwsCertificationAgreement $domain
     * @param array $values
     * @return \Infrastructure\DwsCertification\DwsCertificationAgreement
     */
    public static function fromDomain(DomainDwsCertificationAgreement $domain, array $values): self
    {
        $keys = [
            'index_number',
            'office_id',
            'dws_certification_agreement_type',
            'payment_amount',
            'agreed_on',
            'expired_on',
        ];
        $attrs = self::getDomainValues($domain, $keys);
        return self::newModelInstance($attrs + $values);
    }
}
