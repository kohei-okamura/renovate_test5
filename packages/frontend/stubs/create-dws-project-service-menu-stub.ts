/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsProjectServiceCategory } from '@zinger/enums/lib/dws-project-service-category'
import { range } from '@zinger/helpers'
import { DateLike } from '~/models/date'
import { DwsProjectServiceMenu, DwsProjectServiceMenuId } from '~/models/dws-project-service-menu'
import { CreateStubs } from '~~/stubs/index'

const now: DateLike = '2020-09-28T12:34:56.789+0900'

const stub = (
  id: DwsProjectServiceMenuId,
  category: DwsProjectServiceCategory,
  name: string
): DwsProjectServiceMenu => ({
  id,
  category,
  name,
  displayName: name,
  sortOrder: id,
  createdAt: now
})

const xs = [
  stub(1, DwsProjectServiceCategory.physicalCare, 'サービス準備・記録等'),
  stub(2, DwsProjectServiceCategory.physicalCare, '排泄介助'),
  stub(3, DwsProjectServiceCategory.physicalCare, '食事介助'),
  stub(4, DwsProjectServiceCategory.physicalCare, '専門的配慮をもって行う調理'),
  stub(5, DwsProjectServiceCategory.physicalCare, '清拭（全身清拭）'),
  stub(6, DwsProjectServiceCategory.physicalCare, '部分浴'),
  stub(7, DwsProjectServiceCategory.physicalCare, '全身浴'),
  stub(8, DwsProjectServiceCategory.physicalCare, '身体整容'),
  stub(9, DwsProjectServiceCategory.physicalCare, '更衣介助'),
  stub(10, DwsProjectServiceCategory.physicalCare, '体位交換'),
  stub(11, DwsProjectServiceCategory.physicalCare, '移動・移乗介助'),
  stub(12, DwsProjectServiceCategory.physicalCare, '通院・外出介助'),
  stub(13, DwsProjectServiceCategory.physicalCare, '起床介助'),
  stub(14, DwsProjectServiceCategory.physicalCare, '就寝介助'),
  stub(15, DwsProjectServiceCategory.physicalCare, '服薬介助'),
  stub(16, DwsProjectServiceCategory.physicalCare, '自立生活支援・重度化防止のための見守り的援助'),
  stub(17, DwsProjectServiceCategory.housework, 'サービス準備・記録等'),
  stub(18, DwsProjectServiceCategory.housework, '掃除'),
  stub(19, DwsProjectServiceCategory.housework, '洗濯'),
  stub(20, DwsProjectServiceCategory.housework, 'ベッドメイク'),
  stub(21, DwsProjectServiceCategory.housework, '衣類の整理・被服の補修'),
  stub(22, DwsProjectServiceCategory.housework, '一般的な調理、配下膳'),
  stub(23, DwsProjectServiceCategory.housework, '買い物・薬の受け取り'),
  stub(24, DwsProjectServiceCategory.accompanyWithPhysicalCare, '通院等介助（身体を伴う）'),
  stub(25, DwsProjectServiceCategory.accompany, '通院等介助（身体を伴わない）'),
  stub(26, DwsProjectServiceCategory.visitingCareForPwsd, 'サービス準備・記録等'),
  stub(27, DwsProjectServiceCategory.visitingCareForPwsd, '排泄介助'),
  stub(28, DwsProjectServiceCategory.visitingCareForPwsd, '食事介助'),
  stub(29, DwsProjectServiceCategory.visitingCareForPwsd, '入浴介助'),
  stub(30, DwsProjectServiceCategory.visitingCareForPwsd, '清拭介助'),
  stub(31, DwsProjectServiceCategory.visitingCareForPwsd, '移乗介助'),
  stub(32, DwsProjectServiceCategory.visitingCareForPwsd, '外出介助'),
  stub(33, DwsProjectServiceCategory.visitingCareForPwsd, '外出準備介助'),
  stub(34, DwsProjectServiceCategory.visitingCareForPwsd, '帰宅受入介助'),
  stub(35, DwsProjectServiceCategory.visitingCareForPwsd, 'その他移動・歩行介助'),
  stub(36, DwsProjectServiceCategory.visitingCareForPwsd, '起床介助'),
  stub(37, DwsProjectServiceCategory.visitingCareForPwsd, '就寝介助'),
  stub(38, DwsProjectServiceCategory.visitingCareForPwsd, '体位変換'),
  stub(39, DwsProjectServiceCategory.visitingCareForPwsd, 'その他起床・就寝介助'),
  stub(40, DwsProjectServiceCategory.visitingCareForPwsd, '服薬'),
  stub(41, DwsProjectServiceCategory.visitingCareForPwsd, '服薬管理'),
  stub(42, DwsProjectServiceCategory.visitingCareForPwsd, '医療的ケア'),
  stub(43, DwsProjectServiceCategory.visitingCareForPwsd, '自立への声かけと見守り'),
  stub(44, DwsProjectServiceCategory.visitingCareForPwsd, '意欲・関心の引き出し'),
  stub(45, DwsProjectServiceCategory.visitingCareForPwsd, '共に行う家事（掃除）'),
  stub(46, DwsProjectServiceCategory.visitingCareForPwsd, '共に行う家事（洗濯）'),
  stub(47, DwsProjectServiceCategory.visitingCareForPwsd, '共に行う家事（調理）'),
  stub(48, DwsProjectServiceCategory.visitingCareForPwsd, 'その他自立支援'),
  stub(49, DwsProjectServiceCategory.visitingCareForPwsd, '掃除'),
  stub(50, DwsProjectServiceCategory.visitingCareForPwsd, '洗濯'),
  stub(51, DwsProjectServiceCategory.visitingCareForPwsd, '居室整理（寝具の手入れ）'),
  stub(52, DwsProjectServiceCategory.visitingCareForPwsd, '居室整理（衣類）'),
  stub(53, DwsProjectServiceCategory.visitingCareForPwsd, '調理配下膳'),
  stub(54, DwsProjectServiceCategory.visitingCareForPwsd, '買い物'),
  stub(55, DwsProjectServiceCategory.visitingCareForPwsd, 'コミュニケーション支援'),
  stub(56, DwsProjectServiceCategory.visitingCareForPwsd, '生活等に関する相談・助言'),
  stub(57, DwsProjectServiceCategory.visitingCareForPwsd, '常時付添いを必要とする見守り的援助'),
  stub(58, DwsProjectServiceCategory.visitingCareForPwsd, 'その他')
]

export const DWS_PROJECT_SERVICE_MENU_ID_MAX = Math.max(...xs.map(x => x.id))
export const DWS_PROJECT_SERVICE_MENU_ID_MIN = Math.min(...xs.map(x => x.id))

export function createDwsProjectServiceMenuStub (id: DwsProjectServiceMenuId): DwsProjectServiceMenu {
  return xs[id - 1]
}

export const createDwsProjectServiceMenuStubs: CreateStubs<DwsProjectServiceMenu> = (
  n = DWS_PROJECT_SERVICE_MENU_ID_MAX,
  skip = 0
) => {
  const start = DWS_PROJECT_SERVICE_MENU_ID_MIN + skip
  const end = Math.min(start + n - 1, DWS_PROJECT_SERVICE_MENU_ID_MAX)
  return range(start, end).map(createDwsProjectServiceMenuStub)
}
