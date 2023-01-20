/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { NuxtAxiosInstance } from '@nuxtjs/axios'
import { stubAttendances } from '~~/stubs/axios/stub-attendances'
import { stubCallings } from '~~/stubs/axios/stub-callings'
import { stubCopayLists } from '~~/stubs/axios/stub-copay-lists'
import { stubDwsAreaGrades } from '~~/stubs/axios/stub-dws-area-grades'
import { stubDwsBillingCopayCoordinations } from '~~/stubs/axios/stub-dws-billing-copay-coordination'
import { stubDwsDwsBillingServiceReports } from '~~/stubs/axios/stub-dws-billing-service-reports'
import { stubDwsBillings } from '~~/stubs/axios/stub-dws-billings'
import { stubDwsCertifications } from '~~/stubs/axios/stub-dws-certifications'
import { stubDwsContracts } from '~~/stubs/axios/stub-dws-contracts'
import { stubDwsProjectServiceMenus } from '~~/stubs/axios/stub-dws-project-service-menus'
import { stubDwsProjects } from '~~/stubs/axios/stub-dws-projects'
import { stubDwsProvisionReports } from '~~/stubs/axios/stub-dws-provision-reports'
import { stubDwsSubsidies } from '~~/stubs/axios/stub-dws-subsidies'
import { stubHomeHelpServiceCalcSpecs } from '~~/stubs/axios/stub-home-help-service-calc-specs'
import { stubHomeVisitLongTermCareCalcSpecs } from '~~/stubs/axios/stub-home-visit-long-term-care-calc-specs'
import { stubInvitations } from '~~/stubs/axios/stub-invitations'
import { stubLtcsAreaGrades } from '~~/stubs/axios/stub-ltcs-area-grades'
import { stubLtcsBillings } from '~~/stubs/axios/stub-ltcs-billings'
import { stubLtcsContracts } from '~~/stubs/axios/stub-ltcs-contracts'
import { stubLtcsHomeVisitLongTermCareDictionary } from '~~/stubs/axios/stub-ltcs-home-visit-long-term-care-dictionary'
import { stubLtcsInsCards } from '~~/stubs/axios/stub-ltcs-ins-cards'
import { stubLtcsProjectServiceMenus } from '~~/stubs/axios/stub-ltcs-project-service-menus'
import { stubLtcsProjects } from '~~/stubs/axios/stub-ltcs-projects'
import { stubLtcsProvisionReports } from '~~/stubs/axios/stub-ltcs-provision-reports'
import { stubLtcsSubsidies } from '~~/stubs/axios/stub-ltcs-subsidies'
import { stubOfficeGroups } from '~~/stubs/axios/stub-office-groups'
import { stubOffices } from '~~/stubs/axios/stub-offices'
import { stubOptions } from '~~/stubs/axios/stub-options'
import { stubOwnExpensePrograms } from '~~/stubs/axios/stub-own-expense-programs'
import { stubPasswordResets } from '~~/stubs/axios/stub-password-resets'
import { stubPermissions } from '~~/stubs/axios/stub-permissions'
import { stubPostcodes } from '~~/stubs/axios/stub-postcodes'
import { stubRoles } from '~~/stubs/axios/stub-roles'
import { stubSessions } from '~~/stubs/axios/stub-sessions'
import { stubSetting } from '~~/stubs/axios/stub-setting'
import { stubShifts } from '~~/stubs/axios/stub-shifts'
import { stubStaffVerifications } from '~~/stubs/axios/stub-staff-verifications'
import { stubStaffs } from '~~/stubs/axios/stub-staffs'
import { stubUserBillings } from '~~/stubs/axios/stub-user-billings'
import { stubUserDwsCalcSpecs } from '~~/stubs/axios/stub-user-dws-calc-specs'
import { stubUserLtcsCalcSpecs } from '~~/stubs/axios/stub-user-ltcs-calc-specs'
import { stubUsers } from '~~/stubs/axios/stub-users'
import { stubVisitingCareForPwsdCalcSpecs } from '~~/stubs/axios/stub-visiting-care-for-pwsd-calc-specs'
import { stubWithdrawalTransactions } from '~~/stubs/axios/stub-withdrawal-transactions'
import { createMockAdapter } from '~~/stubs/axios/utils'

/**
 * Axios からの API リクエストをスタブ化する.
 */
export const stubAxios = ($axios: NuxtAxiosInstance): void => {
  const mockAdapter = createMockAdapter($axios)
  const stubFunctions = [
    stubAttendances,
    stubCallings,
    stubCopayLists,
    stubDwsAreaGrades,
    stubDwsBillingCopayCoordinations,
    stubDwsBillings,
    stubDwsCertifications,
    stubDwsContracts,
    stubDwsDwsBillingServiceReports,
    stubDwsProjects,
    stubDwsProjectServiceMenus,
    stubDwsProvisionReports,
    stubDwsSubsidies,
    stubHomeHelpServiceCalcSpecs,
    stubHomeVisitLongTermCareCalcSpecs,
    stubInvitations,
    stubLtcsAreaGrades,
    stubLtcsBillings,
    stubLtcsContracts,
    stubLtcsHomeVisitLongTermCareDictionary,
    stubLtcsInsCards,
    stubLtcsProjects,
    stubLtcsProjectServiceMenus,
    stubLtcsProvisionReports,
    stubLtcsSubsidies,
    stubOfficeGroups,
    stubOffices,
    stubOptions,
    stubOwnExpensePrograms,
    stubPasswordResets,
    stubPermissions,
    stubPostcodes,
    stubRoles,
    stubSessions,
    stubSetting,
    stubShifts,
    stubStaffs,
    stubStaffVerifications,
    stubUserBillings,
    stubUserDwsCalcSpecs,
    stubUserLtcsCalcSpecs,
    stubUsers,
    stubVisitingCareForPwsdCalcSpecs,
    stubWithdrawalTransactions
  ]
  stubFunctions.forEach(f => f(mockAdapter))
}
