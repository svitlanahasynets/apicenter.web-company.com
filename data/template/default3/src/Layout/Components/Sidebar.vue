<style>
    .la-ball-scale-ripple-multiple,
    .la-ball-scale-ripple-multiple > span {
        position: relative;
        float: right;
        -webkit-box-sizing: border-box;
           -moz-box-sizing: border-box;
                box-sizing: border-box;
    }
    .la-ball-scale-ripple-multiple {
        display: block;
        font-size: 0;
        color: #fff;
    }
    .la-ball-scale-ripple-multiple.la-dark {
        color: #333;
    }
    .la-ball-scale-ripple-multiple > span {
        display: inline-block;
        float: none;
        background-color: currentColor;
        border: 0 solid currentColor;
    }
    .la-ball-scale-ripple-multiple {
        width: 32px;
        height: 32px;
    }
    .la-ball-scale-ripple-multiple > span {
        position: absolute;
        top: 0;
        left: 0;
        width: 32px;
        height: 32px;
        background: transparent;
        border-width: 2px;
        border-radius: 100%;
        opacity: 0;
        animation-iteration-count: 1;
        -webkit-animation: ball-scale-ripple-multiple 1.25s 0s infinite cubic-bezier(.21, .53, .56, .8);
           -moz-animation: ball-scale-ripple-multiple 1.25s 0s infinite cubic-bezier(.21, .53, .56, .8);
             -o-animation: ball-scale-ripple-multiple 1.25s 0s infinite cubic-bezier(.21, .53, .56, .8);
                animation: ball-scale-ripple-multiple 1.25s 0s infinite cubic-bezier(.21, .53, .56, .8);
    }
    .la-ball-scale-ripple-multiple > span:nth-child(1) {
        animation-iteration-count: 1;
        -webkit-animation-delay: 0s;
           -moz-animation-delay: 0s;
             -o-animation-delay: 0s;
                animation-delay: 0s;
    }
    .la-ball-scale-ripple-multiple > span:nth-child(2) {
        animation-iteration-count: 1;
        -webkit-animation-delay: .25s;
           -moz-animation-delay: .25s;
             -o-animation-delay: .25s;
                animation-delay: .25s;
    }
    .la-ball-scale-ripple-multiple > span:nth-child(3) {
        animation-iteration-count: 1;
        -webkit-animation-delay: .5s;
           -moz-animation-delay: .5s;
             -o-animation-delay: .5s;
                animation-delay: .5s;
    }
    .la-ball-scale-ripple-multiple.la-sm {
        width: 16px;
        height: 16px;
    }
    .la-ball-scale-ripple-multiple.la-sm > span {
        width: 16px;
        height: 16px;
        border-width: 1px;
    }
    .la-ball-scale-ripple-multiple.la-2x {
        width: 64px;
        height: 64px;
    }
    .la-ball-scale-ripple-multiple.la-2x > span {
        width: 64px;
        height: 64px;
        border-width: 4px;
    }
    .la-ball-scale-ripple-multiple.la-3x {
        width: 96px;
        height: 96px;
    }
    .la-ball-scale-ripple-multiple.la-3x > span {
        width: 96px;
        height: 96px;
        border-width: 6px;
    }
    /*
     * Animation
     */
    @-webkit-keyframes ball-scale-ripple-multiple {
        0% {
            opacity: 1;
            -webkit-transform: scale(.1);
                    transform: scale(.1);
        }
        70% {
            opacity: .5;
            -webkit-transform: scale(1);
                    transform: scale(1);
        }
        95% {
            opacity: 0;
        }
    }
    @-moz-keyframes ball-scale-ripple-multiple {
        0% {
            opacity: 1;
            -moz-transform: scale(.1);
                 transform: scale(.1);
        }
        70% {
            opacity: .5;
            -moz-transform: scale(1);
                 transform: scale(1);
        }
        95% {
            opacity: 0;
        }
    }
    @-o-keyframes ball-scale-ripple-multiple {
        0% {
            opacity: 1;
            -o-transform: scale(.1);
               transform: scale(.1);
        }
        70% {
            opacity: .5;
            -o-transform: scale(1);
               transform: scale(1);
        }
        95% {
            opacity: 0;
        }
    }
    @keyframes ball-scale-ripple-multiple {
        0% {
            opacity: 1;
            -webkit-transform: scale(.1);
               -moz-transform: scale(.1);
                 -o-transform: scale(.1);
                    transform: scale(.1);
        }
        70% {
            opacity: .5;
            -webkit-transform: scale(1);
               -moz-transform: scale(1);
                 -o-transform: scale(1);
                    transform: scale(1);
        }
        95% {
            opacity: 0;
        }
    }  
