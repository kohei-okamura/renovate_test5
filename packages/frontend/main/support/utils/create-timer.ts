/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
export type IntervalUnit = 'hour' | 'minute' | 'second' | 'millisecond'

export type Interval = {
  time?: number
  unit?: IntervalUnit
}

type StartOptions = {
  immediately?: boolean
}

export type Timer = {
  start: (options?: StartOptions) => void
  stop: () => void
  reset: (newInterval?: Interval) => void
}

const timeToMilliseconds = (time: number, unit: IntervalUnit) => {
  switch (unit) {
    case 'hour':
      return time * 3600000 // 1000 * 60 * 60
    case 'minute':
      return time * 60000 // 1000 * 60
    case 'second':
      return time * 1000
    case 'millisecond':
      return time
  }
}

/**
 * 関数を定期的に実行するためのタイマーを作成する
 *
 * @param f 定期的に実行する関数
 * @param [interval] 実行間隔
 * @param [interval.time] 実行間隔の時間 (デフォルト: 30)
 * @param [interval.unit] 実行間隔の時間の単位 (デフォルト: 分)
 * @constructor
 */
export const createTimer = (f: () => void, interval?: Interval): Timer => {
  let timer: ReturnType<typeof setTimeout> | null = null
  let currentTime: number = interval?.time ?? 30
  let currentUnit: IntervalUnit = interval?.unit ?? 'minute'
  let currentMilliseconds: number = timeToMilliseconds(currentTime, currentUnit)

  const execute = () => {
    f()
    timer = setTimeout(execute, currentMilliseconds)
  }

  /**
   * タイマーを開始する
   * すでに開始している場合は何もしない
   */
  const start = (options: StartOptions = {}) => {
    if (!timer) {
      options.immediately ? execute() : setTimeout(execute, currentMilliseconds)
    }
  }

  /**
   * タイマーを止める
   */
  const stop = () => {
    if (timer) {
      clearTimeout(timer)
      timer = null
    }
  }

  /**
   * タイマーをリセットする
   * リセット時に関数の実行間隔を変更することもできる
   *
   * @param newInterval 新しい実行間隔
   * @param newInterval.time 実行間隔の時間
   * @param newInterval.unit 実行間隔の時間の単位
   */
  const reset = (newInterval?: Interval) => {
    stop()
    if (newInterval) {
      currentTime = newInterval.time ?? currentTime
      currentUnit = newInterval.unit ?? currentUnit
      currentMilliseconds = timeToMilliseconds(currentTime, currentUnit)
    }
    start()
  }

  return { start, stop, reset }
}
