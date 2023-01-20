/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  suctionTraining: 1,
  visitingCareWorkerForPwsd: 2,
  novice: 3,
  practitioner: 4,
  certifiedCareWorker: 5,
  careManager: 6,
  practicalNurse: 7,
  registeredNurse: 8,
  physicalTherapist: 9,
  occupationalTherapist: 10,
  driversLicense: 11,
  socialWorkOfficer: 12,
  welfareEquipmentSpecialist: 13,
  speechLanguageHearingTherapist: 14,
  masseur: 15,
  acupuncturist: 16,
  moxibutionist: 17
} as const

/**
 * 資格.
 */
export type Certification = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const Certification = createEnumerable($$, [
  [$$.suctionTraining, '喀痰吸引研修'],
  [$$.visitingCareWorkerForPwsd, '重度訪問介護従業者'],
  [$$.novice, '初任者研修'],
  [$$.practitioner, '実務者研修'],
  [$$.certifiedCareWorker, '介護福祉士'],
  [$$.careManager, 'ケアマネージャー'],
  [$$.practicalNurse, '准看護師'],
  [$$.registeredNurse, '正看護師'],
  [$$.physicalTherapist, '理学療法士'],
  [$$.occupationalTherapist, '作業療法士'],
  [$$.driversLicense, '普通自動車免許'],
  [$$.socialWorkOfficer, '社会福祉主事任用資格'],
  [$$.welfareEquipmentSpecialist, '福祉用具専門相談員'],
  [$$.speechLanguageHearingTherapist, '言語聴覚士'],
  [$$.masseur, 'あん摩マッサージ指圧師'],
  [$$.acupuncturist, 'はり師'],
  [$$.moxibutionist, 'きゅう師']
])

export const resolveCertification = Certification.resolve
