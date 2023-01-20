/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ContractId } from '~/models/contract'
import { DateLike } from '~/models/date'
import { LtcsProjectProgram } from '~/models/ltcs-project-program'
import { Objective } from '~/models/objective'
import { OfficeId } from '~/models/office'
import { StaffId } from '~/models/staff'
import { UserId } from '~/models/user'

/**
 * 計画 ID.
 */
export type LtcsProjectId = number

/**
 * 介護保険サービス：計画.
 */
export type LtcsProject = Readonly<{
  /** 計画 ID */
  id: LtcsProjectId

  /** 契約 ID */
  contractId: ContractId

  /** 事業所 ID */
  officeId: OfficeId

  /** 利用者 ID */
  userId: UserId

  /** 作成者 ID */
  staffId: StaffId

  /** 作成日 */
  writtenOn: DateLike

  /** 適用日 */
  effectivatedOn: DateLike

  /** ご本人の希望 */
  requestFromUser: string

  /** ご家族の希望 */
  requestFromFamily: string

  /** 解決すべき課題 */
  problem: string

  /** 長期目標 */
  longTermObjective: Objective

  /** 短期目標 */
  shortTermObjective: Objective

  /** 週間サービス計画 */
  programs: LtcsProjectProgram[]

  /** 有効フラグ */
  isEnabled: boolean

  /** バージョン */
  version: number

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
