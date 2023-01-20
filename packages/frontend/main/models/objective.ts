/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { TimeRange } from '~/models/range'

/**
 * 期間目標.
 */
export type Objective = Readonly<{
  /** 期間 */
  term: TimeRange

  /** 目標 */
  text: string
}>
