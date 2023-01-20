@extends('pdfs.base')

@section('title', '障害福祉サービス計画書')

@section('content')
    @if(empty($programsPerPage))
        <section class="sheet">
            <div class="content-wrapper">
                @include('pdfs.projects.dws-project.title')
                @include('pdfs.projects.dws-project.basic-info')
                @include('pdfs.projects.dws-project.assistance-goal')
                @include('pdfs.projects.dws-project.service-category')
                @include('pdfs.projects.dws-project.weekly-project')
                @include('pdfs.projects.dws-project.service-detail', ['programs' => $project->programs])
                @include('pdfs.projects.dws-project.sign')
            </div>
        </section>
    @else
        <section class="sheet">
            <div class="content-wrapper">
                @include('pdfs.projects.dws-project.title', ['no' => 1])
                @include('pdfs.projects.dws-project.basic-info')
                @include('pdfs.projects.dws-project.assistance-goal')
                @include('pdfs.projects.dws-project.service-category')
                @include('pdfs.projects.dws-project.weekly-project')
                @include('pdfs.projects.dws-project.service-detail', ['programs' => isset($programsPerPage[\Domain\Project\DwsProject::FIRST_PAGE]) ? $programsPerPage[\Domain\Project\DwsProject::FIRST_PAGE]: []])
            </div>
        </section>
        @foreach($programsPerPage as $page => $programs)
            @continue($page === \Domain\Project\DwsProject::FIRST_PAGE)
            <section class="sheet">
                <div class="content-wrapper">
                    @include('pdfs.projects.dws-project.title', ['no' => $page])
                    @include('pdfs.projects.dws-project.service-detail', ['programs' => $programs])
                    @if($remain >= $sign && $loop->last)
                        @include('pdfs.projects.dws-project.sign')
                    @endif
                </div>
            </section>
        @endforeach
        @if($remain < $sign)
            <section class="sheet">
                <div class="content-wrapper">
                    @include('pdfs.projects.dws-project.sign')
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
