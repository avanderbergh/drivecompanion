<div v-show="updating_section">
    <div class="progress">
        <div class="progress-bar progress-bar-success progress-bar-striped active"
             role="progressbar"
             aria-valuenow="@{{ update_progress.number }}"
             aria-valuemin="0"
             aria-valuemax="@{{ update_progress.total }}"
             style="min-width: 15rem; width: @{{ update_progress.percentage }}">
            <span class="sr-only">Fetching Student @{{ update_progress.number+1 }}</span>
        </div>
    </div>
</div>

<div v-show="creating_assignments">
    <div class="progress">
        <div class="progress-bar progress-bar-info progress-bar-striped active"
             role="progressbar"
             aria-valuenow="@{{ copy_progress.number }}"
             aria-valuemin="@{{ 0 }}"
             aria-valuemax="@{{ copy_progress.total }}"
             style="width: @{{ copy_progress.percentage }};"
                >
            Copying file @{{ copy_progress.number+1 }}
            <span class="sr-only">Copying File @{{ copy_progress.number+1 }}</span>
        </div>
    </div>
</div>