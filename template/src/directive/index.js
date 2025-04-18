import permission from './permission/permission';
import hasPermi from './permission/hasPermi';

const install = function (Vue) {
  Vue.directive('permission', permission);
  Vue.directive('hasPermi', hasPermi);
};

if (window.Vue) {
  window['permission'] = permission;
  window['hasPermi'] = hasPermi;
  Vue.use(install); // eslint-disable-line
}

// permission.install = install;
// export default permission;
export default install;
