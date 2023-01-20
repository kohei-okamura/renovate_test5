<div class="signs-wrapper">
    <div class="user-sign-table">
        <table>
            <tbody>
                <tr><td class="note">上記計画の内容について説明を受け、同意の上交付を受けました。</td></tr>
                <tr>
                    <td class="date">
                        <span></span>年
                        <span></span>月
                        <span></span>日
                    </td>
                </tr>
                <tr><td class="name"><p>ご本人氏名：</p></td></tr>
                <tr><td class="name"><p>ご家族氏名：</p></td></tr>
            </tbody>
        </table>
    </div>
</div>

@once
    @push('css')
        <style>
            .signs-wrapper {
                font-size: 10px;
                height: 45mm;
                position: absolute;
                bottom: 0;
            }
            .user-sign-table {
                float: right;
                width: 50%;
            }
            .user-sign-table table {
                width: 100%;
            }
            .user-sign-table td {
                border: none;
                text-align: left;
                vertical-align: bottom;
            }
            .user-sign-table td.note {
                height: 10mm;
                vertical-align: top;
            }
            .user-sign-table td.date {
                height: 5mm;
                text-align: right;
            }
            .user-sign-table td.date span {
                display: inline-block;
                width: 15%;
            }
            .user-sign-table td.name {
                height: 15mm;
            }
            .user-sign-table td.name p {
                border-bottom: 1px solid black;
            }
        </style>
    @endpush
@endonce
