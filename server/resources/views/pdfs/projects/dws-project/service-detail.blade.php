<div class="service-detail-wrapper">
    @foreach($programs as $program)
        <div class="service-detail-table">
            <table>
                <tbody>
                    <tr><th colspan="4">サービス詳細（No.{{ $program->summaryIndex }}）</th></tr>
                    <tr>
                        <th class="service-menu">サービス項目</th>
                        <th class="content">サービス内容</th>
                        <th class="duration">所要時間</th>
                        <th class="memo">留意事項</th>
                    </tr>

                    @foreach($program->contents as $content)
                        @php $serviceMenu = $serviceMenus[$content->menuId]->head(); @endphp
                        <tr>
                            <td>{{ $serviceMenu->displayName }}</td>
                            <td>{{ $content->content }}</td>
                            <td class="duration">{{ $content->duration }}分</td>
                            <td>{{ $content->memo }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</div>

@once
    @push('css')
        <style>
            .service-detail-wrapper {
                margin-bottom: 5mm;
            }
            .service-detail-table table {
                width: 100%;
                margin-bottom: 5mm;
            }
            .service-detail-table tr:first-child th {
                background-color: lightgray;
            }
            .service-detail-table tr:nth-child(2) th {
                background-color: whitesmoke;
            }
            .service-detail-table td {
                height: 15mm;
                /* マルチプリント判定をするときに、「留意事項」の文字数でズレが生じる可能性がある。
                暫定回避策として、予めテキスト4行分くらい(100文字程度)が収まる height を設定しておく。
                なお、dompdf で min-height は効かないため、height を指定している。
                100文字以上、5行分のテキストの場合、セルは下に伸びる。 */
                padding-left: 1mm;
                padding-right: 2mm;
                text-align: left;
            }
            .service-detail-table th.service-menu {
                width: 20mm;
            }
            .service-detail-table th.duration {
                width: 15mm;
            }
            .service-detail-table td.duration {
                text-align: center;
            }
        </style>
    @endpush
@endonce
