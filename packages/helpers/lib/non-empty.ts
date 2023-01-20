/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { isEmpty } from './is-empty'

type NonEmpty<T> = T extends null | undefined | '' ? never : T

/**
 * 指定された値が「空」でないかどうかを判定する.
 */
export const nonEmpty = <T> (x: T): x is NonEmpty<T> => !isEmpty(x)
