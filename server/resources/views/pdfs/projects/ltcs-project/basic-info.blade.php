<div class="basic-info-wrapper">
    <div class="created-at">作成年月日：{{ $project->createdAt->format('Y年m月d日') }}</div>
    <div class="basic-info-table">
        <table>
            <tbody>
                <tr>
                    <th>フリガナ</th>
                    <td>{{ $user->name->phoneticFamilyName . $user->name->phoneticGivenName }}</td>
                    <th>性別</th>
                    <th>生年月日</th>
                    <th>事業所名</th>
                    <th>計画作成者（サ責）</th>
                </tr>
                <tr>
                    <th>氏名</th>
                    <td>{{ $user->name->familyName . $user->name->givenName }}</td>
                    <td>
                        @if($user->sex === \Domain\Common\Sex::notKnown()) 不明 @endif
                        @if($user->sex === \Domain\Common\Sex::male()) 男性 @endif
                        @if($user->sex === \Domain\Common\Sex::female()) 女性 @endif
                        @if($user->sex === \Domain\Common\Sex::notApplicable()) 適用不能 @endif
                    </td>
                    <td>{{ $user->birthday->format('Y年m月d日') }}</td>
                    <td>{{ $office->name }}</td>
                    <td>{{ $staff->name->familyName . $staff->name->givenName }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@once
    @push('css')
        <style>
            .basic-info-wrapper {
                margin-bottom: 5mm;
            }
            .created-at {
                height: 5mm;
                text-align: right;
            }
            .basic-info-table table {
                width: 100%;
            }
            .basic-info-table th {
                background-color: whitesmoke;
            }
            .basic-info-table tr:nth-child(2) td {
                height: 10mm;
            }
        </style>
    @endpush
@endonce
