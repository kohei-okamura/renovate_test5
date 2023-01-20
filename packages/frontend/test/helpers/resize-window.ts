/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import flushPromises from 'flush-promises'

export interface ResizeWindowParams {
  height?: number
  width?: number
}

export interface ResizeWindow {
  (params: ResizeWindowParams, f: () => void | Promise<void>): Promise<void>
}

/**
 * ウィンドウをリサイズした状態でテストを実行する.
 */
export const resizeWindow: ResizeWindow = async ({ height, width }, f) => {
  // Setup
  const innerHeight = window.innerHeight
  const innerWidth = window.innerWidth
  const originalSize = {
    innerHeight,
    innerWidth
  }
  const expectedSize = {
    innerHeight: height ?? innerHeight,
    innerWidth: width ?? innerWidth
  }
  const resize = (size: Dictionary<number>) => {
    Object.assign(window, size)
    window.dispatchEvent(new Event('resize'))
    jest.runOnlyPendingTimers()
  }

  try {
    resize(expectedSize)
    await flushPromises().then(f)
  } finally {
    resize(originalSize)
  }
}

export default resizeWindow
