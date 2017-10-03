@extends('app')

@section('content')
    <div id="app_student">
        @include('student.partials.navbar')
        <div v-if="updating_files">
            <div class="text-center" style="margin-top: 100px; margin-bottom: 100px">
                <i class="fa fa-spinner fa-spin fa-5x"></i>
            </div>
        </div>
        <div v-else>
            <div v-if="files.length">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th></th>
                        <th>Name</th>
                        <th>Last Modified</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="file in files" v-on:dblclick="rowDoubleClicked(file)" v-on:click="rowClicked(file)" :class="{'info': file.id == selected.id}">
                        <td align="right"><img :src="file.iconLink"></td>
                        <td><a href="#">@{{ file.name }}</a></td>
                        <td>@{{ file.modifiedTime | moment "from"}}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div v-else>
                <div class="row">
                    <div class="col-sm-4 col-sm-offset-4 text-center" style="margin-top: 100px; margin-bottom: 100px">
                        <p class="lead">Your folder for this course is empty</p>
                        <p><a href="http://drive.google.com/drive/folders/{{$enrollment->folder_id}}" class="btn btn-primary btn-outline"><i class="fa fa-external-link"></i> Open your folder</a></p>
                        <p>Any Google Drive Document you or your teacher creates or moves into this folder will appear here.</p>
                    </div>
                </div>
            </div>
        </div>
        @include('partials.footer')
    </div>
    @include('partials.phpvars')
    <script src="{{ elixir('js/student/bundle.js') }}"></script>
@stop