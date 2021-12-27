
<style lang="scss" scoped>

.score {
 width: 0%;
 height: 3px;
 background-color: transparent;

  &.score-0 {
    width: 10%;
    background-color: red;
  }

 &.score-1 {
   width: 25%;
   background-color: orange;
 }

 &.score-2 {
   width: 50%;
   background-color: rgb(191, 255, 0);
 }

 &.score-3 {
   background-color: green;
   width: 75%;
 }

 &.score-4 {
   background-color: rgb(46, 234, 46);
   width: 100%;
 }
}

img.cropped{
    width: 100px;
}

.headerimage{
    width: 100%;
    text-align: left;
}

.imageselect, .headerimage{
    display: flex;
    justify-content: left;
    align-items: left;
    width: 100%;
}

.imageupload {
    width: 100%;
    position: relative;

    .crop{
        position: absolute;
        right: 0px;
        bottom: 0px;
    }
}

.row-upload{

    input[type="file"] {
    display: none;
    }

    label {
    align-items: center;
    background-color: rgba(0,0,0,0.02);
    cursor: pointer;
    display: flex;
    height: 100px;
    justify-content: center;
    outline: 3px dashed #ccc;
    outline-offset: 5px;
    position: relative;
    width: 100px;

    * {
        pointer-events: none;
    }

    .fa {
        color: rgba(0,0,0,0.2);
        font-size: 5em;
    }

    &.dragging {
        outline-color: limegreen;
    }

    &.loaded {
        .fa {
        color: rgba(white, 0.5);
        }
    }

    &.loading {
        .fa {
        display: none;
        }

    }

    img {
        left: 50%;
        opacity: 0;
        max-height: 100%;
        max-width: 100%;
        position: absolute;
        top: 50%;
        transition: all 300ms ease-in;
        transform: translate(-50%,-50%);
        z-index: -1;

        &.loaded {
        opacity: 1;
        }
    }
    }

}

</style>

<template>

