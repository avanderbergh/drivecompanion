var Clipboard = require('../../../node_modules/clipboard/dist/clipboard.min.js')
var Bootstrap = require('../vendor/bootstrap-sass/assets/javascripts/bootstrap');
var Vue = require('vue');
var moment = require('moment');
Vue.use(require('vue-resource'));

Vue.http.headers.common['X-CSRF-TOKEN'] = document.querySelector('#token').getAttribute('value');

new Clipboard('.btn-copy');
var configSchool = new Vue({
    el: '#config_school',
    data: {
        buyCreditsButtonPartial:{
            show: true,
            disabled: false
        },
        enterBillingDetailsForm:{
            show: false,
            disabled: true,
        },
        billingDetailsPartial:{
            show: false,
            disabled: true,
        },
        buyCreditsForm:{
            show: false,
            disabled: true,
        },
        testGoogleAuthForm:{
            disabled: false,
            school_id: DriveCompanion.school_id,
        },
        sections:{},
        credits:{
            available: DriveCompanion.credits,
            price: DriveCompanion.price,
            add: 50,
            purchasing: false,
        },
        school: {
            google_api_configured: DriveCompanion.google_api_configured,
            domain: DriveCompanion.domain,
            xeroContact: null,
            billing_details: {},
            code: DriveCompanion.code,
        }
    },
    ready: function(){
        this.getXeroContact();
        this.getSections();
    },
    methods: {
        testGoogleAuth: function(){
            this.$set('testGoogleAuthForm.disabled', true);
            this.$http.post('/config/testgoogleauth',this.testGoogleAuthForm,function(data){
                this.$set('testGoogleAuthForm.disabled', false)
                this.$set('testGoogleAuthForm.result',data)
                if(data == 'success'){
                    this.$set('school.google_api_configured',true);
                }
            })
        },
        getCurrentSections: function(){
            this.$set('sections.fetching', true);
            this.$http.get('/config/current-sections', null, function(data){
                this.$set('sections.total', data);
                this.$set('sections.fetching', false);
            });
        },
        getSections: function(){
            this.$http.get('/config/schools/'+DriveCompanion.school_id+'/sections', null, function(data){
                this.$set('sections', data);
            });
        },
        checkXeroContact: function(){
            if (this.school.xeroContact){
                this.$set('billingDetailsPartial.show', true);
                this.$set('billingDetailsPartial.disabled', false);
            } else {
                this.$set('enterBillingDetailsForm.show', true);
                this.$set('enterBillingDetailsForm.disabled', false);
            }
        },
        cancelBuyCredits: function () {
            this.$set('buyCreditsForm.show', false);
            this.$set('buyCreditsForm.disabled', true);
        },
        enterBillingDetails: function(){
            this.$set('billingDetailsPartial.show', false);
            this.$set('billingDetailsPartial.disabled', true);
            this.$set('enterBillingDetailsForm.show', true);
            this.$set('enterBillingDetailsForm.disabled', false);
        },
        cancelEnterBillingDetails: function(){
            this.$set('enterBillingDetailsForm.show', false);
            this.$set('enterBillingDetailsForm.disabled', true);
        },
        confirmBillingDetails: function(){
            this.$set('billingDetailsPartial.show', false);
            this.$set('billingDetailsPartial.disabled', true);
            this.$set('buyCreditsForm.show', true);
            this.$set('buyCreditsForm.disabled', false);
        },
        buyCredits: function(){
            this.$set('buyCreditsForm.disabled', true);
            var request = {
                credits: this.credits.add,
            };
            this.$http.post('/config/schools/'+DriveCompanion.school_id+'/credits', request, function(data){
                this.$set('buyCreditsForm.show', false);
                this.$set('credits.available', data.credits);
            })
        },
        getXeroContact: function () {
            this.$set('sectionCreditsPartial.disabled', true);
            this.$set('buyCreditsButtonPartial.disabled', true);
            this.$http.get('/config/schools/'+DriveCompanion.school_id+'/xero', null, function(data) {
                if (data) {
                    this.$set('school.xeroContact', data);
                     this.school.billing_details = {
                         'client_id': data.ContactID,
                         'first_name': data.FirstName,
                         'last_name': data.LastName,
                         'organization': data.Name,
                         'email': data.EmailAddress,
                         'address1': data.Addresses[1].AddressLine1,
                         'address2': data.Addresses[1].AddressLine2,
                         'city': data.Addresses[1].City,
                         'state': data.Addresses[1].Region,
                         'country': data.Addresses[1].Country,
                         'postal_code': data.Addresses[1].PostalCode,
                         'phone': {
                             'country_code': data.Phones[1].PhoneCountryCode,
                             'area_code': data.Phones[1].PhoneAreaCode,
                             'number': data.Phones[1].PhoneNumber,
                         }
                     };
                } else {
                    this.school.billing_details = {
                        'first_name': DriveCompanion.user.name_first,
                        'last_name': DriveCompanion.user.name_last,
                        'organization': DriveCompanion.school.title,
                        'email': DriveCompanion.user.primary_email,
                        'address1': DriveCompanion.school.address1,
                        'address2': DriveCompanion.school.address2,
                        'city': DriveCompanion.school.city,
                        'state': DriveCompanion.school.state,
                        'country': DriveCompanion.school.country,
                        'postal_code': DriveCompanion.school.postal_code,
                    };
                }
                this.$set('sectionCreditsPartial.disabled', false);
                this.$set('buyCreditsButtonPartial.disabled', false);
            })
        },
        setXeroContact: function(){
            this.$set('enterBillingDetailsForm.disabled', true);
            this.$http.post('/config/schools/'+DriveCompanion.school_id+'/xero', this.school.billing_details, function(data){
                this.$set('enterBillingDetailsForm.disabled', true);
                this.$set('enterBillingDetailsForm.show', false);
                this.getXeroContact();
                this.$set('billingDetailsPartial.disabled', false);
                this.$set('billingDetailsPartial.show', true);
            });
        },
        setCode: function(){
            this.$http.post('/config/schools/'+DriveCompanion.school_id+'/code',null, function(data){
                this.$set('school.code', data);
            });
        },
        deleteCode: function(){
            this.$http.delete('/config/schools/'+DriveCompanion.school_id+'/code',null, function(data){
                this.$set('school.code', data);
            });
        },
    },

});

