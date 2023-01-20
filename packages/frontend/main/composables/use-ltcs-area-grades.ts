/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed } from '@nuxtjs/composition-api'
import { useLtcsAreaGradesStore } from '~/composables/stores/use-ltcs-area-grades-store'
import { useBackgroundLoader } from '~/composables/use-background-loader'
import { LtcsAreaGrade, LtcsAreaGradeId } from '~/models/ltcs-area-grade'

const createLtcsAreaGradeResolver = (xs: LtcsAreaGrade[] = []) => (id: LtcsAreaGradeId | undefined, alternative = '-') => {
  return (id && xs.find(x => x.id === id)?.name) ?? alternative
}

export const useLtcsAreaGrades = () => {
  const store = useLtcsAreaGradesStore()
  const state = store.state
  const ltcsAreaGrades = state.ltcsAreaGrades
  if (ltcsAreaGrades.value.length === 0) {
    useBackgroundLoader(() => store.getIndex())
  }
  const ltcsAreaGradeOptions = computed(() => ltcsAreaGrades.value.map(x => ({ text: x.name, value: x.id })))
  const resolveLtcsAreaGrade = computed(() => createLtcsAreaGradeResolver(ltcsAreaGrades.value))
  return {
    ltcsAreaGradeOptions,
    isLoadingLtcsAreaGrades: state.isLoadingLtcsAreaGrades,
    resolveLtcsAreaGrade
  }
}
