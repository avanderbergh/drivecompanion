@extends('app')

@section('content')
    <div id="app_teacher">
        @include('teacher.partials.navbar')
        @include('teacher.partials.student_folders_navbar')
        @include('teacher.partials.assignments_navbar')
        @include('teacher.partials.progressbars')
        @include('teacher.partials.assignments')
        @include('teacher.partials.errors')
        <div class="container-fluid">
            @include('teacher.partials.student_folders')
        </div>
        @include('partials.footer')
        @if(env('APP_ENV')=='local')
            <pre>@{{ $data | json }}</pre>
        @endif
        @include('teacher.modals.create_assignment')
    </div>
    @include('partials.phpvars')
    <script src="{{ elixir('js/teacher/bundle.js') }}"></script>
@stop