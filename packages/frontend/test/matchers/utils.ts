/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { matcherHint, printReceived } from 'jest-matcher-utils'

export type TestFunction<T = any> = (received: T, ...args: any[]) => boolean

export type Message = string | Lazy<string>

export type MessageFunction<T = any> = (received: T, ...args: any[]) => Message

export type Params<T = any> = {
  name: string
  test: TestFunction<T>
  passMessage: MessageFunction<T>
  failMessage: MessageFunction<T>
  printReceived: boolean
}

export function createMatcher<T = any> (params: Params<T>): jest.CustomMatcher {
  return (received: T, ...args: any[]) => {
    const pass = params.test(received, ...args)
    const message = () =>
      matcherHint(pass ? `.not.${params.name}` : `.${params.name}`, 'received', '') +
      '\n' +
      '\n' +
      (pass ? params.passMessage(received, ...args) : params.failMessage(received, ...args)) +
      (params.printReceived ? ` received:\n  ${printReceived(received)}` : '')
    return { pass, message }
  }
}
