<div v-if="!school.google_api_configured" class="panel" :class="{'panel-primary': !testGoogleAuthForm.result, 'panel-danger': testGoogleAuthForm.result=='fail'}">
	  <div class="panel-heading">
			<h3 class="panel-title">Drive Companion needs access to your Google Apps domain.</h3>
	  </div>
	  <div class="panel-body">
          <strong>Please follow these steps to grant the required access:</strong>
          <ol>
              <li>Open the <strong>Manage API client access</strong> of in your domain's Google Apps Admin Panel. <a class="btn btn-default btn-sm pull-right" href="https://admin.google.com/ManageOauthClients" target="_blank"><i class="fa fa-external-link"></i> Open</a></li>
              <li>In the <strong>Client name</strong> field enter: <br>
                  <div class="input-group">
                      <input id="client_name" type="text" disabled="disabled" class="form-control input-sm" value="929741971428-5b6e4q1ln3i11c5eci4mtdisqphh5m8a.apps.googleusercontent.com">
                      <div class="input-group-btn">
                          <button type="button" class="btn btn-default btn-sm btn-copy" data-clipboard-target="#client_name"><i class="fa fa-clipboard"></i> Copy</button>
                      </div>
                  </div>
                  </li>
              <li>In the <strong>One or More API Scopes</strong> field enter: <br>
                  <div class="input-group">
                      <input id="api_scopes" type="text" disabled="disabled" class="form-control input-sm" value="https://www.googleapis.com/auth/drive">
                      <div class="input-group-btn">
                          <button type="button" class="btn btn-default btn-sm btn-copy" data-clipboard-target="#api_scopes"><i class="fa fa-clipboard"></i> Copy</button>
                      </div>
                  </div>
              </li>
              <li>Click the <kbd>Authorize</kbd> button</li>
          </ol>
          <div class="alert alert-danger" v-show="testGoogleAuthForm.result=='fail'">
              <strong>Failed!</strong>
              Drive Companion could not connect to your Google Apps domain using the email address provided<br/>
              Please make sure that you correctly completed the steps above and that you are using a valid email address on your Google Apps domain!
          </div>
          <br>
          <p>When the above steps have been completed, please enter a valid email on your Google Apps domain in the field below and click <strong>Confirm</strong> to ensure that it worked.</p>
          <div class="input-group">
              <input type="email" class="form-control" placeholder="person@yourdomain.com" v-model="testGoogleAuthForm.email">
              <span class="input-group-btn">
                  <button class="btn"
                          :class="{'btn-primary': !testGoogleAuthForm.result, 'btn-danger': testGoogleAuthForm.result=='fail'}"
                          type="button"
                          v-on:click="testGoogleAuth()"
                          :disabled="testGoogleAuthForm.disabled || !testGoogleAuthForm.email">
                      Confirm
                  </button>
              </span>
          </div>
	  </div>
</div>