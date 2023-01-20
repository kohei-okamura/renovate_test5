<div class="weekly-project-wrapper">
    <div class="weekly-project-table">
        <table>
            <tbody>
                <tr><th colspan="10">週間サービス計画表</th></tr>
                <tr>
                    <th class="summary-index">No.</th>
                    <th class="slot">時間帯</th>
                    <th class="day-of-week">月</th>
                    <th class="day-of-week">火</th>
                    <th class="day-of-week">水</th>
                    <th class="day-of-week">木</th>
                    <th class="day-of-week">金</th>
                    <th class="day-of-week">土</th>
                    <th class="day-of-week">日</th>
                    <th>備考</th>
                </tr>
                @foreach($project->programs as $program)
                    <tr>
                        <td>{{ $program->summaryIndex }}</td>
                        <td>{{ $program->slot->start }}〜{{ $program->slot->end }}</td>
                        <td>@if(in_array(\Domain\Common\DayOfWeek::mon(), $program->dayOfWeeks)) ● @endif</td>
                        <td>@if(in_array(\Domain\Common\DayOfWeek::tue(), $program->dayOfWeeks)) ● @endif</td>
                        <td>@if(in_array(\Domain\Common\DayOfWeek::wed(), $program->dayOfWeeks)) ● @endif</td>
                        <td>@if(in_array(\Domain\Common\DayOfWeek::thu(), $program->dayOfWeeks)) ● @endif</td>
                        <td>@if(in_array(\Domain\Common\DayOfWeek::fri(), $program->dayOfWeeks)) ● @endif</td>
                        <td>@if(in_array(\Domain\Common\DayOfWeek::sat(), $program->dayOfWeeks)) ● @endif</td>
                        <td>@if(in_array(\Domain\Common\DayOfWeek::sun(), $program->dayOfWeeks)) ● @endif</td>
                        <td>{{ $program->note }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@once
    @push('css')
        <style>
            .weekly-project-wrapper {
                margin-bottom: 5mm;
            }
            .weekly-project-table table {
                width: 100%;
            }
            .weekly-project-table tr:first-child th {
                background-color: lightgray;
            }
            .weekly-project-table tr:nth-child(2) th {
                background-color: whitesmoke;
            }
            .weekly-project-table th.summary-index,
            .weekly-project-table th.day-of-week {
                width: 5mm;
            }
            .weekly-project-table th.slot {
                width: 20mm;
            }
            .weekly-project-table td:last-child {
                padding-left: 1mm;
                text-align: left;
            }
        </style>
    @endpush
@endonce
