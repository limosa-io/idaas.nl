import Vue, { createApp, configureCompat } from 'vue'
import App from './App.vue'
import router from './router'

// import { BModal } from 'bootstrap-vue'
// import { VBModal } from 'bootstrap-vue'
// import {VBTooltip, BPagination, BDropdown, BDropdownItem, BFormCheckboxGroup, BFormRadio, BFormCheckbox, BFormRadioGroup, BFormTextarea, BFormSelect, BBadge} from 'bootstrap-vue';
// import {BFormGroup, BFormInput} from './bootstrap-vue'
import state from './components/state.js'
import MenuButton from "@/admin/components/general/MenuButton.vue";
import MainTemplate from "@/admin/components/general/MainTemplate.vue";
import Multiselect from 'vue-multiselect';
import Croppie from './components/general/Croppie.vue';
import FormGroup from './components/general/FormGroup.vue';
import FormInput from './components/general/FormInput.vue';
import FormTextarea from './components/general/FormTextarea.vue';
import FormCheckbox from './components/general/FormCheckbox.vue';
import FormSelect from './components/general/FormSelect.vue';
import FormRadioGroup from './components/general/FormRadioGroup.vue';
import FormRadioButton from './components/general/FormRadioButton.vue';
import FormCheckboxGroup from './components/general/FormCheckboxGroup.vue';
import Pagination from './components/general/Pagination.vue';
import Danger from './components/general/Danger.vue';

// Vue.use(VueResource);

// Vue.component(
//   'multiselect',
//   Multiselect
// );

// Vue.component(
//   'vue-croppie',
//   Croppie
// );

// Vue.component(
//   'b-modal', BModal
// );

// Vue.component(
//   'Button', Button
// );

// Vue.component(
//   'MainTemplate', MainTemplate
// );

// Vue.directive('b-modal', VBModal);
// Vue.component('b-form-group', BFormGroup); 
// Vue.component('b-form-select', BFormSelect);

// Vue.component('b-form-input', BFormInput);
// Vue.component('b-form-textarea', BFormTextarea);

// Vue.component('b-form-radio-group', BFormRadioGroup );
// Vue.component('b-form-checkbox', BFormCheckbox );
// Vue.component('b-form-radio', BFormRadio );
// Vue.component('b-form-checkbox-group', BFormCheckboxGroup);

// Vue.component('b-pagination', BPagination);


// Vue.directive('b-tooltip', VBTooltip);

// Vue.component('b-badge', BBadge);


// Set a proper title
// router.beforeEach((to, from, next) => {
//   document.title = 'idaas.nl - ' + ((to.meta ? to.meta.label : null) || to.name || 'home')
//   next()
// })

const app = createApp(App);

Vue.configureCompat({
    COMPONENT_V_MODEL: false,
    ATTR_ENUMERATED_COERCION: false
})

// app.use(pinia);
app.use(router);
// app.use(i18n);
app.mount('#app');

app.component('MainTemplate', MainTemplate);
app.component('FormGroup', FormGroup);
app.component('FormInput', FormInput);
app.component('Danger', Danger);
app.component('multiselect', Multiselect);
app.component('FormTextarea', FormTextarea);
app.component('FormCheckbox', FormCheckbox);
app.component('FormSelect', FormSelect);
app.component('MenuButton', MenuButton);
app.component('FormRadioGroup', FormRadioGroup);
app.component('FormRadioButton', FormRadioButton);
app.component('FormCheckboxGroup', FormCheckboxGroup);
app.component('Pagination', Pagination);
