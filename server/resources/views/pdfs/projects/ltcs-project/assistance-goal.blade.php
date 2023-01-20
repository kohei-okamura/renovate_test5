<div class="assistance-goal-wrapper">
    <div class="assistance-goal-table">
        <table>
            <tbody>
                <tr>
                    <th colspan="3">援助目標</th>
                </tr>
                <tr>
                    <th>解決すべき課題</th>
                    <td colspan="2">{{ $project->problem }}</td>
                </tr>
                <tr>
                    <th>ご本人の希望</th>
                    <td colspan="2">{{ $project->requestFromUser }}</td>
                </tr>
                <tr>
                    <th>ご家族の希望</th>
                    <td colspan="2">{{ $project->requestFromFamily }}</td>
                </tr>
                <tr>
                    <th>長期目標</th>
                    <td class="goal-term">
                        {{ $project->longTermObjective->term->start->format('Y年m月d日') }}から
                        {{ $project->longTermObjective->term->end->format('Y年m月d日') }}まで
                    </td>
                    <td>
                        {{ $project->longTermObjective->text }}
                    </td>
                </tr>
                <tr>
                    <th>短期目標</th>
                    <td class="goal-term">
                        {{ $project->shortTermObjective->term->start->format('Y年m月d日') }}から
                        {{ $project->shortTermObjective->term->end->format('Y年m月d日') }}まで
                    </td>
                    <td>
                        {{ $project->shortTermObjective->text }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@once
    @push('css')
        <style>
            .assistance-goal-wrapper {
                margin-bottom: 5mm;
            }
            .assistance-goal-table table {
                width: 100%;
            }
            .assistance-goal-table th {
                width: 25mm;
                height: 10mm;
                background-color: whitesmoke;
            }
            .assistance-goal-table tr:first-child th {
                width: auto;
                height: 5mm;
                background-color: lightgray;
            }
            .assistance-goal-table td {
                padding-left: 1mm;
                text-align: left;
            }
            .assistance-goal-table td.goal-term {
                width: 30mm;
            }
        </style>
    @endpush
@endonce
