/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { noop } from '@zinger/helpers'

if (typeof console === 'undefined') {
  const dummy = {
    error: noop,
    warn: noop
  }
  Object.assign(window, { console: dummy })
}

const asserted: any[] = []
type Type = 'warn' | 'error'
type Spies = Dictionary<jest.SpyInstance<any, any>>

const error = window.console.error

function createMatcher (spies: Spies, type: Type): jest.CustomMatcher {
  const hasWarned = (message: string) => {
    return spies[type].mock.calls.some((args: any[]) => args.some((arg: any) => arg.toString().includes(message)))
  }
  return (message: any | any[]) => {
    asserted.push(message)
    const warned = Array.isArray(message) ? message.some(hasWarned) : hasWarned(message)
    return {
      pass: warned,
      message: warned
        ? () => (`Expected message "${message}" not to have been warned`)
        : () => (`Expected message "${message}" to have been warned`)
    }
  }
}

function init (types: Type[] = ['warn', 'error']) {
  const spies: Spies = {}
  beforeEach(() => {
    asserted.length = 0
    types.forEach(type => {
      spies[type] = jest.spyOn(window.console, type).mockImplementation((...args: any[]) => {
        console.log(...args)
      })
    })
    expect.extend({
      toHaveBeenWarned: createMatcher(spies, 'warn'),
      toHaveBeenErrored: createMatcher(spies, 'error')
    })
  })
  afterEach(() => {
    const warned = (message: string) => asserted.some(asserted => message.toString().includes(asserted))
    for (const type of Object.keys(spies)) {
      for (const args of spies[type].mock.calls) {
        if (!warned(args[0])) {
          error.apply(window.console, args)
          return Promise.reject(new Error(`Unexpected console.${type} message: ${args[0]}`))
        }
      }
    }
    return Promise.resolve()
  })
}

const toHaveBeenWarned = {
  init
}

export default toHaveBeenWarned
