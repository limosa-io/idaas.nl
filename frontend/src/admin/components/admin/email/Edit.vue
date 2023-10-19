
<template>
  <div>
    <div class="bgc-white bd bdrs-3 p-3 mt-2">
      <div class="row">
        <div class="col-md-6">
          <form
            ref="templateForm"
            :class="{ 'was-validated': wasValidated }"
            v-if="object"
            class="needs-validation"
            novalidate
            v-on:submit.prevent="onSubmit"
          >
            <!-- !object.is_parent &&  -->
            <div class="form-group">
              <label for="parent">Base template</label>
              <FormSelect
                id="parent"
                @change="reloadPreview"
                aria-describedby="parentHelp"
                v-if="parents"
                value-field="id"
                text-field="name"
                v-model="object.parent_id"
                :options="parentOptions"
                class=""
              />
              <small id="parentHelp" class="form-text text-muted"
                >Consider using a parent template that defines the header, main
                and footer section.</small
              >
            </div>

            <div class="form-group">
              <label for="name">Name</label>
              <input
                :class="{ 'is-invalid': errors['name'] ? true : false }"
                type="text"
                class="form-control"
                v-model="object.name"
                id="name"
                aria-describedby="nameHelp"
                placeholder="Template name"
              />
              <template v-if="errors['name']">
                <div
                  class="invalid-feedback"
                  v-for="(e, index) in errors['name']"
                  :key="index"
                >
                  {{ e }}
                </div>
              </template>
              <small id="nameHelp" class="form-text text-muted"
                >The name is not visible for end users. Use for your own
                reference.</small
              >
            </div>

            <div class="form-group">
              <label for="name">Subject</label>
              <input
                :class="{ 'is-invalid': errors['subject'] ? true : false }"
                type="text"
                class="form-control"
                v-model="object.subject"
                id="subject"
                aria-describedby="subjectHelp"
                placeholder="Subject"
              />
              <template v-if="errors['subject']">
                <div
                  class="invalid-feedback"
                  v-for="(e, index) in errors['subject']"
                  :key="index"
                >
                  {{ e }}
                </div>
              </template>
              <small id="subjectHelp" class="form-text text-muted"
                >The subject of the e-mail.</small
              >
            </div>

            <div class="form-group">
              <label for="codemirror">Body</label>

              <textarea
                id="codemirror"
                v-model="object.body"
              ></textarea>
            </div>

            <div class="alert alert-info" role="alert">
              Define sections<br /><br />
              <pre><span v-pre>{{$ content }}
&#x3C;p&#x3E;{{ article.body }}&#x3C;/p&#x3E;
{{/ content }}</span></pre>
              And use translations.<br /><br />
              <pre><span v-pre>{{#t}}login.remember{{/t}}</span></pre>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
          </form>
        </div>

        <div class="col-md-6">
          <iframe ref="preview" sandbox="" class="w-100 h-100"> </iframe>
        </div>
      </div>
    </div>

    <Danger
      v-if="object && !object.default"
      body="Clicking the button below will delete this application. This cannot be undone."
    >
      <button
        type="button"
        class="btn btn-danger"
        @click="deleteObject(object)"
      >
        Delete
      </button>
    </Danger>
  </div>
</template>


<script setup>
import _ from "lodash";
import { Buffer } from "buffer";
import Danger from "@/admin/components/general/Danger.vue";
import {shallowRef} from 'vue';

import { onMounted, watch, computed, ref, getCurrentInstance } from "vue";
import {maxios} from "@/admin/helpers.js";
import { notify } from "../../../helpers";
import {useRouter, useRoute} from 'vue-router4';

const route = useRoute();
const router = useRouter();
const parents = ref(null);

const preview = ref(null);


const errors = ref({});

const wasValidated = ref(false);
const loading = ref(false);

const object = ref(null);

const templateForm = ref(null);


const parentOptions = computed(() => {
  var result = parents.value;
  result.unshift({
    name: "--- no parent ---",
    id: null,
  });
  return parents.value ? result : [];
});

const vue = getCurrentInstance();


onMounted(() => {
  maxios
    .get(
      
        "api/mail_template/" + encodeURIComponent(route.params.object_id)
      
    )
    .then(
      (response) => {
        object.value = response.data;
        wasValidated.value = false;

        reloadPreviewNow();
      },
      (response) => {
        errors.value = response.data.errors;
        wasValidated.value = true;
      }
    );

    maxios.get("api/mail_template").then(
    (response) => {
      parents.value = response.data;
    },
    (response) => {
      // error callback
    }
  );
});

function reloadPreviewNow() {
  if (!templateForm.value || templateForm.value.checkValidity()) {
    maxios
      .post(
        
          "api/preview_mail_template/" + encodeURIComponent(object.value.id)
        ,
        object.value
      )
      .then(
        (response) => {

          if(preview.value)
          preview.value.contentWindow.location.replace(
            "data:text/html;base64," +
              Buffer.from(response.data.preview).toString("base64")
          );

          errors.value = {};
        },
        (response) => {
          errors.value = response.data.errors;
          wasValidated.value = true;

          notify({
            text: "We could not preview this.",
            type: "error",
          });
        }
      );
  } else {
    wasValidated.value = true;
    notify({
      text: "We could not preview this.",
      type: "error",
    });
  }
}

function reloadPreviewDebounced() {
  return _.debounce(function () {
    reloadPreviewNow();
  }, 1000);
}

function reloadPreview() {
  reloadPreviewDebounced();
}

function deleteObject(o) {
  maxios
    .delete(
      "api/mail_template/" + encodeURIComponent(object.value.id)
    )
    .then((response) => {
      notify({
        text: "Successfully deleted the client.",
      });

      router.replace({
        name: "emails.list",
      });
    });
}

function onSubmit(event) {
  if (event.target.checkValidity()) {
    maxios
      .put(
        "api/mail_template/" + encodeURIComponent(object.value.id),
        object.value
      )
      .then(
        (response) => {
          notify({
            text: "We have succesfully saved this.",
          });
          errors.value = {};
        },
        (response) => {
          errors.value = response.data.errors;
          wasValidated.value = true;

          notify({
            text: "We could not save this.",
            type: "error",
          });
        }
      );
  } else {
    wasValidated.value = true;
    notify({
      text: "We could not save this.",
      type: "error",
    });
  }

  event.preventDefault();
}
</script>

<style lang="scss" scoped>
iframe {
  border-style: none;
}
</style>
