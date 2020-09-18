import Vue from 'vue'
import './plugins/vuetify'

import BootstrapVue from "bootstrap-vue"

import VueI18n from 'vue-i18n';
import messages from './lang';

import App from './App'

import Default from './Layout/Wrappers/baseLayout.vue';
import Pages from './Layout/Wrappers/pagesLayout.vue';
import Apps from './Layout/Wrappers/appLayout.vue';

import Dashboard from './Pages/Dashboards/Statistics.vue';
import Login from './Pages/Login.vue';
import Myaccount from './Pages/Myaccount.vue';
import Logs from './Pages/Logs/ListGroups.vue';
import Permissions from './Pages/Admin/Permissions';
import Projects from './Pages/Projects/List.vue';
import ProjectEdit from './Pages/Projects/Edit.vue';
import IntegrationPages from './Pages/Integrations/IntegrationPages.vue';
import Messages from './Pages/Messages/Messages.vue';

import AdminOverview from './Pages/Admin/Overview.vue';
import AdminSettings from './Pages/Admin/Settings.vue';
import AdminTestApiConnection from './Pages/Admin/TestApiConnection.vue';
import AdminMaintenance from './Pages/Admin/Maintenance.vue';
import AdminSendMessage from './Pages/Admin/SendMessage.vue';

import PartnerOverview from './Pages/Partner/Overview.vue';

Vue.config.productionTip = false;

Vue.use(BootstrapVue);

Vue.use(VueI18n);
export const i18n = new VueI18n({
  locale: localStorage.getItem("default_lang"),
  fallbackLocale: localStorage.getItem("default_lang"),
  messages
});

Vue.component('default-layout', Default);
Vue.component('userpages-layout', Pages);
Vue.component('apps-layout', Apps);
Vue.component('app-component', App);

// Pages
Vue.component('login-component', Login);
Vue.component('myaccount-component', Myaccount);
Vue.component('dashboard-component', Dashboard);
Vue.component('logs-component', Logs);
Vue.component('admin-permissions-component', Permissions);
Vue.component('admin-projectslist-component', Projects);
Vue.component('admin-projectedit-component', ProjectEdit);
Vue.component('integration-pages-component', IntegrationPages);
Vue.component('messages-component', Messages);

Vue.component('admin-overview-component', AdminOverview);
Vue.component('admin-settings-component', AdminSettings);
Vue.component('admin-test-api-connection-component', AdminTestApiConnection);
Vue.component('admin-maintenance-component', AdminMaintenance);
Vue.component('admin-sendmessage-component', AdminSendMessage);

Vue.component('partner-overview-component', PartnerOverview);

Vue.prototype.$user = {
  username:localStorage.getItem('username'),
  user_email:localStorage.getItem('user_email'),
  fullname:localStorage.getItem('fullname'),
  profilepicture:localStorage.getItem('profile_picture'),
  default_lang:localStorage.getItem('default_lang'),
};
Vue.prototype.$site_url = localStorage.getItem('site_url');

new Vue({
  el: '#app',
  i18n,
  components: { 
    App,
    Login,
    Myaccount,
    Dashboard,
    Logs,
    Permissions,
    Projects,
    IntegrationPages,
    Messages,
    AdminOverview,
    AdminSettings,
    AdminTestApiConnection,
    AdminMaintenance
  }
});

