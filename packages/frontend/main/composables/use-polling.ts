/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import { wait } from '~/support'

type PollingParams<T> = {
  init: () => T | Promise<T>
  test: (x: T) => boolean
  poll: (x: T) => T | Promise<T>
  intervalMilliseconds?: number | ((retry: number) => number)
  maxRetry?: number
}

export type CancelPolling = () => void

export type StartPolling = {
  <T> (params: PollingParams<T>): Promise<T | false>
}

type PollingFunctions = {
  cancelPolling: CancelPolling
  startPolling: StartPolling
}

const DEFAULT_INTERVAL_MILLISECONDS = 2000

const getIntervalMilliseconds = ({ intervalMilliseconds }: PollingParams<any>, retry: number): number => {
  if (intervalMilliseconds === undefined) {
    return DEFAULT_INTERVAL_MILLISECONDS
  } else if (typeof intervalMilliseconds === 'function') {
    return intervalMilliseconds(retry)
  } else {
    return intervalMilliseconds
  }
}

const canRetry = (retry: number, maxRetry: number | undefined) => maxRetry === undefined || retry < maxRetry

export function usePolling (): PollingFunctions {
  const data = reactive({
    canceled: false
  })
  const cancelPolling = () => {
    data.canceled = true
  }
  const doPolling = async <T> (z: T, params: PollingParams<T>, retry: number = 0): Promise<T | false> => {
    const ms = getIntervalMilliseconds(params, retry)
    await wait(ms)
    const x = await params.poll(z)
    if (params.test(x)) {
      return x
    } else if (data.canceled || !canRetry(retry, params.maxRetry)) {
      return false
    } else {
      return doPolling(x, params, retry + 1)
    }
  }
  const startPolling: StartPolling = async params => {
    data.canceled = false
    const x = await params.init()
    return params.test(x) ? x : doPolling(x, params)
  }
  return {
    cancelPolling,
    startPolling
  }
}
