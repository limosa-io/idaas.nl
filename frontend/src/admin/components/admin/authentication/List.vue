
<template>
  <MainTemplate title="Authentication">
    <template v-slot:header>
      <Modal
        @ok="onSubmitNewLink"
        ref="newLinkModal"
        id="newLinkModal"
        title="New authentication path"
      >
        <form class="needs-validation" v-on:submit="onSubmitNewLink">
          <div class="form-row mb-3">
            <div class="col-md-3">
              <label for="chain.list.from">From</label>
            </div>
            <div class="col">
              <FormSelect
                id="chain.list.from"
                v-model="newLink.from"
                :options="options"
                class="mb-3"
              />
            </div>
          </div>

          <div class="form-row mb-3">
            <div class="col-md-3">
              <label for="chain.list.to">To</label>
            </div>
            <div class="col">
              <FormSelect
                id="chain.list.to"
                v-model="newLink.to"
                :options="options"
                class="mb-3"
              />
            </div>
          </div>

          <div v-for="(e, index) in errors" class="alert alert-danger" role="alert" :key="index">
            {{ e[0] }}
          </div>
        </form>
      </Modal>

      <Button
        to="/authentication/add"
      >
        Add Module
      </Button>
    </template>

    <template v-slot:body>
      <h4 class="c-grey-900 mt-1 mb-3">Authentication</h4>

      <h4 class="c-grey-900 mt-2">Modules</h4>

      <p>
        A <code class="highlighter-rouge">authentication module</code> is a step
        through which the users authenticates himself.
      </p>

      <table class="table table-hover">
        <thead>
          <tr>
            <th scope="col">Name</th>
            <th scope="col">Authentication Levels</th>
            <th scope="col" style="width: 40px"></th>
          </tr>
        </thead>
        <tbody v-if="modules">
          <tr
            @click="edit(module)"
            v-for="(module, index) in modules"
            :class="{ 'table-active': module.system }"
            v-b-tooltip.hover
            :title="
              module.system
                ? 'The start module is at the start of every authentication process'
                : ''
            "
            :key="index"
          >
            <td class="pt-3">
              {{ module.name }}
              <span
                class="badge badge-pill badge-info"
                v-if="
                  userinfo && userinfo.acr && userinfo.acr.includes(module.id)
                "
                v-b-tooltip.hover
                title="You have used this module to authenticate for accessing this page."
                >Now in use</span
              >
              <span
                v-if="!module.chained"
                class="badge badge-pill badge-warning ml-2"
                v-b-tooltip.hover
                title="This module does not appear in the authentication tree. Consider deleting it."
                >Not in use</span
              >
              <small class="form-text text-muted">{{ module.type }}</small>
            </td>
            <td class="">
              <ul v-if="module.levels" class="list-group mt-0">
                <li
                  class="list-group-item"
                  v-for="(level, index) in module.levels"
                  :key="index"
                >
                  {{ level.level }}
                </li>
              </ul>
            </td>
            <td class="pt-4 text-right">
              <button
                @click="edit(module)"
                type="button"
                class="btn btn-dark btn-block"
                style="min-width: 80px"
              >
                Edit
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <hr class="mt-5 mb-5" />

      <button
        @click="createLink"
        type="button"
        class="btn btn-primary btn-md float-right"
      >
        Add link
      </button>

      <h4 class="c-grey-900 mt-2">Tree</h4>

      <p>
        The <code class="highlighter-rouge">authentication tree</code> is an
        evolutionized authentication chain. Every module can be followed by
        zero, one or more authentication modules.
      </p>
      <p>
        Depending on the user's security preferences or your applications
        requirements, the flow is chosen.
      </p>

      <table class="table table-hover">
        <thead>
          <tr>
            <th scope="col">From</th>
            <th scope="col">To</th>

            <th scope="col" class="text-right"></th>
          </tr>
        </thead>
        <tbody v-if="chain">
          <tr v-for="(c, index) in chain" :key="index">
            <td class="pt-4">
              {{ getModule(c.from).name }}
              <small class="form-text text-muted">{{
                getModule(c.from).type
              }}</small>
            </td>
            <td class="pt-4">
              {{ getModule(c.to).name }}
              <small class="form-text text-muted">{{
                getModule(c.to).type
              }}</small>
            </td>

            <td class="pt-4 text-right" style="max-width: 100px">
              <button
                type="button"
                class="btn btn-danger"
                @click="deleteLink(c)"
                v-if="
                  userinfo &&
                  userinfo.acr &&
                  (!userinfo.acr.includes(c.to) ||
                    !userinfo.acr.includes(c.from))
                "
                style="min-width: 80px"
              >
                Delete
              </button>
              <span v-else>This link is in use</span>
            </td>
          </tr>
        </tbody>
      </table>
    </template>
  </MainTemplate>
</template>


<script setup>
import MainTemplate from "@/admin/components/general/MainTemplate.vue";
import { ref, onMounted, defineProps, computed } from "vue";
import {maxios, laxios} from "@/admin/helpers.js";
import Modal from "@/admin/components/general/Modal.vue";
import { useRouter } from "vue-router4";

const router = useRouter();
const modules = ref(null);
const chain = ref(null);
const userinfo = ref({});
const errors = ref({});
const newLink = ref({
  from: null,
  to: null,
});

const newLinkModal = ref(null);

onMounted(() => {
  // Use this for creating modules
  maxios.get("authchain/v2/manage/modules").then(
    (response) => {
      modules.value = response.data;
    },
    (response) => {
      // error callback
    }
  );

  maxios.get("authchain/v2/manage/chain").then(
    (response) => {
      chain.value = response.data;
    },
    (response) => {
      // error callback
    }
  );

  laxios.get("oauth/userinfo").then(
    (response) => {
      userinfo.value = response.data;
    },
    (response) => {
      // error callback
    }
  );
});

const options = computed(() => {
  var result = [];

  if (modules.value)
    for (var m of modules.value) {
      result.push({ value: m.id, text: m.name });
    }

  return result;
});

function edit(module) {
  router.push({
    name: "authentication.edit",
    params: { module_id: module.id },
  });
}

function deleteLink(chain) {
  maxios.delete("authchain/v2/manage/chain/" + chain.id).then(
    (response) => {
      //this.chain.rem
      chain.splice(chain.indexOf(chain), 1);
    },
    (response) => {
      // error callback
    }
  );
}

function getModule(id) {
  return modules.value
    ? modules.value.find((e) => {
        return e.id == id;
      }) || {
        name: "Unknown",
      }
    : {};
}

function createLink() {
  newLinkModal.value.show();
}

function onSubmitNewLink(event) {
  maxios.post("authchain/v2/manage/chain", newLink.value).then(
    (response) => {
      chain.value.push(response.data);

      newLinkModal.value.hide();
    },
    (response) => {
      // error callback
      errors.value = response.data.errors;
    }
  );

  event.preventDefault();
}
</script>