</style>

<template>
    <div :class="sidebarbg" class="app-sidebar sidebar-shadow">
        <div class="app-header__logo">
            <div class="logo-src"/>
            <div class="header__pane ml-auto hidden-lg-only">
                <button type="button" class="hamburger close-sidebar-btn hamburger--elastic">
                    <span class="hamburger-box">
                        <span class="hamburger-inner"></span>
                    </span>
                </button>
            </div>
        </div>
        <div class="app-sidebar-content">
            <VuePerfectScrollbar class="app-sidebar-scroll" v-once>
                <sidebar-menu showOneChild :menu="menu" @item-click="onItemClick"/>
            </VuePerfectScrollbar>
            <div class="bottom-logo-section" v-if="user_role == 'partner'">
                <img width="150" class="bottom-logo" :src="partner_logo" alt="">
            </div>
            <div v-else class="bottom-logo-section">
                <img width="150" class="bottom-logo" src="@/assets/images/sidebar/logo_apicenter.png" alt="">
            </div>
        </div>
    </div>
</template>

<script>
    import {SidebarMenu} from 'vue-sidebar-menu'
    import VuePerfectScrollbar from 'vue-perfect-scrollbar'
    import axios from 'axios'

    export default {
        components: {
            SidebarMenu,
            VuePerfectScrollbar
        },
        data() {
            return {
                isOpen: false,
                sidebarActive: false,
                currentProjectId: localStorage.getItem("permission_project_id"),
                user_role: '',
                partner_logo: '',
                isAdmin: false,                
                metricsSubMenu: {
                    href: '/metrics',
                    title: this.$i18n.t('Metrics'),
                    suffix: true,
                },
                invoicesSubMenu: {
                    href: '/invoices',
                    title: this.$i18n.t('Invoices'),
                    suffix: true,
                },
                afasSubMenu: {
                    href: '/afas',
                    title: this.$i18n.t('AFAS'),
                    suffix: true,
                },
                salesentriesSubMenu: {
                    href: '/salesentries',
                    title: this.$i18n.t('SalesEntries'),
                    suffix: true,
                },
                customersSubMenu: {
                    href: '/customers',
                    title: this.$i18n.t('Customers'),
                    suffix: true,
                },
                productsSubMenu: {
                    href: '/products',
                    title: this.$i18n.t('Products'),
                    suffix: true,
                },
                ordersSubMenu: {
                    href: '/orders',
                    title: this.$i18n.t('Orders'),
                    suffix: true,
                },
                shipmentsSubMenu: {
                    href: '/shipments',
                    title: this.$i18n.t('Shipments'),
                    suffix: true,
                },                
                exactSubMenu: {
                    href: '/exact',
                    title: this.$i18n.t('Exact'),
                    suffix: true,
                },
                customModuleSubMenu: {
                    href: '/custom_module',
                    title: this.$i18n.t('Custom Module'),
                    suffix: true,
                },
                optiplySubMenu: {
                    href: '/optiply',
                    title: this.$i18n.t('Optiply'),
                    suffix: true,
                },
                optiplyBuyorderSubMenu: {
                    href: '/optiply_buyorder',
                    title: this.$i18n.t('Optiply Buyorder'),
                    suffix: true,
                },
                optiplySellorderSubMenu: {
                    href: '/optiply_sellorder',
                    title: this.$i18n.t('Optiply Sellorder'),
                    suffix: true,
                },
                optiplySuppliersSubMenu: {
                    href: '/optiply_suppliers',
                    title: this.$i18n.t('Optiply Suppliers'),
                    suffix: true,
                },
                optiplyReturnSubMenu: {
                    href: '/optiply_return',
                    title: this.$i18n.t('Optiply Return'),
                    suffix: true,
                },
                adminDebugging: {
                    href: '/admindebugging',
                    title: this.$i18n.t('Admin Debugging'),
                    suffix: true,
                },
                integrationSection: {
                    title: this.$i18n.t('Integration section'),
                    icon: 'pe-7s-tools',
                    child: [
                        {
                            href: '/features',
                            title: this.$i18n.t('Features'),
                            suffix: true,
                        },
                        {
                            href: '/schedule',
                            title: this.$i18n.t('Schedule'),
                            suffix: true,
                        },
                        {
                            href: '/manual-sync',
                            title: this.$i18n.t('Manual sync'),
                            suffix: true,
                        },
                        {
                            href: '/integration',
                            title: this.$i18n.t('Integration'),
                            suffix: true,
                        },
                        {
                            href: '/settings',
                            title: this.$i18n.t('Settings'),
                            suffix: true,
                        },
                    ]
                },
                adminMenu: {
                    title: this.$i18n.t('Admin'),
                    icon: 'pe-7s-id',
                    child: [
                        {
                            href: '/projectList',
                            title: this.$i18n.t('Project List'),
                            suffix: false,
                        },
                        {
                            href: '/admin-overview',
                            title: this.$i18n.t('Overview'),
                            suffix: false,
                        }, 
                        {
                            href: '/permissions',
                            title: this.$i18n.t('User permissions'),
                            suffix: false,
                        },  
                        {
                            href: '/admin-settings',
                            title: this.$i18n.t('Settings'),
                            suffix: false,
                        }, 
                        {
                            href: '/admin-test-api-connection',
                            title: this.$i18n.t('Test API Connection'),
                            suffix: false,
                        }, 
                        {
                            href: '/admin-maintenance',
                            title: this.$i18n.t('Maintenance'),
                            suffix: false,
                        },                         
                        {
                            href: '/projects/create',
                            title: this.$i18n.t('New Project'),
                            suffix: false,
                        },
                        {
                            href: '/projects/manageFormFieldsOrders',
                            title: this.$i18n.t('Arrange Fields Order'),
                            suffix: false,
                        },
                        {
                            href: '/admin-sendmessage',
                            title: this.$i18n.t('Send Message'),
                            suffix: true,
                        }                           
                    ]
                },
                partnerMenu: {
                    title: this.$i18n.t('Partner Menu'),
                    icon: 'pe-7s-id',
                    child: [
                        {
                            href: '/partner-overview',
                            title: this.$i18n.t('Overview'),
                            suffix: false,
                        }                          
                    ]
                },
                menu: [
                    {
                        header: true,
                        title: this.$i18n.t('Menu'),
                    },
                    {
                        href: '/index.php',
                        title: this.$i18n.t('Dashboard'),
                        icon: 'pe-7s-display1',
                        suffix: false,
                    },
                    {
                        title: this.$i18n.t('Logs'),
                        // href: '/logs',
                        icon: 'pe-7s-news-paper',
                        // suffix: false,
                        child: [                       
                                                                                   
                        ]
                    },
                    {
                        title: this.$i18n.t('API settings'),
                        href: '',
                        icon: 'pe-7s-way',
                        suffix: false,
                    },
                    {
                        title: this.$i18n.t('Message center'),
                        href: '/message-center',
                        icon: 'pe-7s-mail-open-file',
                        suffix: true,
                    },                 
                    
                ],
                collapsed: true,
                windowWidth: 0,
            }
        },
        props: {
            sidebarbg: String,
        },
        methods: {

            getWindowWidth() {
                const el = document.body;
                this.windowWidth = document.documentElement.clientWidth;
                if (this.windowWidth < '992') {
                    el.classList.add('closed-sidebar', 'closed-sidebar-md');
                } else {
                    el.classList.remove('closed-sidebar', 'closed-sidebar-md');
                }
            },

            onItemClick(event) {

                var old_children = document.querySelectorAll('.la-ball-scale-ripple-multiple');

                if (old_children != null) {
                    var len = old_children.length;

                    for (var i = 0; i < len; i++) {
                        if (old_children[i].className.toLowerCase() == "la-ball-scale-ripple-multiple") {
                            old_children[i].parentNode.removeChild(old_children[i]);
                        }
                    }
                }

                var span = document.createElement('span');
                span.classList.add('la-ball-scale-ripple-multiple');

                var span1 = document.createElement('span');
                var span2 = document.createElement('span');
                var span3 = document.createElement('span');
                span.appendChild(span1);
                span.appendChild(span2);
                span.appendChild(span3);


                if (event.target.tagName == 'A') {

                    span.setAttribute("style", "margin-right: -15px; color:#3f6ad8;");
                    event.target.appendChild(span);
                    
                } else if (event.target.tagName == 'SPAN' || event.target.tagName == 'DIV') {

                    span.setAttribute("style", "margin-right: -15px; color:#3f6ad8;");
                    event.target.closest("a.vsm-link").appendChild(span);
                }                
            },

            add_suffix() {
                const menus = this.menu;
                
                menus.map(it => {  

                    const childs = it['child'];

                    if (it['title'] == 'API settings') {                        
                        if (localStorage.getItem("permission_project_id")) {
                            it['href'] = '/projects/edit/id/' + localStorage.getItem("permission_project_id");
                        } else {
                            it['href'] = '/projects/edit/id/' + localStorage.getItem("first_project_id");
                        }
                    } else if (it['href'] && !childs && it['suffix']) {
                        if (localStorage.getItem("permission_project_id")) {
                            it['href'] = it['href'] + '?selected_project_id=' + localStorage.getItem("permission_project_id");
                        }                        
                    }
                    
                    if (childs) {
                        for (var i = childs.length - 1; i >= 0; i--) {
                            let child = childs[i];

                            if (child['href'] && child['suffix']) {
                                if (localStorage.getItem("permission_project_id")) {
                                    child['href'] = child['href'] + '?selected_project_id=' + localStorage.getItem("permission_project_id");
                                }
                            }
                        }
                    }                    
                });
            },

            sidevar_menu_update() {
                let fd = new FormData();
                var obj = this;

                fd.append('current_project_id', obj.currentProjectId);

                axios.post('/index.php/sidevar-menu-update', fd)
                .then(function (response) {

                    obj.user_role = response.data.role;
                    localStorage.setItem("user_role", obj.user_role);

                    if (!response.data.availableProjects.length) {
                        localStorage.removeItem("permission_project_id");
                        localStorage.removeItem("first_project_id");
                    }

                    if (response.data.role == 'admin') {
                        obj.isAdmin = true;
                        obj.menu.push(obj.integrationSection);
                        obj.menu.push(obj.adminMenu);                       
                    } else if (response.data.role == 'partner') {
                        obj.partner_logo = response.data.partner_logo;
                        obj.menu.push(obj.partnerMenu);
                    }

                    for (let possible_function in response.data.possible_log_types_functions) {

                        if (response.data.possible_log_types_functions[possible_function] == "projectcontrol") {
                            obj.menu[2].child.push(obj.metricsSubMenu);
                        } else if (response.data.possible_log_types_functions[possible_function] == "importInvoices") {
                            obj.menu[2].child.push(obj.invoicesSubMenu);
                        } else if (response.data.possible_log_types_functions[possible_function] == "afas_setup_error") {
                            obj.menu[2].child.push(obj.afasSubMenu);
                        } else if (response.data.possible_log_types_functions[possible_function] == "importSalesEntry") {
                            obj.menu[2].child.push(obj.salesentriesSubMenu);
                        } else if (response.data.possible_log_types_functions[possible_function] == "importcustomers") {
                            obj.menu[2].child.push(obj.customersSubMenu);
                        } else if (response.data.possible_log_types_functions[possible_function] == "importarticles") {
                            obj.menu[2].child.push(obj.productsSubMenu);
                        } else if (response.data.possible_log_types_functions[possible_function] == "exportorders") {
                            obj.menu[2].child.push(obj.ordersSubMenu);
                        } else if (response.data.possible_log_types_functions[possible_function] == "tracktrace") {
                            obj.menu[2].child.push(obj.shipmentsSubMenu);
                        } else if (response.data.possible_log_types_functions[possible_function] == "exact_setup") {
                            obj.menu[2].child.push(obj.exactSubMenu);
                        } else if (response.data.possible_log_types_functions[possible_function] == "custom_cronjob") {
                            obj.menu[2].child.push(obj.customModuleSubMenu);
                        } else if (response.data.possible_log_types_functions[possible_function] == "optiply_connection") {
                            obj.menu[2].child.push(obj.optiplySubMenu);
                        } else if (response.data.possible_log_types_functions[possible_function] == "exact_buy_orders") {
                            obj.menu[2].child.push(obj.optiplyBuyorderSubMenu);
                        } else if (response.data.possible_log_types_functions[possible_function] == "exact_sell_orders") {
                            obj.menu[2].child.push(obj.optiplySellorderSubMenu);
                        } else if (response.data.possible_log_types_functions[possible_function] == "optiply_suppliers") {
                            obj.menu[2].child.push(obj.optiplySuppliersSubMenu);
                        } else if (response.data.possible_log_types_functions[possible_function] == "reimport_orders") {
                            obj.menu[2].child.push(obj.optiplyReturnSubMenu);
                        } else if (response.data.possible_log_types_functions[possible_function] == "admindebugging") {
                            if (response.data.role == 'admin') {
                                obj.menu[2].child.push(obj.adminDebugging);                      
                            }                            
                        }
                    }

                    obj.add_suffix();

                });                
            },
        },
        mounted() {
            this.$nextTick(function () {
                window.addEventListener('resize', this.getWindowWidth);
                //Init
                this.getWindowWidth();
            })
        },

        created() {
            this.sidevar_menu_update();
        },

        beforeDestroy() {
            window.removeEventListener('resize', this.getWindowWidth);
        },

        watch: {
            currentProjectId: function() {

            }
        }
    }
</script>
