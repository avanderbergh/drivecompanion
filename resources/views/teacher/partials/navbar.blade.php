<nav class="navbar navbar-default navbar-main navbar-static-top">
    <div class="container-fluid">
        <div clas="navbar-header">
            <a class="navbar-brand">&nbsp;<i class="icon icon-logo fa-lg"></i>&nbsp;Drive Companion</a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li :class="{'active': selected_view == 'student-folders'}"><a href="#" @click="selected_view = 'student-folders'"><i class="fa fa-user fa-lg"></i></a></li>
                <li :class="{'active': selected_view == 'assignments'}"><a href="#" @click="selected_view = 'assignments'"><i class="fa fa-file fa-lg"></i></a></li>
            </ul>
            <form class="navbar-form navbar-right" @submit.prevent>
                <a class="btn btn-primary btn-circle btn-outline"
                   title="Join the Group to get notified about Drive Companion updates"
                   href="https://{{session('schoology')['domain']}}/group/545135057"
                   target="_blank">
                    <i class="fa fa-comments fa-lg"></i>
                </a>
                <a id="create_assignment_btn"
                   title="Distribute Google Drive Files"
                   class="btn btn-primary btn-circle btn-outline"
                   href="#create_assignment_modal"
                   data-toggle="modal"
                   v-on:click="this.getSchoologyAssignments()">
                    <i class="fa fa-files-o fa-lg"></i>
                    </span>
                </a>
                <button id="refresh_btn"
                        title="Refresh Student Folders"
                        type="button"
                        class="btn btn-primary btn-circle btn-outline"
                        :disabled="updating_section"
                        v-on:click.prevent="onUpdateEnrollments();">
                    <i :class="{ 'fa-spin': updating_section }" class="fa fa-refresh fa-lg"></i>
                </button>
            </form>
        </div>
    </div>
</nav>