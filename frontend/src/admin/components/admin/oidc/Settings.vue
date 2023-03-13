
<template>

<div>

<ul class="nav nav-tabs">
  <li class="nav-item">
    <router-link :to="{name: 'oidc.settings.general'}" class="nav-link" :exact="true" active-class="active">
      General
    </router-link>
  </li>
  
  <li class="nav-item">
    <router-link :to="{name: 'oidc.settings.scopes'}" class="nav-link" :exact="true" active-class="active">
      Scopes
    </router-link>
  </li>

  <li class="nav-item">
    <router-link :to="{name: 'oidc.settings.keys'}" class="nav-link" :exact="true" active-class="active">
      Keys
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
  
}
</script>
