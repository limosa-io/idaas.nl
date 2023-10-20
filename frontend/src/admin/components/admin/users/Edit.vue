
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

img.cropped {
  width: 100px;
}

.headerimage {
  width: 100%;
  text-align: left;
}

.imageselect,
.headerimage {
  display: flex;
  justify-content: left;
  align-items: left;
  width: 100%;
}

.imageupload {
  width: 100%;
  position: relative;

  .crop {
    position: absolute;
    right: 0px;
    bottom: 0px;
  }
}

.row-upload {
  input[type="file"] {
    display: none;
  }

  label {
    align-items: center;
    background-color: rgba(0, 0, 0, 0.02);
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
      color: rgba(0, 0, 0, 0.2);
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
      transform: translate(-50%, -50%);
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
    <Modal
      hide-footer
      size="lg"
      ref="pickerModal"
      id="pickerModal"
      title="Select an image"
    >
      <Picker v-on:picked="picked" :picker="true" />
    </Modal>

    <Modal
      @ok="onSubmitNewHOTP"
      ref="modalNewHOTP"
      id="modalNewHOTP"
      title="Connect new authenticator"
      ok-title="I've connected the phone"
    >
      <p>
        Scan the QR code using an Android or iOS device with a mobile app
        installed that supports HOTP. For example
        <a
          href="https://support.google.com/accounts/answer/1066447?co=GENIE.Platform%3DAndroid"
          >Google Authenticator</a
        >
        or
        <a href="https://freeotp.github.io/">FreeOTP</a>.
      </p>
      <div class="text-center">
        <img :src="qrcode" />
      </div>
    </Modal>

    <h4 class="c-grey-900 mt-1 mb-3">Edit User</h4>
    <div class="row">
      <div class="col-md-12">
        <div v-if="user && user['urn:ietf:params:scim:schemas:core:2.0:User'].userName" class="bgc-white bd bdrs-3 p-3 mt-2">
          <h4 class="c-grey-900 mt-2">
            {{ user["urn:ietf:params:scim:schemas:core:2.0:User"].userName.length > 1 }}
          </h4>

          <form
            class="needs-validation"
            novalidate
            :class="{ 'was-validated': wasValidated }"
            v-on:submit.prevent="onSubmit"
          >
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
                      @click="showPicker((m) => (profileImage.value = m.url))"
                    >
                      <i class="ti-upload"></i>
                    </button>
                  </div>
                </div>

                <small id="pkce_help" class="form-text text-muted"
                  >URL that references a picture for the user.</small
                >
              </div>
            </div>

            <FormGroup
              description="Username."
              label="User name"
              id="user.username"
            >
              <FormInput
                :class="{
                  'is-invalid': errors[
                    'urn:ietf:params:scim:schemas:core:2.0:User:userName'
                  ]
                    ? true
                    : false,
                }"
                id="user.username"
                v-model.trim="
                  user['urn:ietf:params:scim:schemas:core:2.0:User'].userName
                "
              ></FormInput>
              <div
                v-if="
                  errors['urn:ietf:params:scim:schemas:core:2.0:User:userName']
                "
                class="invalid-feedback"
              >
                This is a required field and must be minimal 3 characters long.
              </div>
            </FormGroup>

            <FormGroup
              horizontal
              :label-cols="3"
              description="Display Name."
              label="Display Name"
              label-for="user.displayName"
            >
              <FormInput
                id="user.displayName"
                v-model.trim="
                  user['urn:ietf:params:scim:schemas:core:2.0:User'].displayName
                "
              ></FormInput>
            </FormGroup>

            <FormGroup
              v-for="(email, index) in user[
                'urn:ietf:params:scim:schemas:core:2.0:User'
              ].emails"
              v-bind:key="index"
              horizontal
              :label-cols="3"
              description="E-mail."
              label="E-mail"
              label-for="user.email"
            >
              <FormInput
                :class="{
                  'is-invalid': errors[
                    'urn:ietf:params:scim:schemas:core:2.0:User:emails.' +
                      index +
                      '.value'
                  ]
                    ? true
                    : false,
                }"
                id="user.email"
                v-model="email.value"
              ></FormInput>

              <div
                v-if="
                  errors[
                    'urn:ietf:params:scim:schemas:core:2.0:User:emails.' +
                      index +
                      '.value'
                  ]
                "
                class="invalid-feedback"
              >
                This is a required field and must be a valid mail address.
              </div>
            </FormGroup>

            <FormGroup
              v-for="(phoneNumber, index) in user[
                'urn:ietf:params:scim:schemas:core:2.0:User'
              ].phoneNumbers"
              v-bind:key="index"
              horizontal
              :label-cols="3"
              description="Your phone number"
              label="Phone Number"
              label-for="user.phoneNumber"
            >
              <FormInput
                :class="{
                  'is-invalid': errors[
                    'urn:ietf:params:scim:schemas:core:2.0:User:phoneNumbers.' +
                      index +
                      '.value'
                  ]
                    ? true
                    : false,
                }"
                id="user.phoneNumber"
                v-model="phoneNumber.value"
              ></FormInput>

              <div
                v-if="
                  errors[
                    'urn:ietf:params:scim:schemas:core:2.0:User:phoneNumbers.' +
                      index +
                      '.value'
                  ]
                "
                class="invalid-feedback"
              >
                This is must be a valid phone number.
              </div>
            </FormGroup>

            <FormGroup
              v-for="(address, index) in user[
                'urn:ietf:params:scim:schemas:core:2.0:User'
              ].addresses"
              v-bind:key="index"
              horizontal
              :label-cols="3"
              description="Your address"
              label="Address"
              label-for="user.address"
            >
              <FormInput
                :class="{
                  'is-invalid': errors[
                    'urn:ietf:params:scim:schemas:core:2.0:User:phoneNumbers.' +
                      index +
                      '.formatted'
                  ]
                    ? true
                    : false,
                }"
                id="user.address"
                v-model="address.formatted"
              ></FormInput>

              <div
                v-if="
                  errors[
                    'urn:ietf:params:scim:schemas:core:2.0:User:addresses.' +
                      index +
                      '.formatted'
                  ]
                "
                class="invalid-feedback"
              >
                This is must be a valid address.
              </div>
            </FormGroup>

            <FormGroup
              horizontal
              :label-cols="3"
              description="Extra Identifier 1."
              label="Extra Identifier"
              label-for="user.extraIdentifier1"
            >
              <FormInput
                id="user.extraIdentifier1"
                v-model.trim="user['arietimmerman:ice'].extraIdentifier1"
              ></FormInput>
            </FormGroup>

            <FormGroup
              horizontal
              :label-cols="3"
              description="User editable metadata"
              label="User Metadata"
              label-for="user.metadataUser"
            >
              <FormTextarea
                id="user.metadataUser"
                :state="
                  !wasValidated
                    ? null
                    : errors['arietimmerman:ice:metadataUser'] == null
                "
                v-model.trim="user['arietimmerman:ice'].metadataUser"
                placeholder=""
                :rows="3"
              ></FormTextarea>

              <div
                v-if="errors['arietimmerman:ice:metadataUser']"
                class="invalid-feedback"
              >
                This field can only hold valid json.
              </div>
            </FormGroup>

            <FormGroup
              horizontal
              :label-cols="3"
              breakpoint="md"
              label="Active"
              id="active"
            >
              <FormCheckbox
                id="active"
                v-model="
                  user['urn:ietf:params:scim:schemas:core:2.0:User'].active
                "
                :value="true"
              >
                {{
                  user["urn:ietf:params:scim:schemas:core:2.0:User"].active
                    ? "Active"
                    : "Inactive"
                }}
              </FormCheckbox>
            </FormGroup>

            <div class="form-row">
              <div class="col-md-3">Preferred Language</div>
              <div class="col mb-3">
                <template
                  v-if="availableLanguages && availableLanguages.length > 0"
                >
                  <multiselect
                    id="preferredLanguage"
                    v-model="
                      user['urn:ietf:params:scim:schemas:core:2.0:User']
                        .preferredLanguage
                    "
                    :show-labels="false"
                    :options="availableLanguages"
                    :searchable="false"
                    :close-on-select="true"
                    placeholder="Pick a value"
                  >
                  </multiselect>
                </template>
                <a href="#" @click="router.push('/internationalization')"
                  >Add some languages</a
                >
              </div>
            </div>

            <div class="form-row">
              <div class="col-md-3">Roles</div>
              <div class="col">
                <multiselect
                  id="roles"
                  v-model="
                    user['urn:ietf:params:scim:schemas:core:2.0:User'].roles
                  "
                  track-by="value"
                  :customLabel="
                    (option, label) => option.tenant + ' - ' + option.display
                  "
                  :options="roles || []"
                  :searchable="false"
                  :close-on-select="true"
                  :show-labels="true"
                  :multiple="true"
                  placeholder="Pick a value"
                >
                <!-- FIXME: this templat should contain something -->
                  <template slot="option" slot-scope="props">
                    test
                  </template>

                  <!-- <template slot="tag" slot-scope="props">
                  {{ props.option.display }}
                </template> -->
                </multiselect>
              </div>
            </div>

            <div class="form-row">
              <div class="col-md-3"></div>
              <div class="col">
                <button type="submit" class="btn btn-primary mt-3">
                  Save Changes
                </button>
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
            <div class="col-md-3">Authenticator-app</div>

            <div class="col">
              <template v-if="user['arietimmerman:ice'].otpSecretPresent">
                <p>You have a connected phone.</p>

                <button class="btn btn-primary" @click="showModal">
                  Change Phone
                </button>
                <button class="btn btn-danger ml-2" @click="disconnectPhone">
                  Disconnect Phone
                </button>
              </template>
              <template v-else>
                <button class="btn btn-primary" @click="showModal">
                  Connect Phone
                </button>
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

          <template
            v-if="
              user['arietimmerman:ice'].links &&
              user['arietimmerman:ice'].links.length
            "
          >
            <button
              v-for="(link, index) in user['arietimmerman:ice'].links"
              :key="index"
              class="btn btn-dark mr-1"
              @click.prevent="removeLink(link)"
            >
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

          <div
            class="form-row"
            v-if="groupsSelected && groupsSelected.length > 0"
          >
            <div class="col-md-3">Memberships</div>
            <div class="col">
              <ul class="list-group">
                <li
                  v-for="(group, index) in groupsSelected
                    .concat()
                    .sort((a, b) => a.display.localeCompare(b.display))"
                  :key="index"
                  class="list-group-item d-flex justify-content-between align-items-center"
                >
                  {{ group.display }}
                  <button
                    class="btn btn-danger"
                    type="button"
                    @click="removeMembership(group)"
                  >
                    remove
                  </button>
                </li>
              </ul>
            </div>
          </div>

          <form
            class="needs-validation"
            novalidate
            v-on:submit.prevent="onSubmitGroups"
          >
            <div class="form-row mt-3">
              <div class="col-md-3">Add membership</div>
              <div class="col">
                <select
                  v-if="groups"
                  v-model="groupNew"
                  class="form-control"
                  id="group"
                >
                  <option :value="null">...</option>
                  <option
                    v-for="(group, index) in groups.filter(
                      (e) => !groupsSelected.find((f) => f.value == e.id)
                    )"
                    :value="group.id"
                    :key="index"
                  >
                    {{
                      group["urn:ietf:params:scim:schemas:core:2.0:Group"].name
                    }}<template
                      v-if="
                        group['urn:ietf:params:scim:schemas:core:2.0:Group']
                          .displayName
                      "
                    >
                      ({{
                        group["urn:ietf:params:scim:schemas:core:2.0:Group"]
                          .displayName
                      }})</template
                    >
                  </option>
                </select>
              </div>
            </div>

            <div class="form-row mt-3">
              <div class="col-md-3"></div>
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

          <form
            class="needs-validation"
            novalidate
            :class="{ 'was-validated': wasValidatedPassword }"
            v-on:submit.prevent="onSubmitPassword"
          >
            <FormGroup
              horizontal
              :label-cols="3"
              description="Ensure you're using a secure password."
              label="Password"
              label-for="password"
            >
              <FormInput
                required
                type="password"
                id="password"
                v-model.trim="password"
              ></FormInput>
              <div class="score" :class="['score-' + passwordScore]"></div>
            </FormGroup>

            <div class="form-row">
              <div class="col-md-3"></div>
              <div class="col">
                <button type="submit" class="btn btn-primary mt-3">
                  Set Password
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <Danger
      body="Clicking the button below will delete this user. This cannot be undone."
    >
      <button type="button" class="btn btn-danger" @click="deleteUser(user)">
        Delete
      </button>
    </Danger>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from "vue";

