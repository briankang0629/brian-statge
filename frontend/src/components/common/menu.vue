<template>
    <!-- Main Sidebar Container -->
    <div>
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <router-link to="/">
                <a href="#" class="brand-link">
                    <img src="../../assets/img/AdminLTELogo.png" alt="AdminLTE Logo"
                         class="brand-image img-circle elevation-3" style="opacity: .8">
                    <span class="brand-text font-weight-light">{{ $t('common.manageSystem') }}</span>
                </a>
            </router-link>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img :src="$store.state.auth.picture" class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="#" class="d-block">{{ $t('common.user') }} : {{$store.state.auth.name}}</a>
                    </div>
                </div>
                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <!-- Add icons to the links using the .nav-icon class
                             with font-awesome or any other icon font library -->
                        <!--<li class="nav-header">內部系統</li>-->

                        <li v-for="(data, index) in menu" :key="index" class="nav-item" :class="navItemBinding(data)">
                            <a class="nav-link" :class="navLinkBinding(data)" @click="toggleNavigation(data , index)">
                                <i class="nav-icon fa" :class="data.icon"></i>
                                <p>
                                    {{ $t('menu.' + data.name) }}
                                    <i class="right" :class="{'fas fa-angle-left' : data.children}"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item" v-for="(subData, subIndex) in data.children" :key="subIndex">
                                    <a class="nav-link"
                                       :class="navLinkBinding(subData)"
                                       @click="toggleNavigation(subData)"
                                       v-show="!subData.hide"
                                    >
                                        <i class="fa fa-caret-right nav-icon"></i>
                                        <p>{{ $t('menu.' + subData.name) }}</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>
    </div>
</template>

<script>
    import menu from '@/menu'

    export default {
        name: 'menu',
        data() {
            return {
                menu: [],
                navItemBinding: (data) => [
                    {'has-treeview': (data.children && data.children > 0)},
                    {'menu-open': data.toggle === true}
                ],
                navLinkBinding: (data) => [
                    {'active': data.link === this.$route.fullPath}
                ],
                activeMenu: ''
            }
        },
        mounted() {
            this.init()
        },
        methods: {

            /**
             * init 初始化
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            init() {
                return Promise.all([
                    this.getSystemMenu(),
                ])
            },

            /**
             * getSystemMenu 取系統及權限開放的選單
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            getSystemMenu() {
                let request = {
                    url: '/api/systemMenu/config',
                    method: 'get'
                }

                this.$store.dispatch('authRequest', request).then((response) => {
                    //資料庫過濾的系統選單
                    let systemMenu = response.data
                    let deleteMenu = []
                    menu.forEach((data, key) => {
                        //判斷母選單
                        if (!systemMenu[data.name]) {
                            //將要刪除的母選單存起來 後面再刪掉 不然會影響下方迴圈
                            deleteMenu.push(key)
                        }

                        //判斷子選單
                        if ((data.children) && (systemMenu[data.name].subMenu)) {
                            //暫存子選單
                            let subMenu = systemMenu[data.name].subMenu

                            //判斷網站子選單有無開放
                            data.children.forEach((value, index) => {
                                if (!subMenu[value.name]) {
                                    menu[key].children.splice(index,1)
                                }
                            })
                        }
                    })

                    //刪除未開放母選單
                    deleteMenu.forEach((item, key)=> {
                        menu.splice(key, 1)
                    })

                    //存系統選單
                    this.menu = menu

                }).catch((error) => {
                    console.log(error)
                })
            },

            /**
             * toggleNavigation 判定啟用的選單
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            toggleNavigation(data, key = 0) {
                if (data.children) {
                    if (this.activeMenu === key) {
                        this.activeMenu = ''
                    }

                    if ((this.activeMenu)) {
                        menu[this.activeMenu].toggle = false;
                    }

                    if (this.activeMenu !== key) {
                        data.toggle = !data.toggle
                    }

                    this.activeMenu = key;
                } else if (this.$route.fullPath !== data.link) {
                    this.$router.push(data.link)
                }
            }
        }
    }
</script>
<style>
    .nav.nav-treeview .nav-item {
        /*background: #495053;*/
    }
</style>