<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\DwsCertification\DwsCertificationAgreementType;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Lib\Exceptions\InvalidArgumentException;

/**
 * Support functions for {@link \Domain\Billing\DwsGrantedServiceCode}.
 *
 * @mixin \Domain\Billing\DwsGrantedServiceCode
 */
trait DwsGrantedServiceCodeSupport
{
    /**
     * 障害福祉サービス受給者証の情報からインスタンスを生成する.
     *
     * @param \Domain\DwsCertification\DwsCertificationAgreementType $type
     * @return static
     */
    public static function fromDwsCertificationAgreementType(DwsCertificationAgreementType $type): self
    {
        switch ($type) {
            case DwsCertificationAgreementType::physicalCare():
                return DwsGrantedServiceCode::physicalCare();
            case DwsCertificationAgreementType::housework():
                return DwsGrantedServiceCode::housework();
            case DwsCertificationAgreementType::accompanyWithPhysicalCare():
                return DwsGrantedServiceCode::accompanyWithPhysicalCare();
            case DwsCertificationAgreementType::accompany():
                return DwsGrantedServiceCode::accompany();
            case DwsCertificationAgreementType::visitingCareForPwsd1():
                return DwsGrantedServiceCode::visitingCareForPwsd1();
            case DwsCertificationAgreementType::visitingCareForPwsd2():
                return DwsGrantedServiceCode::visitingCareForPwsd2();
            case DwsCertificationAgreementType::visitingCareForPwsd3():
                return DwsGrantedServiceCode::visitingCareForPwsd3();
            case DwsCertificationAgreementType::outingSupportForPwsd():
                return DwsGrantedServiceCode::outingSupportForPwsd();
            default:
                // @codeCoverageIgnoreStart
                // 追加された場合に漏れをチェックする
                throw new InvalidArgumentException("DwsGrantedServiceCode for DwsCertificationAgreementType({$type}) is not found");
                // @codeCoverageIgnoreEnd
        }
    }

    /**
     * 決定サービスコードからサービス種類コードを導出する.
     *
     * @return \Domain\Billing\DwsServiceDivisionCode
     */
    public function toDwsServiceDivisionCode(): DwsServiceDivisionCode
    {
        switch ($this) {
            case self::physicalCare():
            case self::housework():
            case self::accompanyWithPhysicalCare():
            case self::accompany():
                return DwsServiceDivisionCode::homeHelpService();
            case self::visitingCareForPwsd1():
            case self::visitingCareForPwsd2():
            case self::visitingCareForPwsd3():
            case self::outingSupportForPwsd():
                return DwsServiceDivisionCode::visitingCareForPwsd();
            default:
                throw new InvalidArgumentException('Unexpected DwsGrantedServiceCode value');
        }
    }

    /**
     * サービスコード区分からインスタンスを生成する.
     *
     * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category
     * @return \Domain\Billing\DwsGrantedServiceCode
     */
    public static function fromDwsServiceCodeCategory(DwsServiceCodeCategory $category)
    {
        switch ($category) {
            case DwsServiceCodeCategory::physicalCare():
                return DwsGrantedServiceCode::physicalCare();
            case DwsServiceCodeCategory::housework():
                return DwsGrantedServiceCode::housework();
            case DwsServiceCodeCategory::accompanyWithPhysicalCare():
                return DwsGrantedServiceCode::accompanyWithPhysicalCare();
            case DwsServiceCodeCategory::accompany():
                return DwsGrantedServiceCode::accompany();
            case DwsServiceCodeCategory::visitingCareForPwsd1():
                return DwsGrantedServiceCode::visitingCareForPwsd1();
            case DwsServiceCodeCategory::visitingCareForPwsd2():
                return DwsGrantedServiceCode::visitingCareForPwsd2();
            case DwsServiceCodeCategory::visitingCareForPwsd3():
                return DwsGrantedServiceCode::visitingCareForPwsd3();
            case DwsServiceCodeCategory::outingSupportForPwsd():
                return DwsGrantedServiceCode::outingSupportForPwsd();
            default:
                throw new InvalidArgumentException("DwsGrantedServiceCode for DwsServiceCodeCategory({$category}) is not found");
        }
    }
}