import Picker from "../userinterface/Picker.vue";
import { maxios, laxios, notify } from "@/admin/helpers";
import { getCurrentInstance, defineProps } from "vue"
import {useRouter, useRoute} from 'vue-router4';

import Modal from '@/admin/components/general/Modal.vue'

const vue = getCurrentInstance();
const router = useRouter();
const route = useRoute();

const availableLanguages = ref([]);

const showupload = ref(false);
const cropped = ref(null);

const errors = ref({});

const wasValidated = ref(false);
const wasValidatedPassword = ref(false);
const loading = ref(false);

const user = ref(null);

const password = ref(null);
const passwordScore = ref(null);

const profileImage = ref(null);

const qrcode = ref(null);
const otpSecret = ref(null);

const groupsSelected = ref([]);

const groupNew = ref(null);

const roles = ref([]);

const groups = ref([]);
const callback = null;

const pickerModal = ref(null);
const modalNewHOTP = ref(null);
const imageupload = ref(null);

watch(password, (val) => {
  import("zxcvbn").then((m) => {
    passwordScore.value = m.default(val).score;
  });
});

onMounted(() => {
  laxios
    .get(".well-known/openid-configuration", {
      public: true,
    })
    .then(
      (response) => {
        availableLanguages.value = response.data.ui_locales_supported;
      },
      (response) => {
        // error callback
      }
    );

  maxios.get("api/scim/v2/Users/" + route.params.user_id).then(
    (response) => {
      // somehow, the value-attribtue in response.data of groups is empty. though it is in the response.bodyText
      setUser(response.data);
    },
    (response) => {
      // error callback
    }
  );

  //TODO: Ensure you get all roles, always. Yet, limit attributes returned
  maxios.get("api/scim/v2/Roles?count=250").then((response) => {
    for (var v of response.data.Resources) {
      roles.value.push({
        value: v.value,
        display: v.tenant + " - " + v.display,
      });
    }
  });

  maxios.get("api/scim/v2/Groups").then((response) => {
    groups.value = response.data.Resources;
  });
});