Vue.filter('moment', function() {
    var args = Array.prototype.slice.call(arguments),
        value = args.shift(),
        date = moment(value);

    if (!date.isValid()) return '';

    function parse() {
        var args = Array.prototype.slice.call(arguments).map(
                function(str) { return str.replace(/^("|')|("|')$/g, ''); }
            ),
            method = args.shift();

        switch (method) {
            case 'add':
                // Mutates the original moment by adding time.
                // http://momentjs.com/docs/#/manipulating/add/

                var addends = args.shift()
                    .split(',')
                    .map(Function.prototype.call, String.prototype.trim);
                var obj = {};
                for (var n = 0; n < addends.length; n++) {
                    var addend = addends[n].split(' ');
                    obj[addend[1]] = addend[0];
                }
                date = date.add(obj);
                break;

            case 'subtract':

                // Mutates the original moment by subtracting time.
                // http://momentjs.com/docs/#/manipulating/subtract/

                var subtrahends = args.shift()
                    .split(',')
                    .map(Function.prototype.call, String.prototype.trim);
                var obj = {};
                for (var n = 0; n < subtrahends.length; n++) {
                    var subtrahend = subtrahends[n].split(' ');
                    obj[subtrahend[1]] = subtrahend[0];
                }
                date = date.subtract(obj);
                break;

            case 'from':

                // Display a moment in relative time, either from now or from a specified date.
                // http://momentjs.com/docs/#/displaying/fromnow/

                var from = 'now';
                if (args[0] == 'now') args.shift();

                if (moment(args[0]).isValid()) {
                    // If valid, assume it is a date we want the output computed against.
                    from = moment(args.shift());
                }

                var removeSuffix = false;
                if (args[0] == 'true') {
                    args.shift();
                    var removeSuffix = true;
                }

                if (from != 'now') {
                    date = date.from(from, removeSuffix);
                    break;
                }

                date = date.fromNow(removeSuffix);
                break;

            case 'calendar':

                // Formats a date with different strings depending on how close to a certain date (today by default) the date is.
                // http://momentjs.com/docs/#/displaying/calendar-time/

                var referenceTime = moment();

                if (moment(args[0]).isValid()) {
                    // If valid, assume it is a date we want the output computed against.
                    referenceTime = moment(args.shift());
                }

                date = date.calendar(referenceTime);
                break;

            default:
                // Format
                // Formats a date by taking a string of tokens and replacing them with their corresponding values.
                // http://momentjs.com/docs/#/displaying/format/

                var format = method;
                date = date.format(format);
        }

        if (args.length) parse.apply(parse, args);
    }

    parse.apply(parse, args);


    return date;
});

Vue.filter('bulkprice', function(value) {
    if (value >= 5 && value <=1000){
        return 10 + (-0.001 * value + 3) * value
    } else {
        return "Please enter a number between 5 and 1000";
    }
});

Vue.filter('bulkpriceper', function(value) {
    if (value >= 5 && value <=1000){
        return (10 + (-0.001 * value + 3) * value) / value
    } else {
        return null
    }
});
