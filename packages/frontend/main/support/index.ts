/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { AxiosError } from 'axios'

/**
 * 指定された値が {@link AxiosError} かどうかを判定する.
 */
export function isAxiosError<T = any> (x: any): x is AxiosError<T> {
  return x.isAxiosError ?? false
}

/**
 * 指定したミリ秒待機する.
 */
export const wait = (ms: number): Promise<void> => new Promise(resolve => setTimeout(resolve, ms))
