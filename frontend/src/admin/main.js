import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
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
import { InstallCodemirro } from "codemirror-editor-vue3";

const app = createApp(App);

// app.use(pinia);
app.use(router);
app.use(InstallCodemirro);
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