function picked(m) {
  callback(m);
  pickerModal.value.hide();
}

function showPicker(c) {
  callback = c;
  pickerModal.value.show();
}

function removeLink(link) {
  user.value["arietimmerman:ice"].links.splice(
    user.value["arietimmerman:ice"].links.indexOf(link),
    1
  );

  maxios
    .patch(
      "api/scim/v2/Users/" + route.params.user_id,
      JSON.stringify({
        schemas: ["urn:ietf:params:scim:api:messages:2.0:PatchOp"],
        Operations: [
          {
            op: "replace",
            path: `arietimmerman:ice:links`,
            value: user.value["arietimmerman:ice"].links,
          },
        ],
      }),
      {
        headers: {
          "content-type": "application/scim+json",
        },
      }
    )
    .then(
      (response) => {
        notify({
          text: "We have succesfully saved this user.",
        });
        errors.value = {};
      },
      (response) => {
        notify({
          text: "There were some errors during saving.",
        });
        console.error(response.data.errors);
        errors.value = response.data.errors;
      }
    );
}

function setUser(u) {
  user.value = u;

  if (!user.value["arietimmerman:ice"]) {
    user.value["arietimmerman:ice"] = {};
  }

  if (!user.value["urn:ietf:params:scim:schemas:core:2.0:User"].photos) {
    user.value["urn:ietf:params:scim:schemas:core:2.0:User"].photos = [];
  }

  if (!user.value["urn:ietf:params:scim:schemas:core:2.0:User"].phoneNumbers) {
    user.value["urn:ietf:params:scim:schemas:core:2.0:User"].phoneNumbers = [
      {
        value: null,
        type: "other",
      },
    ];
  }

  if (!user.value["urn:ietf:params:scim:schemas:core:2.0:User"].addresses) {
    user.value["urn:ietf:params:scim:schemas:core:2.0:User"].addresses = [
      {
        formatted: null,
        type: "other",
      },
    ];
  }

  for (var photo of user.value["urn:ietf:params:scim:schemas:core:2.0:User"]
    .photos) {
    if (photo.type == "thumbnail") {
      profileImage.value = photo;
      break;
    }
  }

  if (profileImage.value == null) {
    profileImage.value = {
      value: null,
      type: "thumbnail",
    };
    user.value["urn:ietf:params:scim:schemas:core:2.0:User"].photos.push(
      profileImage.value
    );
  }

  groupsSelected.value =
    user.value["urn:ietf:params:scim:schemas:core:2.0:User"].groups || [];
}

