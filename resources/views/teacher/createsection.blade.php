@extends('app')

@section('content')
    <div class="container">
        <div class="page-header">
            <p class="pull-right"><strong>Credits:</strong> {{$school->credits}}</p>
            <h1><i class="icon icon-logo fa-lg"></i>&nbsp;Drive Companion</h1>
        </div>
        <p class="lead">
            To set up this class, a new set of folders will be created in your <a href="https://drive.google.com/open?id={{$user->folder_id}}" target="_blank">Drive Companion folder</a> in Google Drive.
        </p>
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-4">
                        <script charset="ISO-8859-1" src="https://fast.wistia.com/assets/external/E-v1.js" async></script><div class="wistia_responsive_padding" style="padding:56.25% 0 0 0;position:relative;"><div class="wistia_responsive_wrapper" style="height:100%;left:0;position:absolute;top:0;width:100%;"><span class="wistia_embed wistia_async_131toaizai popover=true popoverAnimateThumbnail=true videoFoam=true" style="display:inline-block;height:100%;width:100%">&nbsp;</span></div></div>
                        <div class="text-center text-muted">
                            New to Drive Companion?
                        </div>
                    </div>
                    <div class="col-sm-8">
                        <p class="lead">
                            Please enter an appropriate name for this class' folder below and click continue to create the class.
                            Once the class is created, a folder will be created for each member of the course section.
                            Please ensure that any other teachers added to this course section are set to Admin to prevent a student folder being created for them.
                            <small>Each course section created in Drive Companion uses 1 credit.</small>
                        </p>
                    </div>
                </div>
                <hr>
                <form action="{{url("/api/sections",[],True)}}" method="post" role="form" id="create_section_form" class="form-horizontal">
                    @if($school->code)
                        <div class="form-group">
                            <label for="code" class="col-sm-2 control-label">School Code</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" name="code" id="code" required>
                            </div>
                            <div class="col-sm-7">
                                <p class="text-muted"><small>Please contact your organization's Schoology Administrator for your School's Code.</small></p>
                            </div>
                        </div>
                    @endif
                    <div class="form-group">
                        <label for="sectionTitle" class="col-sm-2 control-label">Folder Name</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" name="section_title" id="section_title" required value="{{$section->course_title}}: {{$section->section_title}} ({{ date("Y") }})">
                        </div>
                        <button id="submit_form" type="submit" class="btn btn-primary col-sm-2">Continue</button>
                        <div class="col-sm-3">
                            <p class="text-muted"><small>By clicking Continue you agree to our <a href="{{url('/privacy', null, True)}}" target="_blank">Privacy Policy</a> & <a href="{{url('/terms', null, True)}}" target="_blank">Terms of Use</a></small></p>
                        </div>
                    </div>
                    <input type="hidden" name="section_id" id="section_id" value="{{$section->id}}">
                    <input type="hidden" name="folder_id" id="folder_id" value="{{$user->folder_id}}">
                    <input type="hidden" name="school_id" id="school_id" value="{{$user->school_id}}">
                    <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}">
                </form>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(function(){
            $("#submit_form").click(function(){
                $("#submit_form").attr("disabled", true);
                $("#create_section_form").submit();
            })
        })
    </script>
@stop
