<div class="assignments" v-show="selected_view == 'assignments'">
    <iframe :src="'https://drive.google.com/open?id='+selected_file.file_id" frameborder="0" v-show="selected_file" width="100%" height="1000px"></iframe>
</div>