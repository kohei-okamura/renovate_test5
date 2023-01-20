/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { autoAscii } from '~/directives/auto-ascii'

describe('directives/auto-ascii', () => {
  const fullWidth = '！＂＃＄％＆＇（）＊＋，－．／０１２３４５６７８９：；＜＝＞？＠ＡＢＣＤＥＦＧＨＩＪＫＬＭＮＯＰＱＲＳＴＵＶＷＸＹＺ［＼］＾＿｀ａｂｃｄｅｆｇｈｉｊｋｌｍｎｏｐｑｒｓｔｕｖｗｘｙｚ｛｜｝～\n'
  const halfWidth = '!"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~'
  let element: HTMLInputElement
  const vNode = {
    isRootInsert: false,
    isComment: false
  }

  beforeEach(() => {
    element = document.createElement('input')
  })

  it('should add a listener for the \'blur\' event when not specify target event', () => {
    const binding = { name: '', value: true, modifiers: {} }
    autoAscii(element, binding, vNode, vNode)
    element.value = fullWidth
    element.dispatchEvent(new Event('blur'))
    expect(element.value).toEqual(halfWidth)
  })

  it('should add a listener to the specified event when specify target event', () => {
    const event = 'focus'
    const binding = { name: '', value: { event }, modifiers: {} }
    element.value = fullWidth
    autoAscii(element, binding, vNode, vNode)
    element.dispatchEvent(new Event(event))
    expect(element.value).toEqual(halfWidth)
  })
})
