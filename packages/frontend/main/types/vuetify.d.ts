/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

// Ensure this is treated as a module.
export {}

declare global {
  interface HTMLElement {
    _onScroll?: {
      callback: EventListenerOrEventListenerObject
      options: boolean | AddEventListenerOptions
      target: EventTarget
    }
  }
}
