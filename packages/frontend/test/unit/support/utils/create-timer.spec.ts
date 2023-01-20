/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createTimer, Interval, Timer } from '~/support/utils/create-timer'

type WithTimerCallback = (timer: Timer, f: jest.Mock) => void
type WithTimer = (callback: WithTimerCallback, interval?: Interval) => void

describe('support/utils/create-timer', () => {
  const withTimer: WithTimer = (callback, interval = { time: 1000, unit: 'millisecond' }) => {
    const f = jest.fn()
    const timer = createTimer(f, interval)
    callback(timer, f)
    timer.stop()
  }

  beforeAll(() => {
    jest.useFakeTimers()
  })

  afterEach(() => {
    jest.clearAllTimers()
  })

  it('should not call the function immediately when the timer created', () => withTimer((_, f) => {
    expect(f).not.toBeCalled()
  }))

  describe('start', () => {
    const intervals = [
      ['millisecond', 1000, (x: number) => x],
      ['second', 100, (x: number) => x * 1000],
      ['minute', 10, (x: number) => x * 1000 * 60],
      ['hour', 1, (x: number) => x * 1000 * 60 * 60]
    ] as const

    it('should call the function immediately when immediately option is true', () => withTimer((timer, f) => {
      timer.start({ immediately: true })
      expect(f).toHaveBeenCalledTimes(1)
    }))

    it('should not call the function immediately when immediately option is false', () => withTimer((timer, f) => {
      timer.start({ immediately: false })
      expect(f).not.toHaveBeenCalled()
    }))

    it('should not call the function immediately when no options given', () => withTimer((timer, f) => {
      timer.start()
      expect(f).not.toHaveBeenCalled()
    }))

    it.each(intervals)('should call the function after the specified %ss has elapsed', (unit, time, g) => withTimer(
      (timer, f) => {
        timer.start()
        expect(f).not.toHaveBeenCalled()

        jest.advanceTimersByTime(g(time) - 1)
        expect(f).not.toHaveBeenCalled()

        jest.advanceTimersByTime(1)
        expect(f).toHaveBeenCalledTimes(1)
      },
      { time, unit }
    ))

    it.each(intervals)('should call the function every time the specified %ss has elapsed', (unit, time, g) => withTimer(
      (timer, f) => {
        const interval = g(time)
        timer.start()

        jest.advanceTimersByTime(interval)
        expect(f).toHaveBeenCalledTimes(1)

        jest.advanceTimersByTime(interval)
        expect(f).toHaveBeenCalledTimes(2)

        jest.advanceTimersByTime(interval)
        expect(f).toHaveBeenCalledTimes(3)
      },
      { time, unit }
    ))
  })

  describe('stop', () => {
    it('should stop calling the function', () => withTimer((timer, f) => {
      timer.start()

      jest.advanceTimersByTime(1000)
      expect(f).toHaveBeenCalledTimes(1)

      timer.stop()

      jest.advanceTimersByTime(1000)
      expect(f).toHaveBeenCalledTimes(1)

      jest.advanceTimersByTime(1000)
      expect(f).toHaveBeenCalledTimes(1)
    }))
  })

  describe('reset', () => {
    it('should restart the timer', () => withTimer((timer, f) => {
      timer.start()

      // 通常通り 1000ms 経過した時点で関数が実行される
      jest.advanceTimersByTime(1000)
      expect(f).toHaveBeenCalledTimes(1)

      // さらに 500ms 経過した時点では2回目の関数実行はまだ行われていない
      jest.advanceTimersByTime(500)
      expect(f).toHaveBeenCalledTimes(1)

      // 1回目の関数実行と2回目の関数実行の間にリセット
      timer.reset()

      // タイマーがリセットされるので 500 + 500 = 1000ms 経過しても関数は実行されない
      jest.advanceTimersByTime(500)
      expect(f).toHaveBeenCalledTimes(1)

      // リセット後に 500 + 500 = 1000ms 経過した時点で関数が実行される
      jest.advanceTimersByTime(500)
      expect(f).toHaveBeenCalledTimes(2)
    }))

    it('should reset the interval', () => withTimer((timer, f) => {
      timer.start()

      // 通常通り 1000ms 経過した時点で関数が実行される
      jest.advanceTimersByTime(1000)
      expect(f).toHaveBeenCalledTimes(1)

      // さらに 500ms 経過した時点では2回目の関数実行はまだ行われていない
      jest.advanceTimersByTime(500)
      expect(f).toHaveBeenCalledTimes(1)

      // 1回目の関数実行と2回目の関数実行の間にリセット
      timer.reset({ time: 2, unit: 'second' })

      // タイマーがリセットされるので 500 + 500 = 1000ms 経過しても関数は実行されない
      jest.advanceTimersByTime(1000)
      expect(f).toHaveBeenCalledTimes(1)

      // リセット後に 1000 + 1000 = 2000ms = 2s 経過した時点で関数が実行される
      jest.advanceTimersByTime(1000)
      expect(f).toHaveBeenCalledTimes(2)
    }))
  })
})
