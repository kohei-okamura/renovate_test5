/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ContractStatus } from '@zinger/enums/lib/contract-status'
import { DwsServiceDivisionCode } from '@zinger/enums/lib/dws-service-division-code'
import { LtcsExpiredReason } from '@zinger/enums/lib/ltcs-expired-reason'
import { ServiceSegment } from '@zinger/enums/lib/service-segment'
import { DateLike, DateString } from '~/models/date'
import { OfficeId } from '~/models/office'
import { Range } from '~/models/range'
import { UserId } from '~/models/user'

/**
 * 契約 ID.
 */
export type ContractId = number

/**
 * 契約期間.
 */
export type ContractPeriod = Range<DateString | undefined>

/**
 * 契約.
 */
export type Contract = Readonly<{
  /** 契約 ID */
  id: ContractId

  /** 利用者 ID */
  userId: UserId

  /** 事業所 ID */
  officeId: OfficeId

  /** 事業領域 */
  serviceSegment: ServiceSegment

  /** 状態 */
  status: ContractStatus

  /** 契約日 */
  contractedOn: DateLike | undefined

  /** 解約日 */
  terminatedOn: DateLike | undefined

  /** 障害福祉サービス提供期間 */
  dwsPeriods: Record<DwsServiceDivisionCode, ContractPeriod>

  /** 介護保険サービス提供期間 */
  ltcsPeriod: ContractPeriod

  /** 介護保険サービス中止理由 */
  expiredReason: LtcsExpiredReason

  /** 備考 */
  note: string

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
