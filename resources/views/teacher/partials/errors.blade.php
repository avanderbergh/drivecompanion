<div v-show="errors.length">
    <ul class="list-group">
        <li class="list-group-item list-group-item-danger" v-for="error in errors">
            @{{ error }}
        </li>
    </ul>
</div>