function showModal() {
  maxios.get("api/hotp_secret_generator").then((response) => {
    var identifier = null;

    for (let email in user.value["urn:ietf:params:scim:schemas:core:2.0:User"]
      .emails || []) {
      if (email.primary) {
        identifier = email.value;
        break;
      }
    }

    if (
      identifier == null &&
      user.value["urn:ietf:params:scim:schemas:core:2.0:User"].emails
    ) {
      identifier =
        user.value["urn:ietf:params:scim:schemas:core:2.0:User"].emails[0].value;
    }

    if (identifier == null) {
      identifier =
        user.value["urn:ietf:params:scim:schemas:core:2.0:User"].userName ||
        user.value["urn:ietf:params:scim:schemas:core:2.0:User"].id;
    }

    otpSecret.value = response.data.secret;

    import(/* webpackChunkName: "qrious" */ "qrious").then((module) => {
      const QRious = module.default;

      var qr = new QRious({
        value: "otpauth://totp/" + identifier + "?secret=" + otpSecret.value,
        size: 300,
      });

      qrcode.value = qr.toDataURL();

      modalNewHOTP.value.show();
    });
  });
}

function disconnectPhone() {
  modalNewHOTP.value.hide();

  maxios
    .patch(
      "api/scim/v2/Users/" + route.params.user_id,
      JSON.stringify({
        schemas: ["urn:ietf:params:scim:api:messages:2.0:PatchOp"],
        Operations: [
          {
            op: "replace",
            path: "arietimmerman:ice:otpSecret",
            value: "",
          },
        ],
      }),
      {
        headers: {
          "content-type": "application/scim+json",
        },
      }
    )
    .then(
      (response) => {
        notify({
          text: "We have succesfully saved this user.",
        });

        setUser(response.data);
      },
      (error) => {
        notify({
          text: "There were some errors during saving.",
        });
      }
    );
}

