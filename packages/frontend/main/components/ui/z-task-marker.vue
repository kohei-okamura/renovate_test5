<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<script lang="ts">
import { parseEnum } from '@zinger/enums/lib/enum'
import { resolveTask, Task } from '@zinger/enums/lib/task'
import { VNode, VNodeData } from 'vue'
import { defineFunctionalComponent } from '~/components/tools/define-functional-component'

const contains = (task: Task, xs: Task[]): boolean => xs.includes(task)

function generateData (task: Task, data: VNodeData): VNodeData {
  return {
    ...data,
    class: {
      ...data.class,
      'z-task-marker': true,
      'z-task-marker--dws': contains(task, [
        Task.dwsPhysicalCare,
        Task.dwsHousework,
        Task.dwsAccompanyWithPhysicalCare,
        Task.dwsAccompany,
        Task.dwsVisitingCareForPwsd
      ]),
      'z-task-marker--pwsd': task === Task.dwsVisitingCareForPwsd,
      'z-task-marker--ltcs': contains(task, [
        Task.ltcsPhysicalCare,
        Task.ltcsHousework,
        Task.ltcsPhysicalCareAndHousework
      ]),
      'z-task-marker--comp': task === Task.comprehensive,
      'z-task-marker--comm': contains(task, [Task.commAccompanyWithPhysicalCare, Task.commAccompany]),
      'z-task-marker--own': task === Task.ownExpense,
      'z-task-marker--visit': contains(task, [Task.fieldwork, Task.assessment, Task.visit]),
      'z-task-marker--other': contains(task, [Task.officeWork, Task.sales, Task.meeting, Task.other])
    }
  }
}

export default defineFunctionalComponent({
  name: 'ZTaskMarker',
  functional: true,
  props: {
    task: { type: Number, required: true }
  },
  render (h, { data, props }): VNode {
    const task = parseEnum(props.task, Task)
    return h('div', generateData(task, data), resolveTask(task))
  }
})
</script>

<style lang="scss" module>
@import '~vuetify/src/styles/settings/colors';

:global {
  .z-task-marker {
    background-color: map-get($grey, 'darken-2');
    border-radius: 2px;
    color: map-get($shades, 'white');
    font: {
      size: 12px;
      weight: bold;
    }
    max-width: 12em;
    padding: 2px 4px;
    text-align: center;

    &--dws {
      background-color: map-get($deep-orange, 'darken-1');
    }

    &--pwsd {
      background-color: map-get($deep-purple, 'darken-2');
    }

    &--ltcs {
      background-color: map-get($green, 'darken-2');
    }

    &--comp {
      background-color: map-get($light-green, 'darken-1');
    }

    &--comm {
      background-color: map-get($blue, 'darken-3');
    }

    &--own {
      background-color: map-get($teal, 'darken-2');
    }

    &--visit {
      background-color: map-get($brown, 'lighten-1');
    }
  }
}
</style>
