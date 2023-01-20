/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsProvisionReportItem } from '~/models/dws-provision-report-item'
import { Schedule } from '~/models/schedule'
import { $datetime } from '~/services/datetime-service'

type Minutes = DwsProvisionReportItem['movingDurationMinutes']

/**
 * 小数点以下を指定した単位毎に切り上げる
 *
 * @param value
 * @param step 切り上げる単位（default: 0.5））
 * @example
 * ceilByStep(1.2);
 * //=> returns 1.5
 * @example
 * ceilByStep(1.23, 0.25);
 * //=> returns 1.25
 */
const ceilByStep = (value: number, step?: number) => {
  const inv = 1.0 / (step ?? 0.5)
  return Math.ceil(value * inv) / inv
}

/**
 * 移動介護分数を時間単位に変換する.
 * 分数を時間数に変換してから小数第一位までの文字列にして返す、分数が 0 以下の場合は空文字を返す.
 * ただし180分より多い場合は240分として扱う.
 *
 * @param minutes
 * @param digits 小数点以下の桁数（default: 1）
 * @example
 * @example
 * getMovingDurationHours(81);
 * //=> returns 1.5 ※小数第一位を 5 単位で切り上げ
 * getMovingDurationHours(85, 2);
 * //=> returns 1.45 ※小数第二位を 5 単位で切り上げ
 */
export const getMovingDurationHours = (minutes: Minutes, digits = 1) => {
  const d = digits < 0 ? 0 : digits
  if (minutes > 0) {
    const adjustMinutes = minutes > 180 ? 240 : minutes
    return ceilByStep(adjustMinutes / 60, 5 / Math.pow(10, d)).toFixed(d)
  } else {
    return ''
  }
}

/**
 * 稼働時間数を計算する.
 *
 * @param object { start: 時間文字列, end: 時間文字列 }
 * @param digits 小数点以下の桁数（default: 1）
 * @example
 * calculateWorkingHours({ start: '2021-02-15T23:00:00+0900', end: '2021-02-16T01:00:00+0900' }, 2);
 * //=> returns 2.00
 * @example
 * calculateWorkingHours({ start: '2021-02-15T22:39:00+0900', end: '2021-02-16T01:00:00+0900' });
 * //=> returns 2.4 ※端数は四捨五入
 */
export const calculateWorkingHours = ({ start, end }: Omit<Schedule, 'date'>, digits = 1) => {
  const future = $datetime.parse(end)
  const past = $datetime.parse(start)
  return (Math.fround(future.diff(past, 'hours').hours * 10) / 10).toFixed(digits)
}
