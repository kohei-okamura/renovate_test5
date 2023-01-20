/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { AxiosInstance } from 'axios'
import { LazyGetter } from 'lazy-get-decorator'
import { NuxtContext } from '~/models/nuxt'
import { AttendancesApi } from '~/services/api/attendances-api'
import { BankAccountsApi } from '~/services/api/bank-accounts-api'
import { CallingsApi } from '~/services/api/callings-api'
import { CopayListsApi } from '~/services/api/copay-lists-api'
import { DwsAreaGradesApi } from '~/services/api/dws-area-grades-api'
import { DwsBillingCopayCoordinationsApi } from '~/services/api/dws-billing-copay-coordinations-api'
import { DwsBillingServiceReportsApi } from '~/services/api/dws-billing-service-reports-api'
import { DwsBillingStatementsApi } from '~/services/api/dws-billing-statements-api'
import { DwsBillingsApi } from '~/services/api/dws-billings-api'
import { DwsCertificationsApi } from '~/services/api/dws-certifications-api'
import { DwsContractsApi } from '~/services/api/dws-contracts-api'
import { DwsProjectServiceMenusApi } from '~/services/api/dws-project-service-menus-api'
import { DwsProjectsApi } from '~/services/api/dws-projects-api'
import { DwsProvisionReportsApi } from '~/services/api/dws-provision-reports-api'
import { DwsSubsidiesApi } from '~/services/api/dws-subsidies-api'
import { HomeHelpServiceCalcSpecsApi } from '~/services/api/home-help-service-calc-specs-api'
import { HomeVisitLongTermCareCalcSpecsApi } from '~/services/api/home-visit-long-term-care-calc-specs-api'
import { InvitationsApi } from '~/services/api/invitations-api'
import { JobsApi } from '~/services/api/jobs-api'
import { LtcsAreaGradesApi } from '~/services/api/ltcs-area-grades-api'
import { LtcsBillingStatementsApi } from '~/services/api/ltcs-billing-statements-api'
import { LtcsBillingsApi } from '~/services/api/ltcs-billings-api'
import { LtcsContractsApi } from '~/services/api/ltcs-contracts-api'
import { LtcsHomeVisitLongTermCareDictionaryApi } from '~/services/api/ltcs-home-visit-long-term-care-dictionary-api'
import { LtcsInsCardsApi } from '~/services/api/ltcs-ins-cards-api'
import { LtcsProjectServiceMenusApi } from '~/services/api/ltcs-project-service-menus-api'
import { LtcsProjectsApi } from '~/services/api/ltcs-projects-api'
import { LtcsProvisionReportsApi } from '~/services/api/ltcs-provision-reports-api'
import { LtcsSubsidiesApi } from '~/services/api/ltcs-subsidies-api'
import { OfficeGroupsApi } from '~/services/api/office-groups-api'
import { OfficesApi } from '~/services/api/offices-api'
import { OptionsApi } from '~/services/api/options-api'
import { OwnExpenseProgramsApi } from '~/services/api/own-expense-programs-api'
import { PasswordResetsApi } from '~/services/api/password-resets-api'
import { PermissionsApi } from '~/services/api/permissions-api'
import { PostcodeApi } from '~/services/api/postcode-api'
import { RolesApi } from '~/services/api/roles-api'
import { SessionsApi } from '~/services/api/sessions-api'
import { SettingApi } from '~/services/api/setting-api'
import { ShiftsApi } from '~/services/api/shifts-api'
import { StaffsApi } from '~/services/api/staffs-api'
import { UserBillingsApi } from '~/services/api/user-billings-api'
import { UserDwsCalcSpecsApi } from '~/services/api/user-dws-calc-specs-api'
import { UserLtcsCalcSpecsApi } from '~/services/api/user-ltcs-calc-specs-api'
import { UsersApi } from '~/services/api/users-api'
import { VisitingCareForPwsdCalcSpecsApi } from '~/services/api/visiting-care-for-pwsd-calc-specs-api'
import { WithdrawalTransactionsApi } from '~/services/api/withdrawal-transactions-api'

