<nav class="navbar navbar-default navbar-static-top" v-show="selected_view == 'student-folders'">
    <div class="container-fluid">
        @include('teacher.partials.loading_notices')
        <form class="navbar-form navbar-right" @submit.prevent>
            <select v-model="show_files" class="form-control">
                <option v-for="n in 15" :value="n+1">@{{ n+1 }} @{{ n+1 | pluralize 'file' }}</option>
            </select>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                <input type="text" class="form-control" id="search_student" placeholder="Search for a student" v-model="search.student">
            </div>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-file-text"></i></span>
                <input type="text" class="form-control" placeholder="Search for a file" v-model="search.file">
            </div>
        </form>
    </div>
</nav>