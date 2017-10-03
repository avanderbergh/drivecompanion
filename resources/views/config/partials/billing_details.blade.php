<div class="panel panel-primary" v-if="billingDetailsPartial.show">
    <div class="panel-heading">
        <h3 class="panel-title">Please confirm your billing details</h3>
    </div>
    <div class="panel-body">
        <address>
            <strong>@{{ school.xeroContact.Name }}</strong><br>
            @{{ school.xeroContact.Addresses[1].AddressLine1 }}, @{{ school.xeroContact.Addresses[1].AddressLine2 }}<br>
            @{{ school.xeroContact.Addresses[1].City }}, @{{ school.xeroContact.Addresses[1].Region }} @{{ school.xeroContact.Addresses[1].PostalCode }}<br>
            @{{ school.xeroContact.Addresses[1].Country }}<br>
            <i class="fa fa-phone"></i> @{{ school.xeroContact.Phones[1].PhoneCountryCode }} (@{{ school.xeroContact.Phones[1].PhoneAreaCode }}) @{{ school.xeroContact.Phones[1].PhoneNumber }}<br>
        </address>
        <strong>Email Invoices To:</strong>
        <address>
            @{{ school.xeroContact.FirstName }} @{{ school.xeroContact.LastName }}<br>
            <a href="mailto:@{{ school.xeroContact.EmailAddress }}">@{{ school.xeroContact.EmailAddress }}</a>
        </address>
        <div class="form-horizontal">
            <div class="form-group">
                <div class="col-sm-2 col-sm-offset-8">
                    <button type="button" class="btn btn-default btn-block" v-on:click="enterBillingDetails()">Change</button>
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-primary btn-block" v-on:click.prevent="confirmBillingDetails()">Confirm</button>
                </div>
            </div>
        </div>
    </div>
</div>