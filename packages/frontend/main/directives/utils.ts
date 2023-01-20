/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { tap } from '@zinger/helpers'

export function createEvent (type: EventType): Event {
  return tap(document.createEvent('Event'), event => event.initEvent(type, true, true))
}

export function findInputElement (element: HTMLElement): HTMLInputElement {
  if (element.tagName.toUpperCase() === 'INPUT') {
    return element as HTMLInputElement
  }
  const xs = element.getElementsByTagName('input')
  if (xs.length !== 1) {
    throw new Error(`findInputElement expects to have only one input element in the entire argument element, found ${xs.length}`)
  }
  return xs[0]
}

export function getInputElement (event: Event): HTMLInputElement {
  const target = event.target
  if (!(target instanceof HTMLInputElement)) {
    throw new TypeError('Event target is not an HTMLInputElement.')
  }
  return target
}
