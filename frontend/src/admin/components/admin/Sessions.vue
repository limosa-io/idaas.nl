
<template>
<div>

  <ul class="nav nav-tabs">
    <li class="nav-item">
      <router-link :to="{name: 'sessions.sessions'}" class="nav-link" :exact="true" active-class="active">
        Sessions
      </router-link>
    </li>

    <li class="nav-item">
      <router-link :to="{name: 'sessions.tokens'}" class="nav-link" :exact="true" active-class="active">
        Tokens
      </router-link>
    </li>

    <li class="nav-item">
      <router-link :to="{name: 'sessions.subjects'}" class="nav-link" active-class="active">
        Subjects
      </router-link>
    </li>

  </ul>

  <div class="bgc-white bd bdrs-3 p-3">
    <router-view></router-view>
  </div>

</div>
</template>

<script>
export default {
  data() {
    return {};
  },

  mounted() {}
};
</script>
