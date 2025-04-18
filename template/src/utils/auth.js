import store from '@/store';
import vm from '@/main';
import { userMenusApi } from '@/api/public';
import { getRouterMenus } from '@/router';
export function setToken(token) {
  return store.commit('SET_TOKEN', token);
}
export function getToken() {
  return store.getters.token;
}
export function removeToken() {
  vm && vm.closeNotice();
  return store.commit('SET_TOKEN', '');
}

// 获取菜单权限
export const getMenus = () => {
  return new Promise((resolve, reject) => {
    userMenusApi()
      .then((response) => {
        const { menu, roles } = response.data;
        const menus = JSON.parse(JSON.stringify(menu));
        store.commit('user/SET_MENU_LIST', menus);
        store.commit('user/SET_PERMISSIONS', roles); // 按钮权限
        store.dispatch('user/getMember'); 
        store.dispatch('user/getDepartment'); 
        getRouterMenus(menu);
      
        resolve();
      })
      .catch((error) => {
        console.log(error);
        reject();
      });
  });
};
