<div class="student-folders" v-show="selected_view == 'student-folders'">
    <div v-for="user in users | orderBy 'name_last' | filterBy search.student in 'name'"
         class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
        <div  class="panel panel-default" v-if="user.name">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <a href="https://drive.google.com/drive/folders/@{{ user.folder_id }}" target="_blank">
                        <img :src="user.picture" class="img-responsive img-rounded" style="display: inline; width: 1.2em; height: 1.2em;">
                        @{{ user.name }}
                    </a>
                </h3>
            </div>
            <div class="table-responsive" v-if="user.files.length">
                <table class="table table-condensed">
                    <thead>
                    <tr>
                        <td class="text-nowrap">Name</td>
                        <td class="text-nowrap">Last Modified</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="file in user.files | filterBy search.file in 'name' | limitBy show_files">
                        <td class="text-nowrap" :class="{'new-changes': file.newChanges}">
                            @{{ file.lastModifyingUser }}
                            <img :src="file.iconLink">
                            <a href="@{{ file.webViewLink }}" target="_blank" @click="file.newChanges=false">
                                @{{ file.name | shorten }}
                            </a>
                        </td>
                        <td class="text-nowrap">
                            <small>@{{ file.modifiedTime | moment "from" }}</small>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="panel-body">
                <div class="text-muted text-center">
                    <small>This folder is empty.</small>
                </div>
            </div>
        </div>
    </div>
</div>