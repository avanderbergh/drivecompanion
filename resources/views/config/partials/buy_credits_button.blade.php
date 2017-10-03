<form class="navbar-form navbar-right" v-if="buyCreditsButtonPartial.show && school.google_api_configured" @submit.prevent>
    <label>Credits <span class="badge"> @{{ credits.available }}</span></label>
    <button class="btn btn-primary btn-circle btn-outline" :disabled="buyCreditsButtonPartial.disabled" v-on:click="checkXeroContact()" >
        <i class="fa fa-shopping-cart"></i>
    </button>
</form>
