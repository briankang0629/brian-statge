<template>
    <div>
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <!--<li class="nav-item d-none d-sm-inline-block">-->
                <!--<a href="index3.html" class="nav-link">Home</a>-->
                <!--</li>-->
                <!--<li class="nav-item d-none d-sm-inline-block">-->
                <!--<a href="#" class="nav-link">Contact</a>-->
                <!--</li>-->
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" data-widget="control-sidebar" @click="logout()" data-slide="true" href="#"
                       role="button">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->
    </div>
</template>

<script>
    export default {
        data() {
            return {}
        },
        mounted() {
            //解決ipad寬度 768 選單出現問題
            if(screen.width < 992) {
                let el = document.querySelector('body');
                el.setAttribute('class','sidebar-closed sidebar-collapse');
            }
        },
        methods: {
            logout() {
                //請求參數設定
                const request = {
                    method: 'post',
                    url: '/api/logout',
                }

                //請求
                this.$store.dispatch('authRequest', request).then((response) => {
                    if (response.status === 'success') {
                        //unset localStorage
                        localStorage.removeItem('auth');

                        //unset localStorage
                        localStorage.removeItem('permission')

                        //轉跳登入頁
	                    this.$router.push('/login').then(() => { this.$root.notify(response) })

                    }
                }).catch((error) => {
                    this.$root.notify(error)
                })
            }
        }

    }
</script>
