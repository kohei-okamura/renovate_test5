/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed } from '@nuxtjs/composition-api'
import { useDwsAreaGradesStore } from '~/composables/stores/use-dws-area-grades-store'
import { useBackgroundLoader } from '~/composables/use-background-loader'
import { DwsAreaGrade, DwsAreaGradeId } from '~/models/dws-area-grade'

const createDwsAreaGradeResolver = (xs: DwsAreaGrade[] = []) => (id: DwsAreaGradeId | undefined, alternative = '-') => {
  return (id && xs.find(x => x.id === id)?.name) ?? alternative
}

export const useDwsAreaGrades = () => {
  const store = useDwsAreaGradesStore()
  const state = store.state
  const dwsAreaGrades = state.dwsAreaGrades
  if (dwsAreaGrades.value.length === 0) {
    useBackgroundLoader(() => store.getIndex())
  }
  const dwsAreaGradeOptions = computed(() => dwsAreaGrades.value.map(x => ({ text: x.name, value: x.id })))
  const resolveDwsAreaGrade = computed(() => createDwsAreaGradeResolver(dwsAreaGrades.value))
  return {
    dwsAreaGradeOptions,
    isLoadingDwsAreaGrades: state.isLoadingDwsAreaGrades,
    resolveDwsAreaGrade
  }
}
