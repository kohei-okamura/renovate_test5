/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Activity } from '@zinger/enums/lib/activity'
import { Task } from '@zinger/enums/lib/task'
import { RefOrValue, unref } from '~/support/reactive'

type TaskLike = RefOrValue<Task | undefined> | undefined

/**
 * 指定された {@link Task} が指定された値の一覧（配列）に含まれるかどうかを判定する.
 */
export function is (x: TaskLike, tasks: Task[]): boolean {
  const task = unref(x)
  return task !== undefined && tasks.includes(task)
}

/**
 * 指定された {@link Task} が「地域生活支援事業」かどうかを判定する.
 */
export function isCommunityLifeSupport (x: TaskLike): boolean {
  return is(x, [Task.commAccompanyWithPhysicalCare, Task.commAccompany])
}

/**
 * 指定された {@link Task} が「総合事業」かどうかを判定する.
 */
export function isComprehensiveService (x: TaskLike): boolean {
  return unref(x) === Task.comprehensive
}

/**
 * 指定された {@link Task} が「障害福祉サービス（居宅介護）」かどうかを判定する.
 */
export function isHomeHelpService (x: TaskLike): boolean {
  return is(x, [Task.dwsPhysicalCare, Task.dwsHousework, Task.dwsAccompanyWithPhysicalCare, Task.dwsAccompany])
}

/**
 * 指定された {@link Task} が「介護保険サービス（訪問介護）」かどうかを判定する.
 */
export function isLongTermCareService (x: TaskLike): boolean {
  return is(x, [Task.ltcsPhysicalCare, Task.ltcsHousework, Task.ltcsPhysicalCareAndHousework])
}

/**
 * 指定された {@link Task} が「その他」かどうかを判定する.
 */
export function isOthers (x: TaskLike): boolean {
  return is(x, [Task.officeWork, Task.sales, Task.meeting, Task.other])
}

/**
 * 指定された {@link Task} が訪問を伴う＝利用者を対象とするかどうかを判定する.
 */
export function isVisiting (x: TaskLike) {
  const task = unref(x)
  return task !== undefined && !isOthers(task)
}

/**
 * 指定された {@link Task} が「障害福祉サービス（重度訪問介護）」かどうかを判定する.
 */
export function isVisitingCareForPwsd (x: TaskLike): boolean {
  return unref(x) === Task.dwsVisitingCareForPwsd
}

/**
 * 指定された {@link Task} が「障害福祉サービス」かどうかを判定する.
 */
export function isDisabilitiesWelfareService (x: TaskLike): boolean {
  const task = unref(x)
  return isHomeHelpService(task) || isVisitingCareForPwsd(task)
}

/**
 * 指定された {@link Task} において「研修」が有効かどうかを判定する.
 */
export function isTrainingEnabled (x: TaskLike): boolean {
  const task = unref(x)
  return isCommunityLifeSupport(task) ||
    isComprehensiveService(task) ||
    isHomeHelpService(task) ||
    isDisabilitiesWelfareService(task)
}

/**
 * {@link Task} を {@link Activity} の配列に変換する.
 */
export function taskToActivity (x: TaskLike): Activity[] {
  const task = unref(x)
  switch (task) {
    case Task.dwsPhysicalCare:
      return [Activity.dwsPhysicalCare]
    case Task.dwsHousework:
      return [Activity.dwsHousework]
    case Task.dwsAccompanyWithPhysicalCare:
      return [Activity.dwsAccompanyWithPhysicalCare]
    case Task.dwsAccompany:
      return [Activity.dwsAccompany]
    case Task.dwsVisitingCareForPwsd:
      return [Activity.dwsVisitingCareForPwsd]
    case Task.ltcsPhysicalCare:
      return [Activity.ltcsPhysicalCare]
    case Task.ltcsHousework:
      return [Activity.ltcsHousework]
    case Task.ltcsPhysicalCareAndHousework:
      return [Activity.ltcsPhysicalCare, Activity.ltcsHousework]
    case Task.comprehensive:
      return [Activity.comprehensive]
    case Task.commAccompanyWithPhysicalCare:
      return [Activity.commAccompanyWithPhysicalCare]
    case Task.commAccompany:
      return [Activity.commAccompany]
    case Task.ownExpense:
      return [Activity.ownExpense]
    case Task.fieldwork:
      return [Activity.fieldwork]
    case Task.assessment:
      return [Activity.assessment]
    case Task.visit:
      return [Activity.visit]
    case Task.officeWork:
      return [Activity.officeWork]
    case Task.sales:
      return [Activity.sales]
    case Task.meeting:
      return [Activity.meeting]
    case Task.other:
      return [Activity.other]
    default:
      throw new Error(`Unknown task given: ${task}`)
  }
}
