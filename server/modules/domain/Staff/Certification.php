<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Staff;

use Domain\Enum;

/**
 * 資格.
 *
 * @method static Certification suctionTraining() 喀痰吸引研修
 * @method static Certification visitingCareWorkerForPwsd() 重度訪問介護従業者
 * @method static Certification novice() 初任者研修
 * @method static Certification practitioner() 実務者研修
 * @method static Certification certifiedCareWorker() 介護福祉士
 * @method static Certification careManager() ケアマネージャー
 * @method static Certification practicalNurse() 准看護師
 * @method static Certification registeredNurse() 正看護師
 * @method static Certification physicalTherapist() 理学療法士
 * @method static Certification occupationalTherapist() 作業療法士
 * @method static Certification driversLicense() 普通自動車免許
 * @method static Certification socialWorkOfficer() 社会福祉主事任用資格
 * @method static Certification welfareEquipmentSpecialist() 福祉用具専門相談員
 * @method static Certification speechLanguageHearingTherapist() 言語聴覚士
 * @method static Certification masseur() あん摩マッサージ指圧師
 * @method static Certification acupuncturist() はり師
 * @method static Certification moxibutionist() きゅう師
 */
final class Certification extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'suctionTraining' => 1,
        'visitingCareWorkerForPwsd' => 2,
        'novice' => 3,
        'practitioner' => 4,
        'certifiedCareWorker' => 5,
        'careManager' => 6,
        'practicalNurse' => 7,
        'registeredNurse' => 8,
        'physicalTherapist' => 9,
        'occupationalTherapist' => 10,
        'driversLicense' => 11,
        'socialWorkOfficer' => 12,
        'welfareEquipmentSpecialist' => 13,
        'speechLanguageHearingTherapist' => 14,
        'masseur' => 15,
        'acupuncturist' => 16,
        'moxibutionist' => 17,
    ];
}