class ApiServiceImpl {
  constructor (private axios: AxiosInstance) {
  }

  @LazyGetter()
  get attendances (): AttendancesApi.Definition {
    return AttendancesApi.create(this.axios)
  }

  @LazyGetter()
  get bankAccounts (): BankAccountsApi.Definition {
    return BankAccountsApi.create(this.axios)
  }

  @LazyGetter()
  get callings (): CallingsApi.Definition {
    return CallingsApi.create(this.axios)
  }

  @LazyGetter()
  get copayLists (): CopayListsApi.Definition {
    return CopayListsApi.create(this.axios)
  }

  @LazyGetter()
  get dwsAreaGrades (): DwsAreaGradesApi.Definition {
    return DwsAreaGradesApi.create(this.axios)
  }

  @LazyGetter()
  get dwsBillings (): DwsBillingsApi.Definition {
    return DwsBillingsApi.create(this.axios)
  }

  @LazyGetter()
  get dwsBillingCopayCoordinations (): DwsBillingCopayCoordinationsApi.Definition {
    return DwsBillingCopayCoordinationsApi.create(this.axios)
  }

  @LazyGetter()
  get dwsBillingServiceReports (): DwsBillingServiceReportsApi.Definition {
    return DwsBillingServiceReportsApi.create(this.axios)
  }

  @LazyGetter()
  get dwsBillingStatements (): DwsBillingStatementsApi.Definition {
    return DwsBillingStatementsApi.create(this.axios)
  }

  @LazyGetter()
  get dwsCertifications (): DwsCertificationsApi.Definition {
    return DwsCertificationsApi.create(this.axios)
  }

  @LazyGetter()
  get dwsContracts (): DwsContractsApi.Definition {
    return DwsContractsApi.create(this.axios)
  }

  @LazyGetter()
  get dwsProjects (): DwsProjectsApi.Definition {
    return DwsProjectsApi.create(this.axios)
  }

  @LazyGetter()
  get dwsProjectServiceMenus (): DwsProjectServiceMenusApi.Definition {
    return DwsProjectServiceMenusApi.create(this.axios)
  }

  @LazyGetter()
  get dwsSubsidies (): DwsSubsidiesApi.Definition {
    return DwsSubsidiesApi.create(this.axios)
  }

  @LazyGetter()
  get dwsProvisionReports (): DwsProvisionReportsApi.Definition {
    return DwsProvisionReportsApi.create(this.axios)
  }

  @LazyGetter()
  get homeHelpServiceCalcSpecs (): HomeHelpServiceCalcSpecsApi.Definition {
    return HomeHelpServiceCalcSpecsApi.create(this.axios)
  }

  @LazyGetter()
  get homeVisitLongTermCareCalcSpecs (): HomeVisitLongTermCareCalcSpecsApi.Definition {
    return HomeVisitLongTermCareCalcSpecsApi.create(this.axios)
  }

  @LazyGetter()
  get invitations (): InvitationsApi.Definition {
    return InvitationsApi.create(this.axios)
  }

  @LazyGetter()
  get jobs (): JobsApi.Definition {
    return JobsApi.create(this.axios)
  }

  @LazyGetter()
  get ltcsAreaGrades (): LtcsAreaGradesApi.Definition {
    return LtcsAreaGradesApi.create(this.axios)
  }

  @LazyGetter()
  get ltcsBillings (): LtcsBillingsApi.Definition {
    return LtcsBillingsApi.create(this.axios)
  }

  @LazyGetter()
  get ltcsBillingStatements (): LtcsBillingStatementsApi.Definition {
    return LtcsBillingStatementsApi.create(this.axios)
  }

  @LazyGetter()
  get ltcsContracts (): LtcsContractsApi.Definition {
    return LtcsContractsApi.create(this.axios)
  }

