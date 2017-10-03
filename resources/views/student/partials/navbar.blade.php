<div v-if="submit_assignment.status=='success'" class="alert alert-success alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    Your assignment was successfully submitted!
</div>
<div v-if="submit_assignment.status=='fail'" class="alert alert-danger alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    Assignment could not be submitted! Please check if the course instructor has published the assignment and that submissions have been enabled.
</div>
<nav class="navbar navbar-default navbar-static-top">
    <div class="container-fluid">
        <div clas="navbar-header">
            <a class="navbar-brand">&nbsp;<i class="icon icon-logo fa-lg"></i>&nbsp;Drive Companion</a>
        </div>
        <div class="collapse navbar-collapse">
            <form class="navbar-form navbar-right" @submit.prevent>
                <a href="https://drive.google.com/drive/folders/{{ $enrollment->folder_id }}" class="btn btn-primary btn-outline" target="_blank"><i class="fa fa-external-link"></i> Open Folder</a>
                <button type="button"
                        class="btn btn-primary btn-outline"
                        :disabled="updating_files"
                        v-on:click="updateFiles()">
                        <i class="fa fa-refresh" :class="{'fa-spin': updating_files}"></i>
                </button>
                <button type="button"
                        class="btn btn-primary btn-outline"
                        v-if="selected.assignment"
                        :disabled="submit_assignment.disabled"
                        v-on:click="submitAssignment(selected)">
                    <i class="fa fa-cloud-upload" v-if="!submit_assignment.disabled"></i>
                    <i class="fa fa-spinner fa-spin" v-if="submit_assignment.disabled"></i>
                    Submit Assignment
                </button>
            </form>
        </div>
    </div>
</nav>