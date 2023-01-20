<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <div data-z-postcode-resolver :class="$style.root">
    <v-btn color="primary" data-postcode-resolver-button depressed :disabled="isInvalid" @click="onClick">
      <span>住所を自動入力</span>
    </v-btn>
    <v-dialog v-model="data.dialog" data-postcode-resolver-dialog max-width="360px" scrollable>
      <v-card>
        <v-card-title class="text-h6">住所を選択</v-card-title>
        <v-divider />
        <v-card-text class="pa-0" :style="dialogTextStyle">
          <v-radio-group v-model="data.selected" class="ma-0 pa-4" column="column" hide-details>
            <v-radio
              v-for="x in data.postcodes"
              :key="x.prefecture_name + x.city_name + x.town_name"
              :label="x.prefecture_name + x.city_name + x.town_name"
              :value="x"
            />
          </v-radio-group>
        </v-card-text>
        <v-divider />
        <v-card-actions>
          <v-spacer />
          <v-btn color="secondary" data-postcode-resolver-cancel text @click.stop="closeDialog">キャンセル</v-btn>
          <v-btn color="primary" data-postcode-resolver-ok text :disabled="!isSelected" @click.stop="submitDialog">
            <span>決定</span>
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, reactive } from '@nuxtjs/composition-api'
import { parseEnum } from '@zinger/enums/lib/enum'
import { Prefecture } from '@zinger/enums/lib/prefecture'
import { usePlugins } from '~/composables/use-plugins'
import { Addr } from '~/models/addr'
import { Postcode } from '~/models/postcode'

const POSTCODE_PATTERN = /^\d{3}-?\d{4}$/

type Props = Readonly<{
  postcode: string
}>

export default defineComponent<Props>({
  name: 'ZPostcodeResolver',
  props: {
    postcode: { type: String, default: '' }
  },
  setup (props, context) {
    const { $api, $snackbar } = usePlugins()
    const data = reactive({
      dialog: false,
      postcodes: [] as Postcode[],
      selected: undefined as Postcode | undefined
    })
    const dialogTextStyle = computed(() => ({
      height: `${Math.min(data.postcodes.length, 5) * 32 + 24}px`
    }))
    const isInvalid = computed(() => !POSTCODE_PATTERN.test(props.postcode))
    const isSelected = computed(() => !!data.selected)
    const openDialog = (xs: Postcode[]) => {
      data.dialog = true
      data.postcodes = xs
      data.selected = undefined
    }
    const closeDialog = () => {
      data.dialog = false
    }
    const onSelected = (x: Postcode) => {
      const addr: Addr = {
        postcode: x.zip_code,
        prefecture: parseEnum(+x.prefecture_jis_code, Prefecture),
        city: x.city_name,
        street: x.town_name,
        apartment: ''
      }
      context.emit('update', addr)
    }
    const onClick = async () => {
      try {
        const xs = await $api.postcode.get({ postcode: props.postcode })
        if (xs.length === 0) {
          $snackbar.warning('郵便番号に対応する住所が見つかりませんでした。')
        } else if (xs.length === 1) {
          onSelected(xs[0])
        } else {
          openDialog(xs)
        }
      } catch (reason) {
        $snackbar.error('住所の取得に失敗しました。')
      }
    }
    const submitDialog = () => {
      onSelected(data.selected!)
      closeDialog()
    }
    return {
      data,
      dialogTextStyle,
      isInvalid,
      isSelected,
      closeDialog,
      onClick,
      submitDialog
    }
  }
})
</script>

<style lang="scss" module>
.root {
  margin-top: -4px;

  :global {
    .v-btn:not(.v-btn--round).v-size--default {
      height: 34px;
    }
  }
}
</style>