  @LazyGetter()
  get ltcsInsCards (): LtcsInsCardsApi.Definition {
    return LtcsInsCardsApi.create(this.axios)
  }

  @LazyGetter()
  get ltcsHomeVisitLongTermCareDictionary (): LtcsHomeVisitLongTermCareDictionaryApi.Definition {
    return LtcsHomeVisitLongTermCareDictionaryApi.create(this.axios)
  }

  @LazyGetter()
  get ltcsProjects (): LtcsProjectsApi.Definition {
    return LtcsProjectsApi.create(this.axios)
  }

  @LazyGetter()
  get ltcsProjectServiceMenus (): LtcsProjectServiceMenusApi.Definition {
    return LtcsProjectServiceMenusApi.create(this.axios)
  }

  @LazyGetter()
  get ltcsProvisionReports (): LtcsProvisionReportsApi.Definition {
    return LtcsProvisionReportsApi.create(this.axios)
  }

  @LazyGetter()
  get ltcsSubsidies (): LtcsSubsidiesApi.Definition {
    return LtcsSubsidiesApi.create(this.axios)
  }

  @LazyGetter()
  get officeGroups (): OfficeGroupsApi.Definition {
    return OfficeGroupsApi.create(this.axios)
  }

  @LazyGetter()
  get offices (): OfficesApi.Definition {
    return OfficesApi.create(this.axios)
  }

  @LazyGetter()
  get options (): OptionsApi.Definition {
    return OptionsApi.create(this.axios)
  }

  @LazyGetter()
  get ownExpensePrograms (): OwnExpenseProgramsApi.Definition {
    return OwnExpenseProgramsApi.create(this.axios)
  }

  @LazyGetter()
  get passwordResets (): PasswordResetsApi.Definition {
    return PasswordResetsApi.create(this.axios)
  }

  @LazyGetter()
  get permissions (): PermissionsApi.Definition {
    return PermissionsApi.create(this.axios)
  }

  @LazyGetter()
  get shifts (): ShiftsApi.Definition {
    return ShiftsApi.create(this.axios)
  }

  @LazyGetter()
  get postcode (): PostcodeApi.Definition {
    return PostcodeApi.create(this.axios)
  }

  @LazyGetter()
  get roles (): RolesApi.Definition {
    return RolesApi.create(this.axios)
  }

  @LazyGetter()
  get sessions (): SessionsApi.Definition {
    return SessionsApi.create(this.axios)
  }

  @LazyGetter()
  get setting (): SettingApi.Definition {
    return SettingApi.create(this.axios)
  }

  @LazyGetter()
  get staffs (): StaffsApi.Definition {
    return StaffsApi.create(this.axios)
  }

  @LazyGetter()
  get userBillings (): UserBillingsApi.Definition {
    return UserBillingsApi.create(this.axios)
  }

  @LazyGetter()
  get userDwsCalcSpecs (): UserDwsCalcSpecsApi.Definition {
    return UserDwsCalcSpecsApi.create(this.axios)
  }

  @LazyGetter()
  get userLtcsCalcSpecs (): UserLtcsCalcSpecsApi.Definition {
    return UserLtcsCalcSpecsApi.create(this.axios)
  }

  @LazyGetter()
  get users (): UsersApi.Definition {
    return UsersApi.create(this.axios)
  }

  @LazyGetter()
  get visitingCareForPwsdCalcSpecs (): VisitingCareForPwsdCalcSpecsApi.Definition {
    return VisitingCareForPwsdCalcSpecsApi.create(this.axios)
  }

  @LazyGetter()
  get withdrawalTransactions (): WithdrawalTransactionsApi.Definition {
    return WithdrawalTransactionsApi.create(this.axios)
  }
}

export type ApiService = ApiServiceImpl

export function createApiService ({ $axios }: NuxtContext): ApiService {
  return new ApiServiceImpl($axios)
}
