/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { usePolling } from '~/composables/use-polling'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

type Data = {
  token: string
}

describe('composables/use-polling', () => {
  const token = 'xxx'
  const params = {
    init: () => Promise.resolve({ token } as Data),
    test: (_data: Data) => true,
    poll: (_data: Data) => Promise.resolve({ token } as Data)
  }

  function mockTimers () {
    jest.spyOn(global, 'setTimeout').mockImplementation(f => {
      if (typeof f === 'function') {
        f()
      }
      return 0 as any
    })
  }

  beforeAll(() => {
    setupComposableTest()
  })

  afterEach(() => {
    jest.clearAllMocks()
  })

  describe('startPolling', () => {
    it('should call params.init', async () => {
      const { startPolling } = usePolling()
      jest.spyOn(params, 'init')

      await startPolling(params)

      expect(params.init).toHaveBeenCalledTimes(1)
    })

    it('should call params.test with returned value of params.init', async () => {
      const { startPolling } = usePolling()
      jest.spyOn(params, 'init').mockResolvedValueOnce({ token: 'xyz' })
      jest.spyOn(params, 'test')

      await startPolling(params)

      expect(params.test).toHaveBeenCalledTimes(1)
      expect(params.test).toHaveBeenCalledWith({ token: 'xyz' })
    })

    it('should not call params.poll with params.test returns true', async () => {
      const { startPolling } = usePolling()
      jest.spyOn(params, 'test').mockReturnValue(true)
      jest.spyOn(params, 'poll')

      await startPolling(params)

      expect(params.poll).not.toHaveBeenCalled()
    })

    it('should return the value that returned by params.init', async () => {
      const { startPolling } = usePolling()
      jest.spyOn(params, 'init').mockResolvedValueOnce({ token: 'zzz' })

      const x = await startPolling(params)

      expect(x).toStrictEqual({ token: 'zzz' })
    })

    it('should polling and return passed value', async () => {
      const { startPolling } = usePolling()
      const data = [
        { token: 'aaa' },
        { token: 'bbb' },
        { token: 'ccc' },
        { token: 'ddd' },
        { token: 'eee' },
        { token: 'fff' }
      ]
      jest.spyOn(params, 'init').mockResolvedValueOnce(data[0])
      jest.spyOn(params, 'test').mockImplementation(({ token }) => token === 'fff')
      jest.spyOn(params, 'poll')
        .mockResolvedValueOnce(data[1])
        .mockResolvedValueOnce(data[2])
        .mockResolvedValueOnce(data[3])
        .mockResolvedValueOnce(data[4])
        .mockResolvedValueOnce(data[5])
      mockTimers()

      const x = await startPolling(params)

      expect(params.test).toHaveBeenCalledTimes(6)
      expect(params.test).toHaveBeenNthCalledWith(1, data[0])
      expect(params.test).toHaveBeenNthCalledWith(2, data[1])
      expect(params.test).toHaveBeenNthCalledWith(3, data[2])
      expect(params.test).toHaveBeenNthCalledWith(4, data[3])
      expect(params.test).toHaveBeenNthCalledWith(5, data[4])
      expect(params.test).toHaveBeenNthCalledWith(6, data[5])
      expect(params.poll).toHaveBeenCalledTimes(5)
      expect(params.poll).toHaveBeenNthCalledWith(1, data[0])
      expect(params.poll).toHaveBeenNthCalledWith(2, data[1])
      expect(params.poll).toHaveBeenNthCalledWith(3, data[2])
      expect(params.poll).toHaveBeenNthCalledWith(4, data[3])
      expect(params.poll).toHaveBeenNthCalledWith(5, data[4])
      expect(x).toStrictEqual(data[5])
    })
  })

  it('should resolve false when it is canceled', async () => {
    const { cancelPolling, startPolling } = usePolling()
    jest.spyOn(params, 'test').mockReturnValue(false)
    jest.spyOn(params, 'poll').mockImplementation(({ token }) => {
      cancelPolling()
      return Promise.resolve({ token })
    })
    mockTimers()

    cancelPolling()
    const x = await startPolling(params)

    expect(x).toBeFalse()
  })

  it('should resolve false when maxRetry exceeded', async () => {
    const { startPolling } = usePolling()
    jest.spyOn(params, 'test').mockReturnValue(false)
    mockTimers()

    const x = await startPolling({
      ...params,
      maxRetry: 5
    })

    expect(x).toBeFalse()
    expect(params.poll).toHaveBeenCalledTimes(1 + 5) // first try (1) + retries (5)
  })

  it.each([
    [1, 1234, 1234],
    [2, 2345, (x: number) => x + 2345],
    [3, 2000, undefined]
  ])('should wait specified intervalMilliseconds #%n', async (_, ms, intervalMilliseconds) => {
    const { startPolling } = usePolling()
    jest.spyOn(params, 'test').mockReturnValue(false)
    mockTimers()

    await startPolling({
      ...params,
      maxRetry: 0,
      intervalMilliseconds
    })

    expect(setTimeout).toHaveBeenCalledTimes(1)
    expect(setTimeout).toHaveBeenCalledWith(expect.any(Function), ms)
  })
})
