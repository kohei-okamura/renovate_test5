/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { NuxtAxiosInstance } from '@nuxtjs/axios'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import { AxiosInstance } from 'axios'
import { pascalCase } from 'change-case'
import { NuxtContext } from '~/models/nuxt'
import { ApiService, createApiService } from '~/services/api-service'
import { AttendancesApi } from '~/services/api/attendances-api'
import { BankAccountsApi } from '~/services/api/bank-accounts-api'
import { CallingsApi } from '~/services/api/callings-api'
import { DwsAreaGradesApi } from '~/services/api/dws-area-grades-api'
import { DwsBillingsApi } from '~/services/api/dws-billings-api'
import { DwsCertificationsApi } from '~/services/api/dws-certifications-api'
import { DwsContractsApi } from '~/services/api/dws-contracts-api'
import { DwsSubsidiesApi } from '~/services/api/dws-subsidies-api'
import { InvitationsApi } from '~/services/api/invitations-api'
import { JobsApi } from '~/services/api/jobs-api'
import { LtcsAreaGradesApi } from '~/services/api/ltcs-area-grades-api'
import { LtcsBillingStatementsApi } from '~/services/api/ltcs-billing-statements-api'
import { LtcsBillingsApi } from '~/services/api/ltcs-billings-api'
import { LtcsContractsApi } from '~/services/api/ltcs-contracts-api'
import { LtcsHomeVisitLongTermCareDictionaryApi } from '~/services/api/ltcs-home-visit-long-term-care-dictionary-api'
import { LtcsInsCardsApi } from '~/services/api/ltcs-ins-cards-api'
import { OfficeGroupsApi } from '~/services/api/office-groups-api'
import { OfficesApi } from '~/services/api/offices-api'
import { OptionsApi } from '~/services/api/options-api'
import { OwnExpenseProgramsApi } from '~/services/api/own-expense-programs-api'
import { PasswordResetsApi } from '~/services/api/password-resets-api'
import { PermissionsApi } from '~/services/api/permissions-api'
import { PostcodeApi } from '~/services/api/postcode-api'
import { RolesApi } from '~/services/api/roles-api'
import { SessionsApi } from '~/services/api/sessions-api'
import { ShiftsApi } from '~/services/api/shifts-api'
import { StaffsApi } from '~/services/api/staffs-api'
import { UsersApi } from '~/services/api/users-api'

describe('services/api-service', () => {
  const $axios = createMock<AxiosInstance>()
  const context = createMock<NuxtContext>({ $axios })

  let $api: ApiService

  beforeAll(() => {
    $api = createApiService(context)
  })

  type Create = {
    create: (axios: NuxtAxiosInstance) => any
  }

  describe.each<[keyof ApiService, Create]>([
    ['attendances', AttendancesApi],
    ['bankAccounts', BankAccountsApi],
    ['callings', CallingsApi],
    ['dwsAreaGrades', DwsAreaGradesApi],
    ['dwsBillings', DwsBillingsApi],
    ['dwsCertifications', DwsCertificationsApi],
    ['dwsContracts', DwsContractsApi],
    ['dwsSubsidies', DwsSubsidiesApi],
    ['invitations', InvitationsApi],
    ['jobs', JobsApi],
    ['ltcsAreaGrades', LtcsAreaGradesApi],
    ['ltcsBillings', LtcsBillingsApi],
    ['ltcsBillingStatements', LtcsBillingStatementsApi],
    ['ltcsContracts', LtcsContractsApi],
    ['ltcsHomeVisitLongTermCareDictionary', LtcsHomeVisitLongTermCareDictionaryApi],
    ['ltcsInsCards', LtcsInsCardsApi],
    ['officeGroups', OfficeGroupsApi],
    ['offices', OfficesApi],
    ['options', OptionsApi],
    ['ownExpensePrograms', OwnExpenseProgramsApi],
    ['passwordResets', PasswordResetsApi],
    ['permissions', PermissionsApi],
    ['postcode', PostcodeApi],
    ['roles', RolesApi],
    ['sessions', SessionsApi],
    ['shifts', ShiftsApi],
    ['staffs', StaffsApi],
    ['users', UsersApi]
  ])('%s', (name, target) => {
    const stub = {} as any

    beforeEach(() => {
      jest.spyOn(target, 'create').mockReturnValue(stub)
    })

    afterEach(() => {
      mocked(target.create).mockReset()
    })

    it(`should call ${pascalCase(name)}Api.create once`, () => {
      expect($api[name]).toBe(stub)
      expect($api[name]).toBe(stub)
      expect(target.create).toHaveBeenCalledTimes(1)
      expect(target.create).toHaveBeenCalledWith($axios)
    })
  })
})