<div>

  <b-modal hide-footer size="lg" ref="pickerModal" id="pickerModal" title="Select an image">
      <Picker v-on:picked="picked" :picker="true" />
    </b-modal>

  <b-modal @ok="onSubmitNewHOTP" ref="modalNewHOTP" id="modalNewHOTP" title="Connect new authenticator" ok-title="I've connected the phone">

    <p>Scan the QR code using an Android or iOS device with a mobile app installed that supports HOTP. For example
      <a href="https://support.google.com/accounts/answer/1066447?co=GENIE.Platform%3DAndroid">Google Authenticator</a>
      or
      <a href="https://freeotp.github.io/">FreeOTP</a>.</p>
    <div class="text-center">
      <img :src="qrcode" />
    </div>

  </b-modal>

  <h4 class="c-grey-900 mt-1 mb-3">Edit User</h4>
  <div class="row">
    <div class="col-md-12">
      <div v-if="user" class="bgc-white bd bdrs-3 p-3 mt-2">

        <h4 class="c-grey-900 mt-2">{{ user['urn:ietf:params:scim:schemas:core:2.0:User'].userName }}</h4>

        <form class="needs-validation" novalidate :class="{'was-validated': wasValidated}" v-on:submit.prevent="onSubmit">

          <!-- TODO: Add Image Input, create data url -->

          <!-- <div class="form-row mb-3">

            <div class="col-md-3">
              Picture
            </div>

            <div class="col">

              <div class="imageupload" v-show="showupload">

                <vue-croppie ref="imageupload" :enableResize="false" :showZoomer="true" :boundary="{ width: '100%', height: 100 }"
                  :mouseWheelZoom="false" :viewport="{ width: 100, height: 100 }" @update="update" :enableOrientation="true">
                </vue-croppie>

                <button type="button" class="crop" @click="crop">
                  <span>crop</span>
                </button>

              </div>

              <div class="headerimage" v-if="!showupload">
                <label class="" ondragover="return false">
                  <img v-show="profileImage.value" v-bind:src="profileImage.value ? profileImage.value : null" class="loaded"
                    width="100">
                  <input v-show="profileImage.value == null" @change="readFile" type="file" accept="image/*" />
                </label>
              </div>

              <button v-if="profileImage.value" class="btn btn-danger" type="button" @click="profileImage.value = null">Delete
                Image</button>



            </div>

          </div> -->

            <div class="form-group form-row">
          <label class="col-3" for="logo_uri">Picture</label>

          <div class="col">

            <div class="input-group">
              <input
                type="url"
                placeholder="Picture"
                class="form-control"
                v-model="profileImage.value"
              />
              <div class="input-group-append">
                <button
                  class="btn btn-outline-secondary"
                  type="button"
                  @click="showPicker(m => profileImage.value=m.url)"
                >
                  <i class="ti-upload"></i>
                </button>
              </div>
            </div>

            <small id="pkce_help" class="form-text text-muted">URL that references a picture for the user.</small>

          </div>
        </div>


          <b-form-group horizontal :label-cols="3" description="Username." label="User name" label-for="user.username">
            <b-form-input :class="{'is-invalid': errors['urn:ietf:params:scim:schemas:core:2.0:User:userName'] ? true : false}"
              id="user.username" v-model.trim="user['urn:ietf:params:scim:schemas:core:2.0:User'].userName"></b-form-input>
            <div v-if="errors['urn:ietf:params:scim:schemas:core:2.0:User:userName']" class="invalid-feedback">
              This is a required field and must be minimal 3 characters long.
            </div>
          </b-form-group>

          <b-form-group horizontal :label-cols="3" description="Display Name." label="Display Name" label-for="user.displayName">
            <b-form-input id="user.displayName" v-model.trim="user['urn:ietf:params:scim:schemas:core:2.0:User'].displayName"></b-form-input>
          </b-form-group>

          <b-form-group v-for="(email, index) in user['urn:ietf:params:scim:schemas:core:2.0:User'].emails" v-bind:key="index"
            horizontal :label-cols="3" description="E-mail." label="E-mail" label-for="user.email">
            <b-form-input :class="{'is-invalid': errors['urn:ietf:params:scim:schemas:core:2.0:User:emails.' + index + '.value'] ? true : false }"
              id="user.email" v-model="email.value"></b-form-input>

            <div v-if="errors['urn:ietf:params:scim:schemas:core:2.0:User:emails.' + index + '.value']" class="invalid-feedback">
              This is a required field and must be a valid mail address.
            </div>

          </b-form-group>

          <b-form-group v-for="(phoneNumber, index) in user['urn:ietf:params:scim:schemas:core:2.0:User'].phoneNumbers" v-bind:key="index"
            horizontal :label-cols="3" description="Your phone number" label="Phone Number" label-for="user.phoneNumber">
            <b-form-input :class="{'is-invalid': errors['urn:ietf:params:scim:schemas:core:2.0:User:phoneNumbers.' + index + '.value'] ? true : false }"
              id="user.phoneNumber" v-model="phoneNumber.value"></b-form-input>

            <div v-if="errors['urn:ietf:params:scim:schemas:core:2.0:User:phoneNumbers.' + index + '.value']" class="invalid-feedback">
              This is must be a valid phone number.
            </div>

          </b-form-group>

          <b-form-group v-for="(address, index) in user['urn:ietf:params:scim:schemas:core:2.0:User'].addresses" v-bind:key="index"
            horizontal :label-cols="3" description="Your address" label="Address" label-for="user.address">
            <b-form-input :class="{'is-invalid': errors['urn:ietf:params:scim:schemas:core:2.0:User:phoneNumbers.' + index + '.formatted'] ? true : false }"
              id="user.address" v-model="address.formatted"></b-form-input>

            <div v-if="errors['urn:ietf:params:scim:schemas:core:2.0:User:addresses.' + index + '.formatted']" class="invalid-feedback">
              This is must be a valid address.
            </div>

          </b-form-group>

          <b-form-group horizontal :label-cols="3" description="Extra Identifier 1." label="Extra Identifier" label-for="user.extraIdentifier1">
            <b-form-input id="user.extraIdentifier1" v-model.trim="user['arietimmerman:ice'].extraIdentifier1"></b-form-input>
          </b-form-group>

          <b-form-group horizontal :label-cols="3" description="User editable metadata" label="User Metadata" label-for="user.metadataUser">
            <b-form-textarea id="user.metadataUser" :state="!wasValidated  ? null : errors['arietimmerman:ice:metadataUser'] == null"
              v-model.trim="user['arietimmerman:ice'].metadataUser" placeholder="" :rows="3"></b-form-textarea>

            <div v-if="errors['arietimmerman:ice:metadataUser']" class="invalid-feedback">This field can only hold
              valid json.</div>
          </b-form-group>

          <b-form-group horizontal :label-cols="3" breakpoint="md" label="Active">

            <b-form-checkbox id="active" v-model="user['urn:ietf:params:scim:schemas:core:2.0:User'].active" :value="true"
              :unchecked-value="false">
              {{ user['urn:ietf:params:scim:schemas:core:2.0:User'].active ? 'Active' : 'Inactive' }}
            </b-form-checkbox>

          </b-form-group>

          <div class="form-row">
            <div class="col-md-3">
              Preferred Language
            </div>
            <div class="col mb-3">

              <template v-if="availableLanguages && availableLanguages.length > 0">
                <multiselect id="preferredLanguage" v-model="user['urn:ietf:params:scim:schemas:core:2.0:User'].preferredLanguage"
                  :show-labels="false" :options="availableLanguages" :searchable="false" :close-on-select="true"
                  placeholder="Pick a value">

                </multiselect>
              </template>
              <a href="#" @click="$router.push('/internationalization')">Add some languages</a>
            </div>

          </div>


          <div class="form-row">
            <div class="col-md-3">
              Roles
            </div>
            <div class="col">
              <multiselect id="roles" v-model="user['urn:ietf:params:scim:schemas:core:2.0:User'].roles" track-by="value"
                :customLabel="( option,label) => ( option.tenant + ' - ' + option.display)" :options="roles || []"
                :searchable="false" :close-on-select="true" :show-labels="true" :multiple="true" placeholder="Pick a value">

                <template slot="option" slot-scope="props">
                  {{ props.option.display }}
                </template>

                <!-- <template slot="tag" slot-scope="props">
                  {{ props.option.display }}
                </template> -->

              </multiselect>
            </div>

          </div>

          <div class="form-row">
            <div class="col-md-3">

            </div>
            <div class="col">
              <button type="submit" class="btn btn-primary mt-3">Save Changes</button>
            </div>

          </div>

        </form>

      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div v-if="user" class="bgc-white bd bdrs-3 p-3 mt-2">
        <h4 class="c-grey-900 mt-2">Multi-factor authentication</h4>


        <div class="form-row mb-3">

          <div class="col-md-3">
            Authenticator-app
          </div>

          <div class="col">

            <template v-if="user['arietimmerman:ice'].otpSecretPresent">
              <p>You have a connected phone.</p>

              <button class="btn btn-primary" @click="showModal">Change Phone</button>
              <button class="btn btn-danger ml-2" @click="disconnectPhone">Disconnect Phone</button>
            </template>
            <template v-else>
              <button class="btn btn-primary" @click="showModal">Connect Phone</button>
            </template>

          </div>

        </div>

      </div>

    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div v-if="user" class="bgc-white bd bdrs-3 p-3 mt-2">
        <h4 class="c-grey-900 mt-2">Links</h4>

        <template v-if="user['arietimmerman:ice'].links && user['arietimmerman:ice'].links.length">
          <button v-for="(link,index) in user['arietimmerman:ice'].links" :key="index" class="btn btn-dark mr-1"
            @click.prevent="removeLink(link)">
            {{ link.subject_type }}
          </button>
        </template>
        <p v-else>No accounts connected</p>

      </div>

    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div v-if="user" class="bgc-white bd bdrs-3 p-3 mt-2">
        <h4 class="c-grey-900 mt-2">Group memberships</h4>


        <div class="form-row" v-if="groupsSelected && groupsSelected.length > 0">
          <div class="col-md-3">
            Memberships
          </div>
          <div class="col">

            <ul class="list-group">
              <li v-for="(group,index) in groupsSelected.concat().sort((a,b) => a.display.localeCompare(b.display))"
                :key="index" class="list-group-item d-flex justify-content-between align-items-center">
                {{ group.display }}
                <button class="btn btn-danger" type="button" @click="removeMembership(group)">remove</button>
              </li>
            </ul>

          </div>

        </div>

        <form class="needs-validation" novalidate v-on:submit.prevent="onSubmitGroups">
          <div class="form-row mt-3">
            <div class="col-md-3">
              Add membership
            </div>
            <div class="col">

              <select v-if="groups" v-model="groupNew" class="form-control" id="group">
                <option :value="null">...</option>
                <option v-for="(group, index) in groups.filter( e => !this.groupsSelected.find(f => f.value == e.id))"
                  :value="group.id" :key="index">{{ group['urn:ietf:params:scim:schemas:core:2.0:Group'].name }}<template
                    v-if="group['urn:ietf:params:scim:schemas:core:2.0:Group'].displayName"> ({{
                    group['urn:ietf:params:scim:schemas:core:2.0:Group'].displayName }})</template></option>
              </select>

            </div>

          </div>

          <div class="form-row mt-3">
            <div class="col-md-3">

            </div>
            <div class="col">

              <button class="btn btn-primary">Add membership</button>

            </div>

          </div>


        </form>

      </div>

    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div v-if="user" class="bgc-white bd bdrs-3 p-3 mt-2">
        <h4 class="c-grey-900 mt-2">Set a new password</h4>

        <form class="needs-validation" novalidate :class="{'was-validated': wasValidatedPassword}" v-on:submit.prevent="onSubmitPassword">

          <b-form-group horizontal :label-cols="3" description="Ensure you're using a secure password." label="Password"
            label-for="password">
            <b-form-input required type="password" id="password" v-model.trim="password"></b-form-input>
            <div class="score" :class="['score-' + passwordScore]"></div>

          </b-form-group>

          <div class="form-row">
            <div class="col-md-3">

            </div>
            <div class="col">
              <button type="submit" class="btn btn-primary mt-3">Set Password</button>
            </div>

          </div>

        </form>


      </div>

    </div>
  </div>

  <div class="card border-danger mb-3 mt-3">
    <div class="card-header">Danger Zone</div>
    <div class="card-body text-danger">
      <p class="card-text">Clicking the button below will delete this user. This cannot be undone.</p>
      <button type="button" class="btn btn-danger" @click="deleteUser(user)">Delete</button>
    </div>
  </div>
