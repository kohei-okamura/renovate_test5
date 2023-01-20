/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsProjectServiceCategory } from '@zinger/enums/lib/ltcs-project-service-category'
import { range } from '@zinger/helpers'
import { DateLike } from '~/models/date'
import { LtcsProjectServiceMenu, LtcsProjectServiceMenuId } from '~/models/ltcs-project-service-menu'
import { CreateStubs } from '~~/stubs/index'

const now: DateLike = '2020-09-28T12:34:56.789+0900'

const stub = (
  id: LtcsProjectServiceMenuId,
  category: LtcsProjectServiceCategory,
  name: string
): LtcsProjectServiceMenu => ({
  id,
  category,
  name,
  displayName: name,
  sortOrder: id,
  createdAt: now
})

const xs = [
  stub(1, LtcsProjectServiceCategory.physicalCare, 'サービス準備・記録等'),
  stub(2, LtcsProjectServiceCategory.physicalCare, '排泄介助'),
  stub(3, LtcsProjectServiceCategory.physicalCare, '食事介助'),
  stub(4, LtcsProjectServiceCategory.physicalCare, '専門的配慮をもって行う調理'),
  stub(5, LtcsProjectServiceCategory.physicalCare, '清拭（全身清拭）'),
  stub(6, LtcsProjectServiceCategory.physicalCare, '部分浴'),
  stub(7, LtcsProjectServiceCategory.physicalCare, '全身浴'),
  stub(8, LtcsProjectServiceCategory.physicalCare, '身体整容'),
  stub(9, LtcsProjectServiceCategory.physicalCare, '更衣介助'),
  stub(10, LtcsProjectServiceCategory.physicalCare, '体位交換'),
  stub(11, LtcsProjectServiceCategory.physicalCare, '移動・移乗介助'),
  stub(12, LtcsProjectServiceCategory.physicalCare, '通院・外出介助'),
  stub(13, LtcsProjectServiceCategory.physicalCare, '起床介助'),
  stub(14, LtcsProjectServiceCategory.physicalCare, '就寝介助'),
  stub(15, LtcsProjectServiceCategory.physicalCare, '服薬介助'),
  stub(16, LtcsProjectServiceCategory.physicalCare, '自立生活支援・重度化防止のための見守り的援助'),
  stub(17, LtcsProjectServiceCategory.housework, 'サービス準備・記録等'),
  stub(18, LtcsProjectServiceCategory.housework, '掃除'),
  stub(19, LtcsProjectServiceCategory.housework, '洗濯'),
  stub(20, LtcsProjectServiceCategory.housework, 'ベッドメイク'),
  stub(21, LtcsProjectServiceCategory.housework, '衣類の整理・被服の補修'),
  stub(22, LtcsProjectServiceCategory.housework, '一般的な調理、配下膳'),
  stub(23, LtcsProjectServiceCategory.housework, '買い物・薬の受け取り')
]

export const LTCS_PROJECT_SERVICE_MENU_ID_MAX = Math.max(...xs.map(x => x.id))
export const LTCS_PROJECT_SERVICE_MENU_ID_MIN = Math.min(...xs.map(x => x.id))

export function createLtcsProjectServiceMenuStub (id: LtcsProjectServiceMenuId): LtcsProjectServiceMenu {
  return xs[id - 1]
}

export const createLtcsProjectServiceMenuStubs: CreateStubs<LtcsProjectServiceMenu> = (
  n = LTCS_PROJECT_SERVICE_MENU_ID_MAX,
  skip = 0
) => {
  const start = LTCS_PROJECT_SERVICE_MENU_ID_MIN + skip
  const end = Math.min(start + n - 1, LTCS_PROJECT_SERVICE_MENU_ID_MAX)
  return range(start, end).map(createLtcsProjectServiceMenuStub)
}
