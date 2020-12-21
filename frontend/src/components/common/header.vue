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
                let header = {
                    headers: {
                        'Authorization': JSON.parse(localStorage.getItem('auth')).token,
                    }
                }
                this.axios.post('/api/logout', {},  header).then(response => {
                    if (response.data.status == 'success') {
                        //unset localStorage
                        localStorage.removeItem('auth');

                        //unset localStorage
                        localStorage.removeItem('permission')

                        this.$Swal.fire({
                            icon: response.data.status,
                            title: '成功',
                            text: response.data.msg,
                            confirmButtonText: 'OK',
                        }).then(() => {
                            window.location.href = '/login';
                        });
                    }
                }).catch(error => {
                    this.$Swal.fire({
                        icon: error.response.data.status,
                        title: '失敗',
                        text: error.response.data.msg,
	                    confirmButtonText: 'OK',
                    }).then(() => {
	                    window.location.href = '/login';
                    });
                })
            }
        }

    }
</script>
