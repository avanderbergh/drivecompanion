<br>
<p v-if="!sections.length" class="alert alert-info">
    <strong>We've given you 5 free credits to get started!</strong><br>
    A credit is used when a new section is created in Drive Companion. Sections will appear here as teachers start setting them up in Drive Companion.
</p>
<div v-if="sections.length">
    <h3>Created Sections</h3>
    <div class="list-group">
        <a v-for="section in sections" href="@{{ school.domain + '/course/' + section.id }}" target="_blank" class="list-group-item">@{{ section.name }}</a>
    </div>
</div>