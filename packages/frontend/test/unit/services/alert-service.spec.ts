/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createMock } from '@zinger/helpers/testing/create-mock'
import VueRouter from 'vue-router'
import { AlertConfig, AlertService, createAlertService } from '~/services/alert-service'
import { setupVue } from '~~/test/helpers/setup-vue'

describe('services/alert-service', () => {
  const router = createMock<VueRouter>({
    afterEach: (fn: () => void) => { fn() }
  })
  let app: { router: jest.Mocked<VueRouter> }
  let alert: AlertService

  beforeAll(() => {
    jest.spyOn(router, 'afterEach')
    setupVue()
    app = { router }
    const context: any = { app }
    alert = createAlertService(context)
  })

  afterAll(() => {
    jest.restoreAllMocks()
  })

  it('afterEach should be called once.', () => {
    expect(router.afterEach).toHaveBeenCalledTimes(1)
  })

  it('should be updated state when show is called', async () => {
    const { alertShow, config, show } = alert
    const conf: AlertConfig = {
      color: 'success',
      title: 'New Title',
      text: 'New Text'
    }
    expect(alertShow.value).toBeFalse()
    await show(conf)
    expect(alertShow.value).toBeTrue()
    expect(conf).toEqual(config.value)
  })

  it('should be updated state correctly when each sugar function is called', async () => {
    const { config, error, info, success, warning } = alert
    const functions = [error, info, success, warning]
    const colors = ['error', 'info', 'success', 'warning']
    for (let i = 0; i < functions.length; i++) {
      const color = colors[i]
      const title = `New ${color} Title`
      const text = `New ${color} Text`
      await functions[i](title, text)
      expect({ color, title, text }).toEqual(config.value)
    }
  })
})
