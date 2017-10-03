@extends('app')

@section('content')
    <div class="container">
        @if ($enrollments)
            <h1>Please choose your class</h1>
            <p>If you don't see your class here, your course instrcutor might not have set up a Drive Companion Folder for it yet...</p>
            <ul class="list-group">
            @foreach ($enrollments as $enrollment)
                <li class="list-group-item"><a href="{{url('app/student/'.$enrollment->section->id,[], True)}}">{{$enrollment->section->name}}</a></li>
            @endforeach
            </ul>
        @else
            <h1><br>Oh no!</h1>
            <p class="lead">Could not find any of your classes using Drive Companion.</p>
        @endif
    </div>
@stop
