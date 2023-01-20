/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ServiceOption } from '@zinger/enums/lib/service-option'
import { Task } from '@zinger/enums/lib/task'
import { Assignee } from '~/models/assignee'
import { ContractId } from '~/models/contract'
import { DateLike } from '~/models/date'
import { Duration } from '~/models/duration'
import { OfficeId } from '~/models/office'
import { Schedule } from '~/models/schedule'
import { ShiftId } from '~/models/shift'
import { StaffId } from '~/models/staff'
import { UserId } from '~/models/user'

/**
 * 勤務実績 ID.
 */
export type AttendanceId = number

/**
 * 勤務実績.
 */
export type Attendance = Readonly<{
  /** 勤務実績 ID */
  id: AttendanceId

  /** 勤務シフト ID */
  shiftId: ShiftId | undefined

  /** 契約 ID */
  contractId: ContractId | undefined

  /** 事業所 ID */
  officeId: OfficeId

  /** 利用者 ID */
  userId: UserId | undefined

  /** 管理スタッフ ID */
  assignerId: StaffId

  /** 予実区分 */
  task: Task

  /** サービスコード */
  serviceCode: string

  /** 頭数 */
  headcount: number

  /** 担当スタッフ */
  assignees: Assignee[]

  /** スケジュール */
  schedule: Schedule

  /** 予実所要時間 */
  durations: Duration[]

  /** オプション */
  options: ServiceOption[]

  /** 備考 */
  note: string

  /** 確定フラグ */
  isConfirmed: boolean

  /** キャンセルフラグ */
  isCanceled: boolean

  /** キャンセル理由 */
  reason: string | undefined

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