function onSubmitNewHOTP(event) {
  modalNewHOTP.value.hide();

  maxios
    .patch(
      "api/scim/v2/Users/" + route.params.user_id,
      JSON.stringify({
        schemas: ["urn:ietf:params:scim:api:messages:2.0:PatchOp"],
        Operations: [
          {
            op: "replace",
            path: "arietimmerman:ice:otpSecret",
            value: otpSecret.value,
          },
        ],
      }),
      {
        headers: {
          "content-type": "application/scim+json",
        },
      }
    )
    .then(
      (response) => {
        notify({
          text: "We have succesfully saved this user.",
        });

        otpSecret.value = null;

        setUser(response.data);
      },
      (error) => {
        notify({
          text: "There were some errors during saving.",
        });
      }
    );
}

function deleteUser(user) {
  maxios.delete("api/scim/v2/Users/" + user.id).then(
    (response) => {
      notify({
        text: "We have succesfully deleted this user.",
      });

      router.replace({
        name: "users.list",
      });
    },
    (response) => {
      // error callback
    }
  );
}

function onSubmitGroups(event) {
  maxios
    .patch(
      "api/scim/v2/Groups/" + groupNew.value,
      JSON.stringify({
        schemas: ["urn:ietf:params:scim:api:messages:2.0:PatchOp"],
        Operations: [
          {
            op: "add",
            path: "urn:ietf:params:scim:schemas:core:2.0:Group:members",
            value: [
              {
                value: route.params.user_id,
              },
            ],
          },
        ],
      }),
      {
        headers: {
          "content-type": "application/scim+json",
        },
      }
    )
    .then(
      (response) => {
        notify({
          text: "We have succesfully saved your groups.",
        });

        groupsSelected.value.push({
          display:
            response.data["urn:ietf:params:scim:schemas:core:2.0:Group"].name,
          value: response.data.id,
        });
      },
      (error) => {
        notify({
          text: "There were some errors during saving.",
        });
      }
    );
}

