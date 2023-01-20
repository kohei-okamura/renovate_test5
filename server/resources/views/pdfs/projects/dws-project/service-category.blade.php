<div class="service-category-wrapper">
    <div class="service-category-table">
        <table>
            <tbody>
                @foreach($project->programs as $i => $program)
                    @if($loop->odd) <tr> @endif
                        @if($loop->first)
                            <th rowspan={{ ceil(count($project->programs) / 2) }}>サービス<br>区分</th>
                        @endif
                        <td class="check-box"><i></i></td>
                        <td class="category">{{ $program->category->resolve($program->category) }}</td>
                        <td class="amount">{{ \ScalikePHP\Seq::fromArray($program->contents)->map(fn (\Domain\Project\DwsProjectContent $x): int => $x->duration)->sum() / 60 }}時間</td>
                    @if($loop->even) </tr> @endif
                    @if($loop->count % 2 === 1 && $loop->last)
                            <td class="check-box"><i></i></td>
                            <td class="category"></td>
                            <td class="amount">時間</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@once
    @push('css')
        <style>
            .service-category-wrapper {
                margin-bottom: 5mm;
            }
            .service-category-table table {
                width: 100%;
                border: 1px solid black;
            }
            .service-category-table th {
                width: 15mm;
                background-color: whitesmoke;
            }
            .service-category-table td {
                border: none;
                border-bottom: 1px solid black;
            }
            .service-category-table td.check-box {
                width: 4mm;
                padding-left: 1mm;
            }
            .service-category-table td.check-box i {
                display: inline-block;
                width: 2mm;
                height: 2mm;
                border: 1px solid black;
            }
            .service-category-table td.category {
                text-align: left;
            }
            .service-category-table td.amount {
                width: 15mm;
                text-align: right;
                border-right: 1px solid black;
                padding-right: 1mm;
            }
        </style>
    @endpush
@endonce