</div>

</template>

<script>
import Vue from 'vue';
import Picker from "../userinterface/Picker";

export default {
  
  components: {
    Picker
  },

  data() {
    return {
      
      availableLanguages: [],

      showupload: false,
      cropped: null,

      errors: {},

      wasValidated: false,
      wasValidatedPassword: false,
      loading: false,

      user: null,

      password: null,
      passwordScore: null,

      profileImage: null,

      qrcode: null,
      otpSecret: null,

      groupsSelected: [
        
      ],
      groupNew: null,
      
      roles: [

      ],

      groups: []

    }
  },

  watch: {
    password: function(val){

      import('zxcvbn').then(m => {

        this.passwordScore = m.default(val).score;
      });

    }
  },

  mounted() {

    // availableLanguages
    // this.availableLanguages


    this.$http.get(this.$oidcUrl('.well-known/openid-configuration'), {
        public: true
      }).then(response => {
        
        this.availableLanguages = response.data.ui_locales_supported;

      }, response => {
        // error callback
      });

    this.$http.get(Vue.murl('api/scim/v2/Users/' + this.$route.params.user_id)).then(response => {

      // somehow, the value-attribtue in response.data of groups is empty. though it is in the response.bodyText
      this.setUser(response.data);

    }, response => {
      // error callback
    });


    //TODO: Ensure you get all roles, always. Yet, limit attributes returned
    this.$http.get(this.$murl('api/scim/v2/Roles?count=100')).then(response => {

      for (var v of response.data.Resources) {
        this.roles.push({
          value: v.value,
          display: (v.tenant + ' - ' + v.display)
        });
      }

    });

    this.$http.get(this.$murl('api/scim/v2/Groups')).then(response => {

      this.groups = response.data.Resources;

    });

  },

  methods: {

    picked(m) {
      this.callback(m);
      this.$refs.pickerModal.hide();
    },

    showPicker(callback) {
      this.callback = callback;
      this.$refs.pickerModal.show();
    },

    removeLink(link) {

      this.user['arietimmerman:ice'].links.splice(this.user['arietimmerman:ice'].links.indexOf(link), 1);

      this.$http.patch(this.$murl('api/scim/v2/Users/' + this.$route.params.user_id), JSON.stringify({
        "schemas": ["urn:ietf:params:scim:api:messages:2.0:PatchOp"],
        "Operations": [{
          "op": "replace",
          "path": `arietimmerman:ice:links`,
          "value": this.user['arietimmerman:ice'].links
        }]
      }), {
        headers: {
          'content-type': 'application/scim+json'
        }
      }).then(response => {
        this.$noty({
          text: 'We have succesfully saved this user.'
        });
        this.errors = {};
      }, response => {
        this.$noty({
          text: 'There were some errors during saving.'
        });
        console.error(response.data.errors);
        this.errors = response.data.errors;
      });

    },

    setUser(user) {

      this.user = user;

      if (!this.user['arietimmerman:ice']) {
        this.user['arietimmerman:ice'] = {};
      }

      if (!this.user['urn:ietf:params:scim:schemas:core:2.0:User'].photos) {
        this.user['urn:ietf:params:scim:schemas:core:2.0:User'].photos = [];
      }

      if (!this.user['urn:ietf:params:scim:schemas:core:2.0:User'].phoneNumbers) {
        this.user['urn:ietf:params:scim:schemas:core:2.0:User'].phoneNumbers = [
          {
            value: null,
            type: 'other'
          }
        ];
      }

      if (!this.user['urn:ietf:params:scim:schemas:core:2.0:User'].addresses) {
        this.user['urn:ietf:params:scim:schemas:core:2.0:User'].addresses = [
          {
            formatted: null,
            type: 'other'
          }
        ];
      }

      for (var photo of this.user['urn:ietf:params:scim:schemas:core:2.0:User'].photos) {
        if (photo.type == 'thumbnail') {
          this.profileImage = photo;
          break;
        }
      }

      if (this.profileImage == null) {
        this.profileImage = {
          value: null,
          type: 'thumbnail'
        };
        this.user['urn:ietf:params:scim:schemas:core:2.0:User'].photos.push(this.profileImage);
      }

      

      this.groupsSelected = user['urn:ietf:params:scim:schemas:core:2.0:User'].groups || [];

    },

    showModal() {

      this.$http.get(this.$murl('api/hotp_secret_generator')).then(response => {

        var identifier = null;

        for (let email in this.user['urn:ietf:params:scim:schemas:core:2.0:User'].emails || []) {
          if (email.primary) {
            identifier = email.value;
            break;
          }
        }

        if (identifier == null && this.user['urn:ietf:params:scim:schemas:core:2.0:User'].emails) {
          identifier = this.user['urn:ietf:params:scim:schemas:core:2.0:User'].emails[0].value;
        }

        if (identifier == null) {
          identifier = this.user['urn:ietf:params:scim:schemas:core:2.0:User'].userName || this.user['urn:ietf:params:scim:schemas:core:2.0:User'].id;
        }

        this.otpSecret = response.data.secret;


        import( /* webpackChunkName: "qrious" */ 'qrious').then(module => {

          const QRious = module.default;

          var qr = new QRious({
            value: 'otpauth://totp/' + identifier + '?secret=' + this.otpSecret,
            size: 300
          });

          this.qrcode = qr.toDataURL();

          this.$refs.modalNewHOTP.show();

        });


      })


    },

    disconnectPhone() {

      this.$refs.modalNewHOTP.hide();

      this.$http.patch(this.$murl('api/scim/v2/Users/' + this.$route.params.user_id), JSON.stringify({
        "schemas": ["urn:ietf:params:scim:api:messages:2.0:PatchOp"],
        "Operations": [{
          "op": "replace",
          "path": "arietimmerman:ice:otpSecret",
          "value": ""
        }]
      }), {
        headers: {
          'content-type': 'application/scim+json'
        }
      }).then(response => {
        this.$noty({
          text: 'We have succesfully saved this user.'
        });

        this.setUser(response.data);
      }, error => {
        this.$noty({
          text: 'There were some errors during saving.'
        });
      });


    },

    onSubmitNewHOTP(event) {

      this.$refs.modalNewHOTP.hide();

      this.$http.patch(this.$murl('api/scim/v2/Users/' + this.$route.params.user_id), JSON.stringify({
        "schemas": ["urn:ietf:params:scim:api:messages:2.0:PatchOp"],
        "Operations": [{
          "op": "replace",
          "path": "arietimmerman:ice:otpSecret",
          "value": this.otpSecret
        }]
      }), {
        headers: {
          'content-type': 'application/scim+json'
        }
      }).then(response => {
        this.$noty({
          text: 'We have succesfully saved this user.'
        });

        this.otpSecret = null;

        this.setUser(response.data);

      }, error => {
        this.$noty({
          text: 'There were some errors during saving.'
        });
      });

    },

    deleteUser(user) {

      this.$http.delete(this.$murl('api/scim/v2/Users/' + user.id)).then(response => {

        this.$noty({
          text: 'We have succesfully deleted this user.'
        });

        this.$router.replace({
          name: 'users.list'
        });

      }, response => {
        // error callback
      });

    },

    onSubmitGroups(event){

      this.$http.patch(this.$murl('api/scim/v2/Groups/' + this.groupNew), JSON.stringify({
        "schemas": ["urn:ietf:params:scim:api:messages:2.0:PatchOp"],
        "Operations": [{
          "op": "add",
          "path": "urn:ietf:params:scim:schemas:core:2.0:Group:members",
          "value": [ {
            "value": this.$route.params.user_id
          }]
        }]
      }), {
        headers: {
          'content-type': 'application/scim+json'
        }
      }).then(response => {
        this.$noty({
          text: 'We have succesfully saved your groups.'
        });

        this.groupsSelected.push({
          display: response.data['urn:ietf:params:scim:schemas:core:2.0:Group'].name,
          value: response.data.id
        });

      }, error => {
        this.$noty({
          text: 'There were some errors during saving.'
        });
      });



    },

    removeMembership(group){

      this.$http.patch(this.$murl('api/scim/v2/Groups/' + group.value), JSON.stringify({
        "schemas": ["urn:ietf:params:scim:api:messages:2.0:PatchOp"],
        "Operations": [{
          "op": "remove",
          "path": "urn:ietf:params:scim:schemas:core:2.0:Group:members",
          "value": [ {
            "value": this.$route.params.user_id
          }]
        }]
      }), {
        headers: {
          'content-type': 'application/scim+json'
        }
      }).then(response => {
        this.$noty({
          text: 'We have succesfully removed you from this group.'
        });

        this.groupsSelected.splice(this.groupsSelected.indexOf(group), 1);
        
      }, error => {
        this.$noty({
          text: 'There were some errors during saving.'
        });
      });



    },

    onSubmitPassword(event) {

      this.$http.patch(this.$murl('api/scim/v2/Users/' + this.$route.params.user_id), JSON.stringify({
        "schemas": ["urn:ietf:params:scim:api:messages:2.0:PatchOp"],
        "Operations": [{
          "op": "replace",
          "path": "urn:ietf:params:scim:schemas:core:2.0:User:password",
          "value": this.password
        }]
      }), {
        headers: {
          'content-type': 'application/scim+json'
        }
      }).then(response => {
        this.$noty({
          text: 'We have succesfully updated the password.'
        });

        this.password = null;
      }, error => {
        this.$noty({
          text: 'There were some errors during saving.'
        });
      });

      event.preventDefault();

    },

    onSubmit(event) {

      this.$http.put(this.$murl('api/scim/v2/Users/' + this.$route.params.user_id), JSON.stringify(this.user), {
        headers: {
          'content-type': 'application/scim+json'
        }
      }).then(response => {
        this.wasValidated = false;
        this.$noty({
          text: 'We have succesfully saved this user.'
        });
        this.errors = {};
      }, response => {
        this.wasValidated = true;
        this.$noty({
          text: 'There were some errors during saving.'
        });
        this.errors = response.data.errors;
      });

      event.preventDefault();

    },

    readFile: function (event) {

      var input = event.target;

      if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = (event) => {

          this.$refs.imageupload.bind({
            url: event.target.result
          });

          this.showupload = true;

        };

        reader.readAsDataURL(input.files[0]);
      } else {
        alert('Sorry - you\'re browser doesn\'t support the FileReader API');
      }

    },

    update: function () {

    },

    crop() {

      let options = {
        type: 'base64',
        format: 'png',
        circle: false,
        size: {
          width: 100,
          height: 100
        }
      }

      this.$refs.imageupload.result(options, (output) => {

        this.profileImage.value = output;
        this.showupload = false;

      });
    }

  }

}

</script>
