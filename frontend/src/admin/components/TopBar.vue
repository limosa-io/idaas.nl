<template>
  <div class="header navbar">
    <div class="header-container">
      <ul class="nav-left">
        <li>
          <a id="sidebar-toggle" class="sidebar-toggle" @click.stop="$emit('toggle-sidebar')">
            <i class="ti-menu"></i>
          </a>
        </li>
        <li class="search-box" :class="{'active':searchActive}">
          <a
            class="search-toggle no-pdd-right"
            href="#"
            @click.prevent="searchActive = !searchActive"
          >
            <i class="search-icon ti-search pdd-right-10"></i>
            <i class="search-icon-close ti-close pdd-right-10"></i>
          </a>
        </li>
        <li class="search-input" :class="{'active':searchActive}">
          <input
            autocomplete="off"
            :spellcheck="false"
            class="form-control"
            ref="search"
            id="search"
            type="search"
            placeholder="Search..."
            @keyup.enter="followMatch"
            @keyup.up="keyup"
            @keyup.down="keydown"
            v-model="keyword"
          />

          <div class="search-results" v-if="matches && matches.length >0">
            <ul class="list-group">
                <li
                v-for="(m, index) in matches" :key="index"
                  @click="matchActive = index; followMatch()"
                  class="list-group-item"
                  :class="{active: matchActive == index}"
                >{{ m.breadcrumb }}</li>
            </ul>
          </div>
        </li>
      </ul>

      <nav aria-label="breadcrumb" class="breadcrumb">
        <ol class="breadcrumb">
          <li
            class="breadcrumb-item"
            v-for="(r, index) in breadcrumb"
            :class="{active: (index == breadcrumb.length-1)}"
            :key="index"
          >
            <router-link
              v-if="index < breadcrumb.length-1"
              :to="{ path: r.path || '/' }"
            >{{ (r.meta && r.meta.label) ? r.meta.label : r.name }}</router-link>
            <span v-else>{{ (r.meta && r.meta.label) ? r.meta.label : r.name }}</span>
          </li>
        </ol>
      </nav>

      <ul class="nav-right" v-if="userinfo">
        <li>
          <a target="_blank" href="https://www.idaas.nl/documentation/">Help</a>
        </li>

        <li class="dropdown" @click.stop.prevent="drop = !drop" :class="{'show':drop}">
          <a
            href="#"
            class="dropdown-toggle no-after peers fxw-nw ai-c lh-1"
            data-toggle="dropdown"
          >
            <div class="peer mR-10">
              <img
                v-if="userinfo.picture"
                class="w-2r bdrs-50p"
                :src="userinfo.picture"
                alt="no picture"
              />
              <div v-else class="w-2r bdrs-50p no-picture">
                <i class="ti-user mR-10" style="font-size:20px;"></i>
              </div>
            </div>

            <div class="peer">
              <span class="fsz-sm c-grey-900 ml-2">{{ userinfo.name }}</span>
            </div>
          </a>
          <ul class="dropdown-menu fsz-sm pt-0 pb-0">
            <li class="pt-3 pb-3">
              <a class="c-grey-700" @click.stop v-if="userinfo.profile" :href="userinfo.profile">
                <i class="ti-user mr-2"></i>
                <span>Profile</span>
              </a>

              <router-link
                v-else
                :to="{ name: 'users.edit', params: { user_id: userinfo.scim_id }}"
                class="c-grey-700"
                :exact="true"
                active-class="active"
              >
                <i class="ti-user mr-2"></i>
                <span>Profile</span>
              </router-link>
            </li>

            <li role="separator" class="divider"></li>
            <li class="pt-3 pb-3">
              <router-link
                :to="{ name: 'git'}"
                class="c-grey-700"
                :exact="true"
                active-class="active"
              >
                <i class=" ti-exchange-vertical mr-2"></i>
                <span>Sync</span>
              </router-link>
            </li>
            
            <li role="separator" class="divider"></li>
            <li class="pt-3 pb-3">
              <a class="c-grey-700" @click.stop href="https://my.idaas.nl">
                <i class="ti-light-bulb mr-2"></i>
                <span>My idaas.nl</span>
              </a>
            </li>
            <li role="separator" class="divider"></li>

            <li class="pt-3 pb-3">
              <router-link
                :to="{ name: 'initlogout' }"
                class="c-grey-700"
                :exact="true"
                active-class="active"
              >
                <i class="ti-power-off mr-2"></i>
                <span>Logout</span>
              </router-link>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</template>

<style lang="scss">
$color: #4285f4;

.breadcrumb {
  float: left;
  padding: 0px;
  margin: 0px;
  margin-left: 10px;
  height: 100%;
  display: flex;
  align-items: center;
  background-color: transparent;
}

.no-picture {
  border-color: $color;
  border-width: 2px;
  border-style: solid;
  text-align: center;
  height: 35px;
  width: 35px;
  padding-top: 5px;
  background-color: $color;
  box-shadow: 0 10px 40px 0 rgba(64, 63, 72, 0.07),
    0 2px 9px 0 rgba(60, 59, 69, 0.06);

  color: white !important;
}

.search-input {
  position: relative;

  .search-results {
    z-index: 999;

    position: absolute;
    top: 55px;
    left: -40px;

    background-color: white;
    width: 300px;
  }
}
</style>


<script setup>
import {ref, watch, nextTick, onMounted, getCurrentInstance, computed, defineProps} from 'vue';
import { useRoute, useRouter } from 'vue-router4';

const router = useRouter();
const drop = ref(false);
const searchActive = ref(false);
const keyword = ref(null);
const matches = ref([]);
const matchActive = ref(0);
const route = useRoute();
const vue = getCurrentInstance();

const props = defineProps({
  userinfo: {
    type: Object,
    required: true
  }
});

watch(searchActive, (val) => {
  //Wait for the element to become visible before focus
  nextTick(() => {
    vue.refs.search.focus();
  });
});

watch(keyword, (val) => {

  let m = [];

  for (let r of routeList()) {
    if (
      r.path &&
      r.path.length > 0 &&
      val.length >= 2 &&
      !r.path.match(":") &&
      r.breadcrumb &&
      r.breadcrumb.toLowerCase().match(val.toLowerCase())
    ) {
      m.push({
        path: r.path,
        breadcrumb: r.breadcrumb
      });

      if (m.length > 5) {
        break;
      }
    }
  }

  matches.value = m;
});

const breadcrumb = computed(() => {
  let result = [];

  for (let r of route.matched) {
    if (r.meta && r.meta.label) {
      result.push(r);
    }
  }

  return result;
});

function routeList() {
  var routes = [];

  for (let x of router.options.routes[0].children) {
    let parent = {
      path: x.path,
      breadcrumb: x.meta ? x.meta.label : x.name,
      name: x.name
    };

    routes.push(parent);

    if (x.children) {
      for (let y of x.children) {
        routes.push({
          path: y.path,
          breadcrumb:
            parent.breadcrumb + " > " + (y.meta ? y.meta.label : y.name),
          name: y.name
        });
      }
    }
  }

  return routes;
}

function followMatch() {
  searchActive.value = false;
  router.push(matches.value[matchActive.value].path);
}

function search() {}

function keyup() {
  matchActive.value = Math.max(0, matchActive.value - 1);
}

function keydown() {
  matchActive.value = Math.min(
    matches.value ? matches.value.length - 1 : 0,
    parseInt(matchActive.value) + 1
  );
}

</script>