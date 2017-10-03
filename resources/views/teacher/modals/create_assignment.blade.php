<!-- Create Assignment Modal -->
<div class="modal fade" id="create_assignment_modal">
	<div class="modal-dialog">
		<div class="modal-content">
            <form @submit="onCreateAssignments($event)" action="#" method="post" role="form" class="form-horizontal">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title"><i class="fa fa-file fa-lg fa-fw"></i>&nbsp;Distribute Files</h5>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="assignment_name" class="col-sm-3 control-label">Title</label>
                        <div class="col-sm-9">
                            <input type="text" v-model="assignment.title" name="assignment_name" id="assignment_name" class="form-control" required="required" placeholder="Enter a title for the copied files">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="template_select" class="col-sm-3 control-label">File to Copy/Share</label>
                        <div class="col-sm-5">
                            <select v-model="assignment.template"
                                    name="template_select"
                                    id="template_select"
                                    class="form-control"
                                    data-toggle="tooltip"
                                    data-placement="top"
                                    title="Documents copied into or created in the Templates folder can be selected here"
                                    required>
                                <option value="" selected disabled>Select a file you want to make copies of</option>
                                <option v-for="template_file in template_files" :value="template_file.value">@{{ template_file.text }}</option>
                            </select>
                        </div>
                        <button class="btn btn-default col-sm-1"
                                v-on:click="fetchTemplateFiles()"
                                data-toggle="tooltip"
                                data-placement="top"
                                title="Reload files from the templates folder"
                                :disabled="fetching_template_files">
                            <i class="fa fa-refresh" :class="{'fa-spin': fetching_template_files}"></i>
                        </button>
                        <a class="btn btn-default col-sm-3"
                           href="https://drive.google.com/drive/folders/@{{ templates_folder_id }}"
                           target="_blank"
                           data-toggle="tooltip"
                           data-placement="top"
                           title="Create template files in this folder">
                            <i class="fa fa-external-link"></i> Templates Folder
                        </a>
                    </div>
                    <div class="form-group">
                        <label for="type_select" class="col-sm-3 control-label">Type</label>
                        <div class="col-sm-9">
                            <select v-model="assignment.type" name="type_select" id="type_select" class="form-control">
                                <option value="1">Make a copy for each student</option>
                                <option value="2">Make a copy for each grading group</option>
                                <option value="3">Let all students edit the same file</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-9 col-sm-offset-3" v-show="assignment.type == 1">
                        <div class="well well-sm well-scroll">
                            <small>
                            <label class="checkbox-inline" v-for="user in users">
                                <input type="checkbox" id="@{{ user.id }}" value="@{{ user.enrollment_id }}" v-model="checked_names" checked> @{{ user.name }}
                            </label>
                            </small>
                        </div>
                    </div>
                    <div class="col-sm-9 col-sm-offset-3" v-show="assignment.type == 2">
                        <div class="well well-sm well-scroll">
                            <small>
                                <label class="checkbox-inline" v-for="grading_group in grading_groups">
                                    <input type="checkbox" id="@{{ grading_group.id }}" value="@{{ grading_group.id }}" v-model="checked_groups" checked> @{{ grading_group.title }}
                                </label>
                            </small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="schoology_assignment" class="col-sm-3 control-label">
                            Link Assignment
                        </label>
                        <div class="col-sm-9">
                            <select
                                    name="schoology_assignment"
                                    id="schoology_assignment"
                                    v-model="assignment.schoology_assignment"
                                    class="form-control"
                                    v-on:change="assignment.title=this.schoology_title"
                                    data-toggle="tooltip"
                                    data-placement="top"
                                    title="Select a Schoology Assignment to link to this file (optional). Students will be able to submit this to Schoology from Drive Companion."
                            >
                                <option selected>None</option>
                                <option
                                        v-for="schoology_assignment in schoology_assignments"
                                        :value="schoology_assignment.id"
                                >
                                    @{{ schoology_assignment.title }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" :disabled="creating_assignments || (assignment.type == 1 && checked_names.length < 1) || (assignment.type == 2 && checked_groups.length < 1)">Copy & Share</button>
                </div>
            </form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
