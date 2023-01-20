<div class="assistance-goal-wrapper">
    <div class="assistance-goal-table">
        <table>
            <tbody>
                <tr>
                    <th>ご本人の希望</th>
                    <td>{{ $project->requestFromUser }}</td>
                </tr>
                <tr>
                    <th>ご家族の希望</th>
                    <td>{{ $project->requestFromFamily }}</td>
                </tr>
                <tr>
                    <th>援助目標</th>
                    <td>{{ $project->objective }}</td>
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
            .assistance-goal-table td {
                padding-left: 1mm;
                text-align: left;
            }
        </style>
    @endpush
@endonce
