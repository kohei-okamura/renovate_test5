/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { phoneNumber } from '~/directives/phone-number'

describe('directives/phone-number', () => {
  let element: HTMLInputElement
  const vNode = {
    isRootInsert: false,
    isComment: false
  }

  beforeEach(() => {
    element = document.createElement('input')
  })

  describe('add event listener', () => {
    const original = '0359376825'
    const formatted = '03-5937-6825'

    it('should add a listener for the \'blur\' event when not specify target event', () => {
      const binding = { name: '', value: true, modifiers: {} }
      phoneNumber(element, binding, vNode, vNode)
      element.value = original
      element.dispatchEvent(new Event('blur'))
      expect(element.value).toEqual(formatted)
    })

    it('should add a listener to the specified event when specify target event', () => {
      const event = 'focus'
      const binding = { name: '', value: { event }, modifiers: {} }
      element.value = original
      phoneNumber(element, binding, vNode, vNode)
      element.dispatchEvent(new Event(event))
      expect(element.value).toEqual(formatted)
    })
  })

  describe('format', () => {
    it.each([
      ['0331000145', '03-3100-0145'],
      ['0112000000', '011-200-0000'],
      ['0248350122', '0248-35-0122'],
      ['0499270080', '04992-7-0080'],
      ['05031969876', '050-3196-9876'],
      ['07058872485', '070-5887-2485'],
      ['08001231234', '0800-123-1234'],
      [' 0120221094 ', '0120-221-094'],
      ['0709489058', '0709489058'],
      ['0120aabbbb', '0120aabbbb']
    ])('should be formatted from %s to %s', (original, formatted) => {
      const binding = { name: '', value: true, modifiers: {} }
      phoneNumber(element, binding, vNode, vNode)
      element.value = original
      element.dispatchEvent(new Event('blur'))
      expect(element.value).toEqual(formatted)
    })
  })
})
