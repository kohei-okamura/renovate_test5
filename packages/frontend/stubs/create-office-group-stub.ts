/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DateLike } from '~/models/date'
import { OfficeGroup, OfficeGroupId } from '~/models/office-group'

function stub (id: OfficeGroupId, name: string, parentOfficeGroupId?: OfficeGroupId): OfficeGroup {
  const now: DateLike = '2018-05-17T12:34:56.789+0900'
  return {
    id,
    name,
    parentOfficeGroupId,
    sortOrder: id,
    createdAt: now,
    updatedAt: now
  }
}

const xs: OfficeGroup[] = [
  stub(10, '北海道ブロック'),
  stub(11, '北海道', 10),
  stub(20, '東北ブロック'),
  stub(21, '青森', 20),
  stub(22, '岩手', 20),
  stub(23, '宮城', 20),
  stub(24, '秋田', 20),
  stub(25, '山形', 20),
  stub(26, '福島', 20),
  stub(30, '関東ブロック'),
  stub(31, '茨城', 30),
  stub(32, '栃木', 30),
  stub(33, '群馬', 30),
  stub(34, '埼玉', 30),
  stub(35, '千葉', 30),
  stub(36, '東京', 30),
  stub(37, '神奈川', 30),
  stub(40, '甲信越ブロック'),
  stub(41, '新潟', 40),
  stub(42, '富山', 40),
  stub(43, '石川', 40),
  stub(44, '福井', 40),
  stub(45, '山梨', 40),
  stub(46, '長野', 40),
  stub(50, '中部ブロック'),
  stub(51, '岐阜', 50),
  stub(52, '静岡', 50),
  stub(53, '愛知', 50),
  stub(54, '三重', 50),
  stub(60, '関西ブロック'),
  stub(61, '滋賀', 60),
  stub(62, '京都', 60),
  stub(63, '大阪', 60),
  stub(64, '兵庫', 60),
  stub(65, '奈良', 60),
  stub(66, '和歌山', 60),
  stub(70, '中四国ブロック'),
  stub(71, '鳥取', 70),
  stub(72, '島根', 70),
  stub(73, '岡山', 70),
  stub(74, '広島', 70),
  stub(75, '山口', 70),
  stub(76, '徳島', 70),
  stub(77, '香川', 70),
  stub(78, '愛媛', 70),
  stub(79, '高知', 70),
  stub(80, '九州ブロック'),
  stub(81, '福岡', 80),
  stub(82, '佐賀', 80),
  stub(83, '長崎', 80),
  stub(84, '熊本', 80),
  stub(85, '大分', 80),
  stub(86, '宮崎', 80),
  stub(87, '鹿児島', 80),
  stub(88, '沖縄', 80)
]
export const OFFICE_GROUP_STUB_COUNT = xs.length
export const OFFICE_GROUP_IDS = xs.map(x => x.id)
export const OFFICE_GROUP_ID_MAX = Math.max(...OFFICE_GROUP_IDS)
export const OFFICE_GROUP_ID_MIN = Math.min(...OFFICE_GROUP_IDS)

export function createOfficeGroupStub (id: OfficeGroupId | undefined = OFFICE_GROUP_ID_MIN): OfficeGroup | undefined {
  return xs.find(x => x.id === id)
}

export function createOfficeGroupStubs (): OfficeGroup[] {
  return xs
}
