<template>

<button class="btn btn-secondary btn-lg btn-block mt-1" type="button" @click.prevent="init" style="background-color: #dd4b39; border-color: rgba(0,0,0,0.2);">
    <i class="fab fa-google"></i> <span>{{ $t('login.google') }}</span>
</button>

</template>

<script>

import Vue from 'vue'
import base from './Base';
import fontawesome from '@fortawesome/fontawesome'

import faGoogle from '@fortawesome/fontawesome-free-brands/faGoogle'

fontawesome.library.add(faGoogle)

const URL_MODULE_NAME = "NAME_OF_THE_MODULE";

export default Vue.extend({

    mixins: [base],
    
    methods: {
        init() {
            
            

        }
    }
});
</script>
