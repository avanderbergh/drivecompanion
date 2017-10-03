<nav class="navbar navbar-default" v-show="selected_view == 'assignments'">
    <form class="navbar-form" @submit.prevent>
        <div class="col-sm-2">
                <select class="form-control" v-model="selected_assignment" @change="getAssignmentFiles(selected_assignment)" style="max-width: 100%;">
                <option value="" disabled selected>Select an Assignment</option>
                <option v-for="a in assignments" :value="a.id">@{{ a.name }}</option>
                </select>
        </div>
        <div class="col-sm-10">
            <div class="student-select" v-show="selected_view == 'assignments' && assignment_files">
                <ul>
                    <li v-for="file in assignment_files">
                        <a href="#" @click="openAssignmentFile(file)">
                        <img :src="file.enrollment.user.picture"
                             :title="file.enrollment.user.name"
                             data-toggle="tooltip"
                             data-placement="bottom"
                                >
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </form>
</nav>