function removeMembership(group) {
  maxios
    .patch(
      "api/scim/v2/Groups/" + group.value,
      JSON.stringify({
        schemas: ["urn:ietf:params:scim:api:messages:2.0:PatchOp"],
        Operations: [
          {
            op: "remove",
            path: "urn:ietf:params:scim:schemas:core:2.0:Group:members",
            value: [
              {
                value: route.params.user_id,
              },
            ],
          },
        ],
      }),
      {
        headers: {
          "content-type": "application/scim+json",
        },
      }
    )
    .then(
      (response) => {
        notify({
          text: "We have succesfully removed you from this group.",
        });

        groupsSelected.value.splice(groupsSelected.value.indexOf(group), 1);
      },
      (error) => {
        notify({
          text: "There were some errors during saving.",
        });
      }
    );
}

function onSubmitPassword(event) {
  maxios
    .patch(
      "api/scim/v2/Users/" + route.params.user_id,
      JSON.stringify({
        schemas: ["urn:ietf:params:scim:api:messages:2.0:PatchOp"],
        Operations: [
          {
            op: "replace",
            path: "urn:ietf:params:scim:schemas:core:2.0:User:password",
            value: password.value,
          },
        ],
      }),
      {
        headers: {
          "content-type": "application/scim+json",
        },
      }
    )
    .then(
      (response) => {
        notify({
          text: "We have succesfully updated the password.",
        });

        password.value = null;
      },
      (error) => {
        notify({
          text: "There were some errors during saving.",
        });
      }
    );

  event.preventDefault();
}

function onSubmit(event) {
  maxios
    .put(
      "api/scim/v2/Users/" + route.params.user_id,
      JSON.stringify(user.value),
      {
        headers: {
          "content-type": "application/scim+json",
        },
      }
    )
    .then(
      (response) => {
        wasValidated.value = false;
        notify({
          text: "We have succesfully saved this user.",
        });
        errors.value = {};
      },
      (response) => {
        wasValidated.value = true;
        notify({
          text: "There were some errors during saving.",
        });
        errors.value = response.data.errors;
      }
    );

  event.preventDefault();
}

function readFile(event) {
  var input = event.target;

  if (input.files && input.files[0]) {
    var reader = new FileReader();

    reader.onload = (event) => {
      imageupload.value.bind({
        url: event.target.result,
      });

      showupload.value = true;
    };

    reader.readAsDataURL(input.files[0]);
  } else {
    alert("Sorry - you're browser doesn't support the FileReader API");
  }
}

function update() {}

function crop() {
  let options = {
    type: "base64",
    format: "png",
    circle: false,
    size: {
      width: 100,
      height: 100,
    },
  };

  imageupload.value.result(options, (output) => {
    profileImage.value.value = output;
    showupload.value = false;
  });
}

</script>
