export default [
    {
        name: 'dashboard',
        icon: 'fa-tachometer-alt',
        permission: '',
        toggle: false,
        link: '/'
    },
    {
        name: 'admin',
        icon: 'fa-user-md',
        permission: '',
        toggle: false,
        children: [
            {
                name: 'adminList',
                permission: '',
                link: '/admin/list'
            },
            {
                name: 'adminEdit',
                permission: '',
                link: '/admin/edit',
                hide: true,
            },
            {
                name: 'adminCreate',
                permission: '',
                link: '/admin/create',
                hide: true,
            },
            {
                name: 'permission',
                permission: '',
                link: '/admin/permission'
            },
            {
                name: 'permissionCreate',
                permission: '',
                link: '/admin/permission/create',
                hide: true,
            },
            {
                name: 'permissionEdit',
                permission: '',
                link: '/admin/permission/edit',
                hide: true,
            }
        ]
    },
    {
        name: 'user',
        icon: 'fa-users',
        permission: '',
        toggle: false,
        children: [
            {
                name: 'userList',
                permission: '',
                link: '/user/list'
            },
            {
                name: 'userEdit',
                permission: '',
                link: '/user/edit',
                hide: true,
            },
            {
                name: 'userCreate',
                permission: '',
                link: '/user/create',
                hide: true,
            },
            {
                name: 'userGroup',
                permission: '',
                link: '/user/group'
            },
            {
                name: 'userGroupEdit',
                permission: '',
                link: '/user/group/edit',
                hide: true,
            },
            {
                name: 'userGroupCreate',
                permission: '',
                link: '/user/group/create',
                hide: true,
            },
        ]
    },
    {
        name: 'product',
        icon: 'fa-shopping-cart',
        permission: '',
        toggle: false,
        children: [
            {
                name: 'productList',
                permission: '',
                link: '/product/list'
            },
            {
                name: 'productCategory',
                permission: '',
                link: '/product/category'
            },
            {
                name: 'productOption',
                permission: '',
                link: '/product/option'
            },
            {
                name: 'productSpecification',
                permission: '',
                link: '/product/specification'
            }
        ]
    },
    {
        name: 'stock',
        icon: 'fa-archive',
        permission: '',
        toggle: false,
        children: [
            {
                name: 'stockCategory',
                permission: '',
                link: '/stock/category'
            }
        ]
    },
    {
        name: 'report',
        icon: 'fa-map',
        permission: '',
        toggle: false,
        children: [
            {
                name: 'product',
                permission: '',
                link: '/report/product'
            },
        ]
    },
    {
        name: 'order',
        icon: 'fa-calculator',
        permission: '',
        toggle: false,
        children: [
            {
                name: 'orderList',
                permission: '',
                link: '/order/list'
            },
            {
                name: 'orderStatus',
                permission: '',
                link: '/order/status'
            }
        ]
    },
    {
        name: 'system',
        icon: 'fa-cog',
        permission: '',
        toggle: false,
        children: [
            {
                name: 'setting',
                permission: '',
                link: '/system/setting'
            },
        ]
    },
    {
        name: 'module',
        icon: 'fa-cubes',
        permission: '',
        toggle: false,
        children: [
            {
                name: 'moduleList',
                permission: '',
                link: '/module/list'
            },
        ]
    },
    {
        name: 'media',
        icon: 'fa-film',
        permission: '',
        toggle: false,
        children: [
            {
                name: 'image',
                permission: '',
                link: '/media/image'
            },
        ]
    },
    {
        name: 'logRecord',
        icon: 'fa-database',
        permission: '',
        toggle: false,
        children: [
            {
                name: 'logRecordList',
                permission: '',
                link: '/logRecord/list'
            },
        ]
    },
]
