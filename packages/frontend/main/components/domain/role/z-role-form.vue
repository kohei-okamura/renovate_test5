<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-form data-z-role-form data-form :class="$style.root" @submit.prevent="submit">
    <validation-observer ref="observer" tag="div">
      <z-form-card title="基本情報">
        <z-form-card-item-set :icon="$icons.role">
          <z-form-card-item v-slot="{ errors }" data-name vid="name" :rules="rules.name">
            <z-text-field
              v-model.trim="form.name"
              label="ロール名 *"
              :error-messages="errors"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.scope">
          <z-form-card-item v-slot="{ errors }" data-scope vid="scope" :rules="rules.scope">
            <z-select
              v-model="form.scope"
              label="権限範囲 *"
              :error-messages="errors"
              :items="scopes"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.admin">
          <z-form-card-item v-slot="{ errors }" data-is-system-admin vid="isSystemAdmin" :rules="rules.isSystemAdmin">
            <v-checkbox v-model="form.isSystemAdmin" label="システム管理権限" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
        <v-expand-transition>
          <div v-show="!form.isSystemAdmin" class="pt-4">
            <v-divider />
            <v-subheader>詳細権限</v-subheader>
            <v-list dense>
              <template v-for="g in permissionGroups">
                <v-list-group :key="g.id">
                  <template #activator>
                    <v-list-item-action @click.stop="">
                      <v-checkbox
                        :data-permission-group-checkbox="g.id"
                        :indeterminate="permissionGroupIndeterminate[g.code]"
                        :input-value="permissionGroupValue[g.code]"
                        @change="setPermissionGroupValue(g, $event)"
                      />
                    </v-list-item-action>
                    <v-list-item-content>
                      <v-list-item-title>権限：{{ g.displayName }}</v-list-item-title>
                    </v-list-item-content>
                  </template>
                  <v-list-item v-for="p in g.permissions" :key="p">
                    <v-list-item-action>
                      <v-checkbox v-model="form.permissions[p]" />
                    </v-list-item-action>
                    <v-list-item-content>
                      <v-list-item-title>{{ resolvePermission(p) }}</v-list-item-title>
                    </v-list-item-content>
                  </v-list-item>
                </v-list-group>
              </template>
            </v-list>
          </div>
        </v-expand-transition>
      </z-form-card>
      <z-form-action-button :disabled="progress" :icon="$icons.save" :loading="progress" :text="buttonText" />
    </validation-observer>
  </v-form>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { resolvePermission } from '@zinger/enums/lib/permission'
import { RoleScope } from '@zinger/enums/lib/role-scope'
import { assign } from '@zinger/helpers'
import Vue from 'vue'
import { enumerableOptions } from '~/composables/enumerable-options'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { usePermissionGroups } from '~/composables/use-permission-groups'
import { PermissionGroup } from '~/models/permission-group'
import { RolesApi } from '~/services/api/roles-api'
import { required } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Props = FormProps<RolesApi.Form> & Readonly<{
  buttonText: string
}>

export default defineComponent<Props>({
  name: 'ZRoleForm',
  props: {
    ...getFormPropsOptions(),
    buttonText: { type: String, required: true }
  },
  setup (props, context) {
    const { form, observer, submit } = useFormBindings(props, context, {
      init: form => ({
        isSystemAdmin: form.isSystemAdmin ?? false,
        permissions: form.permissions ?? {}
      }),
      processOutput: output => output.isSystemAdmin ? assign(output, { permissions: {} }) : output
    })
    const { permissionGroups } = usePermissionGroups()
    const permissionGroupIndeterminate = computed(() => {
      return Object.fromEntries(permissionGroups.value.map(g => {
        const permissions = form.permissions ?? {}
        const value = g.permissions.some(x => permissions[x]) && g.permissions.some(x => !permissions[x])
        return [g.code, value]
      }))
    })
    const permissionGroupValue = computed(() => {
      return Object.fromEntries(permissionGroups.value.map(g => {
        const permissions = form.permissions ?? {}
        return [g.code, g.permissions.every(x => permissions[x])]
      }))
    })
    const rules = validationRules({
      name: { required, max: 100 },
      isSystemAdmin: {},
      scope: { required }
    })
    const setPermissionGroupValue = (group: PermissionGroup, value: boolean) => {
      group.permissions.forEach(x => Vue.set(form.permissions!, x, value))
    }
    return {
      form,
      observer,
      permissionGroupIndeterminate,
      permissionGroups,
      permissionGroupValue,
      resolvePermission,
      rules,
      scopes: enumerableOptions(RoleScope),
      setPermissionGroupValue,
      submit
    }
  }
})
</script>

<style lang="scss" module>
.root {
  :global {
    .v-list-item__action,
    .v-list.v-list--dense .v-list-item .v-list-item__icon {
      margin-bottom: 12px;
      margin-top: 12px;
    }

    .v-list-group__items {
      margin-left: 56px;
    }
  }
}
</style>
