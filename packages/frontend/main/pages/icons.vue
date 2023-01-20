<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page data-page-icons-index :class="$style.root" :breadcrumbs="[]">
    <z-data-table :items="icons" :options="tableOptions">
      <template #item.icon="{ item }">
        <v-icon>{{ item.icon }}</v-icon>
      </template>
    </z-data-table>
  </z-page>
</template>

<script lang="ts">
import { defineComponent, useCssModule } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { keys } from '@zinger/helpers'
import { dataTableOptions } from '~/composables/data-table-options'
import { auth } from '~/middleware/auth'
import { Role } from '~/models/role'
import { $icons } from '~/plugins/icons'

export default defineComponent({
  name: 'RolesIndexPage',
  middleware: [auth(Permission.listRoles)],
  setup () {
    const style = useCssModule()
    const descriptions: Record<keyof typeof $icons, string> = {
      add: '「追加」を表すボタン等に用いる。',
      addr: '「住所」を表す項目等に用いる',
      admin: '「管理者」を表す項目等に用いる。',
      alert: '「警告」を表すメッセージなどに用いる。',
      amount: '「提供量」などの「量」を表す項目等に用いる。',
      back: '「戻る」ボタン等に用いる。',
      bank: '「銀行」に関連する項目等に用いる。',
      billing: '「請求」を表すメニュー等に用いる。',
      birthday: '「生年月日」を表す項目に用いる。',
      blank: 'アイコンが必要な箇所でアイコンを表示したくない場合に用いる。',
      carePlanAuthor: '「居宅介護支援」所謂ケアマネに関する項目等に用いる。',
      category: 'カテゴリや区分を表す項目等に用いる。',
      certification: 'スタッフの「資格」を表す項目等に用いる。',
      city: '団体としての「市町村」「自治体」等を表す項目に用いる。',
      close: '「閉じる」ボタン等に用いる。',
      completed: '「完了」を表すメッセージ等に用いる。',
      comprehensiveService: '「総合事業」を表す項目等に用いる。',
      confirmed: '「確認済」であることを表す項目等に用いる。',
      copayLimit: '利用者負担等の「上限」を表す項目等に用いる。',
      copy: '「コピー」「複製」を表すボタン等に用いる。',
      csv: '「CSV ファイル」を表す項目等に用いる。',
      dashboard: '「ダッシュボード」を表すメニュー等に用いる。',
      date: '「日時」「年月日」など日付関連を値を表す項目等に用いる。',
      dateEnd: '「終了日」「中止日」など期間の終了を表す項目に用いる。',
      dateRange: '「日時」「年月日」など日付関連の範囲を表す項目等に用いる。',
      dateStart: '「契約日」「開始年月日」など期間の開始を表す項目に用いる。',
      dayOfWeek: '「曜日」を表す項目等に用いる。',
      days: '「日数」を表す項目等に用いる。',
      defrayerCategory: '「法別番号」「公費制度」を表す項目等に用いる。',
      delete: '「削除」を表すボタン等に用いる。',
      disableContract: '「契約」の無効化を表すボタン等に用いる。',
      document: '「ドキュメント」を表す項目等に用いる。',
      download: '「ダウンロード」を表すボタン等に用いる。',
      dws: '「障害福祉サービス」「障害者総合支援」関連の値を表す項目等に用いる。',
      dwsNumber: '「受給者証番号」を表す項目に用いる。',
      dwsType: '「障害種別」を表す項目に用いる。',
      edit: '「編集」「更新」を表すボタン等に用いる。',
      editVariant: '「編集」「更新」を表すボタン等に用いる。同じスピードダイアルで `edit` が既に使われている場合用。',
      editContract: '「契約」の編集を表すボタンに用いる。',
      email: '「メールアドレス」を表す項目等に用いる。',
      employeeNumber: 'スタッフの「社員番号」を表す項目に用いる。',
      expand: '「開く」動作を表すボタン等に用いる。',
      filter: '検索フォームを表す場合等に用いる。',
      fix: '「確定」を表すボタン等に用いる。',
      forward: '「進む」ボタン等に用いる。',
      headcount: '「人数」を表す項目等に用いる。',
      help: 'ヘルプメッセージを表示する場合に用いる。',
      id: '「ID」を表す項目等に用いる。',
      invisible: '「表示・非表示」が切り替え可能な場合の「非表示」を表す場合に用いる。',
      issuedOn: '「交付日」を表す項目に用いる。',
      keyword: '「キーワード」を表す項目等に用いる。',
      ledger: '「台帳」を表すメニュー等に用いる。',
      level: '「要介護度」「障害支援区分」など何らかの「程度」を表す項目に用いる。',
      ltcs: '介護保険関連の値を表す項目等に用いる。',
      ltcsInsNumber: '「被保険者証番号」を表す項目に用いる。',
      month: '「サービス提供年月」等の「月」を表す項目に用いる。',
      more: '「もっと見る」のようなアクションを表すボタン等に用いる。',
      moveRight: '「右へ移動させる」のようなアクションを表すボタン等に用いる。',
      note: '「備考」を表す項目等に用いる。',
      notification: '「通知」を表すボタン等に用いる。',
      number: '「番号」を表す項目等に用いる。',
      objective: '「目標」を表す項目等に用いる。',
      office: '「事業所」を表す項目等に用いる。',
      ownExpenseProgram: '「自費サービス」を表す項目等に用いる。',
      password: '「パスワード」を表す項目等に用いる。',
      pdf: '「PDF ファイル」を表す項目等に用いる。',
      personalName: '利用者やスタッフ以外の「氏名」や「担当者名」を表す項目等に用いる。',
      problem: '「解決すべき課題」を表す項目等に用いる。',
      provisionReport: '「予実」を表す項目等に用いる。',
      ratio: '「利用者負担割合」等の「割合」を表す項目に用いる。',
      recipientNumber: '公費等の「受給者番号」を表す項目に用いる。',
      recurrence: '「周期」を表す項目等に用いる。',
      refresh: '「更新」を表す項目等に用いる。',
      request: '計画の「ご本人の希望」等の項目に用いる。',
      role: '「ロール」を表す項目等に用いる。',
      save: '「保存」等を表すボタンに用いる。',
      schedule: '日付と時刻の両方を伴うスケジュールを表す項目等に用いる。',
      scope: '「権限範囲」を表す項目に用いる。',
      score: '請求における「単位」を表す項目に用いる。',
      send: '「送信」を表すボタン等に用いる。',
      serviceCode: '「サービスコード」を表す項目等に用いる。',
      serviceOption: '「サービスオプション」を表す項目等に用いる。',
      settings: '「設定」を表すメニュー等に用いる。',
      sex: '「性別」を表す項目等に用いる。',
      shift: '「勤務シフト」を表す項目等に用いる。',
      shrink: '「開く」に対応する「閉じる」動作を表すボタン等に用いる。',
      sortable: '並び替え可能なリストにおいて並び替えが可能であることを示すアイコンとして用いる。',
      staff: '「スタッフ」を表す項目等に用いる。',
      statusChecking: '「入力中」等の状態を表すアイコンとして用いる。',
      statusDisabled: '「無効」等の状態を表すアイコンとして用いる。',
      statusNotAvailable: '「未作成」等の状態を表すアイコンとして用いる。',
      statusProgress: '「処理中」等の状態を表すアイコンとして用いる。',
      statusReady: '「未確定」等の準備ができている状態を表すアイコンとして用いる。',
      statusRejected: '「無効」等の状態を表すアイコンとして用いる。',
      statusResolved: '「確定」等の状態を表すアイコンとして用いる。',
      statusSuspended: '「保留」等の状態を表すアイコンとして用いる。',
      statusUnknown: '状態がわからないことを表すアイコンとして用いる。',
      tel: '「電話番号」等を表す項目に用いる。',
      terminateContract: '契約の「終了」を表すボタン等に用いる。',
      text: '「備考」等の文章を入力する項目等に用いる。',
      timeAmount: '時間の「量」を表す項目等に用いる。',
      timeframe: '夜間・日中などの「時間帯」を表す項目等に用いる。',
      undo: '確定した請求を未確定に戻す等の「アンドゥ」操作を表すボタン等に用いる。',
      upload: '「アップロード」を表すボタン等に用いる。',
      user: '「利用者」を表す項目等に用いる。',
      visible: '「表示・非表示」が切り替え可能な場合の「表示」を表す場合に用いる。',
      wallet: '「支払い方法」等を表す項目等に用いる。',
      xlsx: '「エクセルファイル」を表す項目等に用いる。',
      yen: '金額を表す項目等に用いる。'
    }
    const icons = keys($icons).map(name => ({
      name,
      icon: $icons[name],
      description: descriptions[name]
    }))
    const tableOptions = dataTableOptions<Role>({
      content: 'アイコン',
      headers: [
        { text: '物理名', value: 'name', class: style.name, align: 'start', width: 100 },
        { text: 'アイコン', value: 'icon', class: style.icon, align: 'center', width: 100 },
        { text: '用途', value: 'description', class: style.description, align: 'start' }
      ]
    })
    return {
      icons,
      tableOptions
    }
  }
})
</script>

<style lang="scss" module>
.root {
  .name, .icon, .description {
    width: auto;
  }

}
</style>
