/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { defineNuxtPlugin } from '@nuxtjs/composition-api'
import { createGlobalStore, GlobalStore } from '~/composables/stores'
import { AlertService, createAlertService } from '~/services/alert-service'
import { ApiService, createApiService } from '~/services/api-service'
import { BreadcrumbsService, createBreadcrumbsService } from '~/services/breadcrumbs-service'
import { ConfirmDialogService, createConfirmDialogService } from '~/services/confirm-dialog-service'
import { createDownloadService, DownloadService } from '~/services/download-service'
import { createDrawerService, DrawerService } from '~/services/drawer-service'
import { createFormService, FormService } from '~/services/form-service'
import { createRoutesService, RoutesService } from '~/services/routes-service'
import { createSnackbarService, SnackbarService } from '~/services/snackbar-service'
import { createTabService, TabService } from '~/services/tab-service'

export type Services = Readonly<{
  $alert: AlertService
  $api: ApiService
  $breadcrumbs: BreadcrumbsService
  $confirm: ConfirmDialogService
  $download: DownloadService
  $drawer: DrawerService
  $form: FormService
  $globalStore: GlobalStore
  $routes: RoutesService
  $snackbar: SnackbarService
  $tabs: TabService
}>

export default defineNuxtPlugin((context, inject) => {
  inject('alert', createAlertService(context))
  inject('api', createApiService(context))
  inject('breadcrumbs', createBreadcrumbsService())
  inject('confirm', createConfirmDialogService())
  inject('download', createDownloadService())
  inject('drawer', createDrawerService())
  inject('form', createFormService(context))
  inject('globalStore', createGlobalStore(context))
  inject('routes', createRoutesService(context))
  inject('snackbar', createSnackbarService())
  inject('tabs', createTabService())
})
