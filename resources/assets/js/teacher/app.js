//window.jQuery = require('jquery');
var Bootstrap = require('../../vendor/bootstrap-sass/assets/javascripts/bootstrap');
var Pusher = require('pusher-js');
var Vue = require('vue');
var moment = require('moment');
Vue.use(require('vue-resource'));

Vue.http.headers.common['X-CSRF-TOKEN'] = document.querySelector('#token').getAttribute('value');
Vue.config.debug = true;

var vm = new Vue({
    el: '#app_teacher',

    data: {
        templates_folder_id: DriveCompanion.section.templates_folder_id,
        number: 30,
        show_files: 5,
        max_results: 15,
        users: [],
        checked_names: [],
        checked_groups: [],
        grading_groups: [],
        copied_files: [],
        fetching_template_files: false,
        fetching_enrollments: false,
        updating_section: false,
        creating_assignments: false,
        show_asssignments_create_form: false,
        selected_view: 'student-folders',
        selected_assignment: null,
        copy_progress: {
            number: 0,
            total: 0,
            percentage: "0%"
        },
        update_progress: {
            number: 0,
            total: 0,
            percentage: "0%"
        },
        assignment: {
            'template': '',
            'title': '',
            'type': 1,
        },
        errors: [],
    },
    computed: {
        schoology_title: function() {
            for (var a in this.schoology_assignments){
                if (this.schoology_assignments[a].id == this.assignment.schoology_assignment){
                    return this.schoology_assignments[a].title;
                }
            }
            return null;
        }
    },
    ready: function(){
        var pusher = new Pusher(DriveCompanion.PUSHER_KEY, {encrypted: true});
        var channel = pusher.subscribe(DriveCompanion.SESSION_ID);

        channel.bind('App\\Events\\UserUpdated', this.updateUser);
        channel.bind('App\\Events\\FileCopied', this.fileCopied);
        channel.bind('App\\Events\\ReportError', this.reportError);

        this.fetchTemplateFiles();
        this.onUpdateEnrollments();
        this.getAssignments();
        this.getSchoologyAssignments();
        this.getGradingGroups();

        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        });
    },

    methods:{
        fileCopied: function(data){
            this.copied_files.push(data);
            var copy_progress = {
                number: this.copied_files.length,
                total: data.count,
                percentage: Math.round((this.copied_files.length/data.count)*100)+'%'
            }
            this.$set('copy_progress', copy_progress);
            if (this.copied_files.length == data.count){
                this.$set('creating_assignments', false);
                this.onUpdateEnrollments();
            }
        },
        updateUser: function(data){
            this.users.push(data);
            var update_progress = {
                number: this.users.length,
                total: data.count,
                percentage: Math.round((this.users.length/data.count)*100)+'%'
            }
            this.$set('update_progress',update_progress);
            if (this.users.length == data.count){
                this.$set('updating_section', false);
            }
        },
        reportError: function(data){
            switch (data.type){
                case 'user':
                    this.users.push('error');
                    var update_progress = {
                        number: this.users.length,
                        total: data.count,
                        percentage: Math.round((this.users.length/data.count)*100)+'%'
                    }
                    this.$set('update_progress',update_progress);
                    if (this.users.length == data.count){
                        this.$set('updating_section', false);
                    }
                    break;
                case 'file':
                    this.copied_files.push('error');
                    var copy_progress = {
                        number: this.copied_files.length,
                        total: data.count,
                        percentage: Math.round((this.copied_files.length/data.count)*100)+'%'
                    }
                    this.$set('copy_progress', copy_progress);
                    if (this.copied_files.length == data.count){
                        this.$set('creating_assignments', false);
                    }
                    break;
            }
            this.errors.push(data.message);
        },
        fetchTemplateFiles: function(){
            var request={
                'email': DriveCompanion.user.email,
                'templates_folder_id': DriveCompanion.section.templates_folder_id,
                'user_folder_id': DriveCompanion.user.folder_id,
                'max_results': 100
            }
            this.$set('fetching_template_files', true);
            this.$http.post(
                '/api/assignments/gettemplatefiles',
                request,
                function(data, status, request){
                    this.$set('template_files', data);
                    this.$set('fetching_template_files', false);
                }
            )
        },
        getGradingGroups: function() {
            this.$http.get(
                '/api/sections/'+DriveCompanion.section.id+'/grading-groups',
                function(data) {
                    this.$set('grading_groups', data)
                }
            )
        },
        onUpdateEnrollments: function(e){
            if (typeof e !== 'undefined'){
                e.preventDefault();
            }
            this.$set('users',[]);
            this.$set('errors', []);
            this.$set('update_progress', {number:0, total:0, percentage:'0%'})
            var update={
                'uid' : DriveCompanion.user.id,
                'teacher_email': DriveCompanion.user.email,
                'school_id': DriveCompanion.user.school_id,
                'section_name': DriveCompanion.section.name,
                'students_folder_id': DriveCompanion.section.students_folder_id,
                'max_results': this.max_results,
                'pusher_channel': DriveCompanion.SESSION_ID
            };
            this.$set('updating_section',true);
            $('#view_options_modal').modal('hide');
            this.$http.put(
                '/api/sections/'+DriveCompanion.section.id,
                update,
                function(users, status, request){
                    //this.$set('updating_section', false);
                }
            ).error(function(users, status, request){
                    this.$set('updating_section', false);
                }
            )
        },

        onAssignments: function(){
            this.show_asssignments_create_form=!this.show_asssignments_create_form;
        },

        getAssignments: function(){
            this.$http.get('/api/sections/'+DriveCompanion.section.id+'/assignments',
                function(data){
                    this.$set('assignments', data)
                }
            )
        },

        getAssignmentFiles: function(id){
            this.$set('selected_file', null);
            this.$http.get('/api/assignments/'+id+'/files',
                function(data){
                    this.$set('assignment_files', data);
                    $('[data-toggle="tooltip"]').tooltip();
                }
            )
        },

        getSchoologyAssignments: function()
        {
            this.$http.get('/api/sections/'+DriveCompanion.section.id+'/schoology-assignments',
                function(data)
                {
                    this.$set('schoology_assignments', data);
                }
            )
        },

        openAssignmentFile: function(file){
            this.$set('selected_file', file);
        },

        onCreateAssignments: function (e) {
            e.preventDefault();
            $('#create_assignment_modal').modal('hide');
            this.$set('copied_files',[]);
            this.$set('errors', []);
            this.$set('creating_assignments', true);
            if (this.assignment.type == 1){
                // Make a copy for each Student.
                this.$set('copy_progress', {number:0, total:0, percentage:'5%'});
            } else if (this.assignment.type == 2) {
                // Make a copy for each Grading Group.
                this.$set('copy_progress', {number:0, total:0, percentage:'5%'});
            } else {
                // Let all students edit the same file.
                this.$set('copy_progress', {number:1, total:1, percentage:'100%'})
            }
            if (this.assignment.schoology_assignment == "None") {
                this.$set('assignment.schoology_assignment', null);
            }
            var request = {
                'section_id': DriveCompanion.section.id,
                'teacher_email': DriveCompanion.user.email,
                'assignments_folder_id': DriveCompanion.section.assignments_folder_id,
                'template_id': this.assignment.template,
                'assignment_title': this.assignment.title,
                'assignment_type': this.assignment.type,
                'pusher_channel': DriveCompanion.SESSION_ID,
                'schoology_assignment_id': this.assignment.schoology_assignment,
                'checked_names': this.checked_names,
                'checked_groups': this.checked_groups,
            };
            this.$http.post(
                '/api/assignments',
                request,
                function(data, status, request){
                    if (this.assignment.type == 3) {
                        // For a shared assignment set 'creating_assignment' false so the bar disappears.
                        this.$set('creating_assignments', false);
                        this.onUpdateEnrollments();
                    };
                    this.getAssignments();
                }).error(function(data, status, request) {
                    this.$set('creating_assignments', false);
                }
            );
        },
    }
})

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
Vue.filter('shorten', function(value, limit){
    if (typeof limit == 'undefined'){
        limit = 15;
    } else {
        limit = parseInt(limit);
    }
    if (value.length-2 > limit){
        return value.slice(0,limit)+"...";
    } else {
        return value;
    }
});
