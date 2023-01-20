/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import {
  dwsBillingCopayCoordinationStateKey,
  dwsBillingCopayCoordinationStoreKey
} from '~/composables/stores/use-dws-billing-copay-coordination-store'
import { dwsBillingStatementStateKey } from '~/composables/stores/use-dws-billing-statement-store'
import { useOffices } from '~/composables/use-offices'
import { HttpStatusCode } from '~/models/http-status-code'
import DwsBillingCopayCoordinationEditPage
  from '~/pages/dws-billings/_id/bundles/_bundleId/statements/_statementId/copay-coordinations/_coordinationId/edit.vue'
import { DwsBillingCopayCoordinationsApi } from '~/services/api/dws-billing-copay-coordinations-api'
import { SnackbarService } from '~/services/snackbar-service'
import {
  createDwsBillingCopayCoordinationResponseStub
} from '~~/stubs/create-dws-billing-copay-coordination-response-stub'
import { createDwsBillingCopayCoordinationStoreStub } from '~~/stubs/create-dws-billing-copay-coordination-store-stub'
import { createDwsBillingStatementResponseStub } from '~~/stubs/create-dws-billing-statement-response-stub'
import { createDwsBillingStatementStoreStub } from '~~/stubs/create-dws-billing-statement-store-stub'
import { OFFICE_ID_MAX, OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { USER_ID_MIN } from '~~/stubs/create-user-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')

describe('pages/dws-billings/_id/bundles/_bundleId/statements/_statementId/copay-coordinations/_coordinationId/edit.vue', () => {
  const { shallowMount } = setupComponentTest()
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const mocks = {
    $form,
    $router,
    $snackbar
  }
  const responseStub = createDwsBillingStatementResponseStub({ id: 1 })
  const dwsBillingStatementStore = createDwsBillingStatementStoreStub(responseStub)
  const response = createDwsBillingCopayCoordinationResponseStub()
  const dwsBillingCopayCoordinationStore = createDwsBillingCopayCoordinationStoreStub(response)
  let wrapper: Wrapper<Vue & any>

  type MountComponentArguments = {
    options?: MountOptions<Vue>
  }

  function mountComponent ({ options }: MountComponentArguments = {}) {
    wrapper = shallowMount(DwsBillingCopayCoordinationEditPage, {
      ...provides(
        [dwsBillingStatementStateKey, dwsBillingStatementStore.state],
        [dwsBillingCopayCoordinationStoreKey, dwsBillingCopayCoordinationStore],
        [dwsBillingCopayCoordinationStateKey, dwsBillingCopayCoordinationStore.state]
      ),
      mocks,
      ...options
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
    mountComponent()
  })

  afterAll(() => {
    unmountComponent()
    mocked(useOffices).mockReset()
  })

  describe('submit', () => {
    const pathParameters = {
      billingId: response.billing.id,
      bundleId: response.bundle.id,
      id: response.copayCoordination.id
    }
    const form: Partial<DwsBillingCopayCoordinationsApi.Form> = {
      userId: USER_ID_MIN,
      items: [
        { officeId: OFFICE_ID_MIN, subtotal: { fee: 3200, copay: 1200, coordinatedCopay: 480 } },
        { officeId: OFFICE_ID_MAX, subtotal: { fee: 36000, copay: 3600, coordinatedCopay: 1080 } }
      ],
      result: response.copayCoordination.result
    }

    beforeAll(() => {
      jest.spyOn($router, 'replace')
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn(dwsBillingCopayCoordinationStore, 'update').mockResolvedValue()
    })

    afterAll(() => {
      mocked(dwsBillingCopayCoordinationStore.update).mockRestore()
      mocked($snackbar.success).mockRestore()
      mocked($router.replace).mockRestore()
    })

    afterEach(() => {
      mocked($router.replace).mockClear()
      mocked($snackbar.success).mockClear()
      mocked(dwsBillingCopayCoordinationStore.update).mockClear()
    })

    it('should call dwsBillingCopayCoordinationStore.update', async () => {
      await wrapper.vm.submit(form)

      expect(dwsBillingCopayCoordinationStore.update).toHaveBeenCalledTimes(1)
      expect(dwsBillingCopayCoordinationStore.update).toHaveBeenCalledWith({ ...pathParameters, form })
    })

    it('should display message', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('利用者負担上限額管理結果票を編集しました。')
    })

    it('should move to details page if update was successful', async () => {
      const { billingId, bundleId, id } = pathParameters
      const statementId = dwsBillingStatementStore.state.statement.value?.id
      const url = `/dws-billings/${billingId}/bundles/${bundleId}/statements/${statementId}/copay-coordinations/${id}`
      await wrapper.vm.submit(form)

      expect($router.replace).toHaveBeenCalledTimes(1)
      expect($router.replace).toHaveBeenCalledWith(url)
    })

    it.each([
      ['result', '利用者負担上限額管理結果を入力してください。']
    ])(
      'should set errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message) => {
        jest.spyOn(dwsBillingCopayCoordinationStore, 'update').mockRejectedValue(
          createAxiosError(HttpStatusCode.BadRequest, {
            errors: {
              [key]: [message]
            }
          })
        )

        await wrapper.vm.submit(form)
        await wrapper.vm.$nextTick()

        expect($snackbar.success).not.toHaveBeenCalled()
        expect(wrapper.vm.errors).toMatchObject({ [key]: [message] })
      }
    )
  })
})
