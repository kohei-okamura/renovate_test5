/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
type MatchMediaFunctions = {
  hasCoarsePointer: () => boolean
}

export function useMatchMedia (): MatchMediaFunctions {
  return {
    hasCoarsePointer: () => window.matchMedia('(any-pointer: coarse)').matches
  }
}
