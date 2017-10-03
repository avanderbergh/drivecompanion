<div class="navbar-header">
    <p class="navbar-brand" style="margin-bottom: 0;" v-show="creating_assignments">
        <i class="fa fa-circle-o-notch fa-spin fa-lg"></i> Creating Assignment: @{{ assignment.title }}...
    </p>
    <p class="navbar-brand" style="margin-bottom: 0;" v-show="updating_section">
        <i class="fa fa-circle-o-notch fa-spin fa-lg"></i> Retrieving Student Folders...
    </p>
</div>