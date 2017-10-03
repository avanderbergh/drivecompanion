<div class="panel panel-primary" v-if="enterBillingDetailsForm.show">
    <div class="panel-heading">
        <h3 class="panel-title">Please enter your billing details</h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal">
            <div class="form-group">
                <label for="organization" class="col-sm-2 control-label">Organization</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="organization" v-model="school.billing_details.organization">
                </div>
            </div>
            <div class="form-group">
                <label for="first_name" class="col-sm-2 control-label">First Name</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" id="first_name" v-model="school.billing_details.first_name">
                </div>
                <label for="last_name" class="col-sm-2 control-label">Last Name</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" id="last_name" v-model="school.billing_details.last_name">
                </div>
            </div>
            <div class="form-group">
                <label for="email" class="col-sm-2 control-label">Invoice Email</label>
                <div class="col-sm-4">
                    <input type="email" class="form-control" id="email" v-model="school.billing_details.email">
                </div>
                <label for="phoneCountryCode" class="col-sm-2 control-label">Phone</label>
                <div class="col-sm-1">
                    <input type="tel" class="form-control" id="phoneCountryCode" v-model="school.billing_details.phone.country_code">
                </div>
                <div class="col-sm-1">
                    <input type="tel" class="form-control" id="phoneCountryCode" v-model="school.billing_details.phone.area_code">
                </div>
                <div class="col-sm-2">
                    <input type="tel" class="form-control" id="phoneCountryCode" v-model="school.billing_details.phone.number">
                </div>
            </div>
            <div class="form-group">
                <label for="street1" class="col-sm-2 control-label">Address</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="address1" v-model="school.billing_details.address1">
                </div>
            </div>
            <div class="form-group">
                <label for="street2" class="col-sm-2 control-label"></label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="address2" v-model="school.billing_details.address2">
                </div>
            </div>
            <div class="form-group">
                <label for="city" class="col-sm-2 control-label">City</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" id="city" v-model="school.billing_details.city">
                </div>
                <label for="state" class="col-sm-2 control-label">State</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" id="state" v-model="school.billing_details.state">
                </div>
            </div>
            <div class="form-group">
                <label for="postal_code" class="col-sm-2 control-label">Postal Code</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" id="postal_code" v-model="school.billing_details.postal_code">
                </div>
                <label for="country" class="col-sm-2 control-label">Country</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" id="country" v-model="school.billing_details.country">
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-2 col-sm-offset-8">
                    <button type="button" class="btn btn-default btn-block" v-on:click="cancelEnterBillingDetails">Cancel</button>
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-primary btn-block" v-on:click="setXeroContact()" :disabled="enterBillingDetailsForm.disabled">Continue</button>
                </div>
            </div>
        </form>
    </div>
</div>