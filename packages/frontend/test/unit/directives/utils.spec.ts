/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { findInputElement } from '~/directives/utils'

describe('directives/utils', () => {
  describe('findInputElement', () => {
    it('should be return the passed input element when argument is input element', () => {
      const element = document.createElement('input')
      element.setAttribute('id', 'inputElement')
      const foundElement = findInputElement(element)
      expect(element.id).toEqual(foundElement.id)
    })

    it('should be return the child element when element has child input element', () => {
      const parent = document.createElement('div')
      const child = document.createElement('input')
      child.setAttribute('id', 'childInputElement')
      parent.appendChild(child)
      const foundElement = findInputElement(parent)
      expect(child.id).toEqual(foundElement.id)
    })

    it('should throw an error when element doesn\'t have input element', () => {
      const element = document.createElement('div')
      expect(() => { findInputElement(element) }).toThrow()
    })

    it('should throw an error when element has multiple input elements', () => {
      const parent = document.createElement('div')
      for (let i = 1; i <= 3; i++) {
        const child = document.createElement('input')
        child.setAttribute('id', `inputElement_${i}`)
        parent.appendChild(child)
      }
      expect(() => { findInputElement(parent) }).toThrow()
    })
  })
})
