<div class="well">
    <form class="form-inline" v-if="school.code" @submit.prevent>
        <button class="btn btn-primary btn-circle btn-outline" @click="deleteCode()"><i class="fa fa-lock"></i></button>
        Course admins need to enter this code to use a credit 
        <div class="input-group">
            <input class="form-control" disabled="disabled" value="@{{school.code}}">
            <div class="input-group-btn">
                <button type="button" class="btn btn-default" @click="setCode()"><i class="fa fa-refresh"></i></button>
            </div>
        </div>
    </form>
    <div v-else>
        <button class="btn btn-primary btn-circle btn-outline" @click="setCode()"><i class="fa fa-unlock"></i></button>
        Any course admin can use credits to create sections
    </div>
</div>
