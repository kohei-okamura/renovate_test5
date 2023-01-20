/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Permission } from '@zinger/enums/lib/permission'
import { mocked } from '@zinger/helpers/testing/mocked'
import { useAuth } from '~/composables/use-auth'
import { createSessionStoreStub } from '~~/stubs/create-session-store-stub'
import { createStaffStub } from '~~/stubs/create-staff-stub'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

describe('composables/use-auth', () => {
  setupComposableTest()
  const staff = createStaffStub()

  describe('isAuthenticated', () => {
    it('should return false if user is not authenticated', () => {
      const session = createSessionStoreStub({ auth: undefined })
      const { isAuthenticated } = useAuth(session)
      expect(isAuthenticated.value).toBeFalse()
    })

    it('should return true if user is authenticated', () => {
      const auth = { isSystemAdmin: true, permissions: [], staff }
      const session = createSessionStoreStub({ auth })
      const { isAuthenticated } = useAuth(session)
      expect(isAuthenticated.value).toBeTrue()
    })

    it('should change the state to reactive', () => {
      const auth = { isSystemAdmin: true, permissions: [], staff }
      const session = createSessionStoreStub()
      const { isAuthenticated } = useAuth(session)

      expect(isAuthenticated.value).toBeFalse()

      session.updateAuth(auth)

      expect(isAuthenticated.value).toBeTrue()
    })
  })

  describe('isAuthorized', () => {
    it('should return true if user is system administrator even if the user does not have permission', () => {
      const auth = { isSystemAdmin: true, permissions: [], staff }
      const session = createSessionStoreStub({ auth })
      const { isAuthorized } = useAuth(session)
      expect(isAuthorized.value([Permission.listUsers])).toBeTrue()
    })

    it('should return true if permission is not required', () => {
      const auth = { isSystemAdmin: false, permissions: [], staff }
      const session = createSessionStoreStub({ auth })
      const { isAuthorized } = useAuth(session)
      expect(isAuthorized.value()).toBeTrue()
    })

    it('should return true if the user has permission', () => {
      const auth = { isSystemAdmin: false, permissions: [Permission.listUsers], staff }
      const session = createSessionStoreStub({ auth })
      const { isAuthorized } = useAuth(session)
      expect(isAuthorized.value([Permission.listUsers])).toBeTrue()
    })

    it('should return true if the user have even one permission', () => {
      const auth = { isSystemAdmin: false, permissions: [Permission.listStaffs], staff }
      const session = createSessionStoreStub({ auth })
      const { isAuthorized } = useAuth(session)
      expect(isAuthorized.value([Permission.listUsers, Permission.listStaffs])).toBeTrue()
    })

    it('should return false if the user does not have permission', () => {
      const auth = { isSystemAdmin: false, permissions: [], staff }
      const session = createSessionStoreStub({ auth })
      const { isAuthorized } = useAuth(session)
      expect(isAuthorized.value([Permission.listUsers])).toBeFalse()
    })
  })

  describe('prepare', () => {
    it('should call session.get if the user is unknown', () => {
      const session = createSessionStoreStub({ auth: undefined })
      jest.spyOn(session, 'get')
      const { prepare } = useAuth(session)

      prepare()

      expect(session.get).toHaveBeenCalled()
      expect(session.get).toHaveBeenCalledTimes(1)
      mocked(session.get).mockReset()
    })

    it('should not call session.get if the user have been authenticated', () => {
      const auth = { isSystemAdmin: true, permissions: [], staff }
      const session = createSessionStoreStub({ auth })
      jest.spyOn(session, 'get')
      const { prepare } = useAuth(session)

      prepare()

      expect(session.get).not.toHaveBeenCalled()
      mocked(session.get).mockReset()
    })
  })
})
