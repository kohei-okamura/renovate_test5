/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
function * generate () {
  yield 'example.com'
  yield 'example.net'
  yield 'example.org'
  yield 'example.jp'
  yield 'example.co.jp'
  yield 'example.ne.jp'
  for (let i = 9; i >= 0; --i) {
    yield `example${i}.jp`
    yield `example${i}.co.jp`
    yield `example${i}.ne.jp`
  }
}

export const domains: string[] = Array.from(generate())
