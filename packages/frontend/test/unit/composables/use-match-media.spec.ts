/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { useMatchMedia } from '~/composables/use-match-media'

describe('composables/use-match-media', () => {
  const setSpy = (matches: boolean) => {
    const mockWindow = { ...window }
    Object.defineProperty(mockWindow, 'matchMedia', {
      writable: true,
      value: jest.fn().mockImplementation(query => ({
        matches,
        media: query,
        onchange: null,
        addListener: jest.fn(), // deprecated
        removeListener: jest.fn(), // deprecated
        addEventListener: jest.fn(),
        removeEventListener: jest.fn(),
        dispatchEvent: jest.fn()
      }))
    })
    return jest.spyOn(global, 'window', 'get').mockReturnValue(mockWindow)
  }

  describe('hasCoarsePointer', () => {
    it('should return false when "matches" is false', () => {
      const spy = setSpy(false)
      const { hasCoarsePointer } = useMatchMedia()
      expect(hasCoarsePointer()).toBeFalse()
      spy.mockRestore()
    })

    it('should return true when "matches" is true', () => {
      const spy = setSpy(true)
      const { hasCoarsePointer } = useMatchMedia()
      expect(hasCoarsePointer()).toBeTrue()
      spy.mockRestore()
    })
  })
})
