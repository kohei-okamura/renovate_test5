@extends('pdfs.base')

@section('title', '介護保険サービス計画書')

@section('content')
    @if(empty($programsPerPage))
        <section class="sheet">
            <div class="content-wrapper">
                @include('pdfs.projects.ltcs-project.title')
                @include('pdfs.projects.ltcs-project.basic-info')
                @include('pdfs.projects.ltcs-project.assistance-goal')
                @include('pdfs.projects.ltcs-project.weekly-project')
                @include('pdfs.projects.ltcs-project.service-detail', ['programs' => $project->programs])
                @include('pdfs.projects.ltcs-project.sign')
            </div>
        </section>
    @else
        <section class="sheet">
            <div class="content-wrapper">
                @include('pdfs.projects.ltcs-project.title', ['no' => 1])
                @include('pdfs.projects.ltcs-project.basic-info')
                @include('pdfs.projects.ltcs-project.assistance-goal')
                @include('pdfs.projects.ltcs-project.weekly-project')
                @include('pdfs.projects.ltcs-project.service-detail', ['programs' => isset($programsPerPage[\Domain\Project\LtcsProject::FIRST_PAGE]) ? $programsPerPage[\Domain\Project\LtcsProject::FIRST_PAGE]: []])
            </div>
        </section>
        @foreach($programsPerPage as $page => $programs)
            @continue($page === \Domain\Project\LtcsProject::FIRST_PAGE)
            <section class="sheet">
                <div class="content-wrapper">
                    @include('pdfs.projects.ltcs-project.title', ['no' => $page])
                    @include('pdfs.projects.ltcs-project.service-detail', ['programs' => $programs])
                    @if($remain >= $sign && $loop->last)
                        @include('pdfs.projects.ltcs-project.sign')
                    @endif
                </div>
            </section>
        @endforeach
        @if($remain < $sign)
            <section class="sheet">
                <div class="content-wrapper">
                    @include('pdfs.projects.ltcs-project.sign')
                </div>
            </section>
        @endif
    @endif
@endsection

@push('css')
    <style>
        .content-wrapper {
            font-size: 11px;
            padding: 10mm;
            height: 272mm;
        }
        table th, table td {
            height: 5mm;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #000000;
        }
    </style>
@endpush
