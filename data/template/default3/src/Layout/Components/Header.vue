<template>
    <div :class="headerbg" class="app-header header-shadow">
        <div class="logo-src"/>
        <div class="app-header__content m-brand">
            <div class="app-header-left m-brand__tools">
                <div class="m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-left m-dropdown--align-push" data-dropdown-toggle="click" aria-expanded="true">
                    <a href="#" class="dropdown-toggle m-dropdown__toggle btn btn-outline-metal m-btn m-btn--pill">
                        <span>
                            <i class="nav-link-icon pe-7s-settings" style="display: inline-block;"></i>
                            {{ $t('Projects') }}
                        </span>
                    </a>
                    <div class="m-dropdown__wrapper">
                        <span class="m-dropdown__arrow m-dropdown__arrow--left m-dropdown__arrow--adjust"></span>
                        <div class="m-dropdown__inner br-10">
                            <div class="m-dropdown__body pa-0">
                                <div class="m-dropdown__header pa-0">
                                    <div class="dropdown__menu-header-inner bg-success">
                                        <div class="menu-header-image opacity-1 dd-header-bg-3"></div>
                                        <div class="menu-header-content text-left">
                                            <h5 class="menu-header-title">{{ $t('Overview') }}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="m-dropdown__content">
                                    <ul class="m-nav nav flex-column">                                        
                                        <li class="nav-item" v-for="permission_project in permission_projects" v-bind:key="permission_project" :class="[permission_project.id == current_project_id ? 'active' : '']">
                                            <a @click="setProjectId(permission_project.id)" class="m-nav__link">
                                                <i class="dropdown-icon lnr-file-empty"></i>
                                                <span class="m-menu__link-text">
                                                    <font color="#6f727d">{{ permission_project.title }}</font>
                                                </span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="app-header-right">
                <div class="header-dots">
                    <!--notification start-->
                    <div class="dropdown" :class="[is_show ? 'show' : '']" v-click-outside="onClickOutside" @click="isShow()">
                        <button aria-haspopup="true" aria-expanded="false" type="button" class="btn btn-link dropdown-toggle dropdown-toggle-no-caret p-0 mr-2">
                            <span>
                                <div class="icon-wrapper icon-wrapper-alt rounded-circle">
                                    <div class="icon-wrapper-bg bg-danger"></div>
                                    <i class="lnr-bullhorn text-danger"></i>
                                    <div class="badge badge-dot badge-dot-sm badge-danger">{{ $t('Notifications') }}</div>
                                </div>
                            </span>
                        </button>
                        <div role="menu" class="dropdown-menu dropdown-menu-right dropdown-menu-xl" :class="[is_show ? 'show' : '']">
                            <div class="dropdown-menu-header mb-0">
                                <div class="dropdown-menu-header-inner bg-deep-blue">
                                    <div class="menu-header-image opacity-1 dd-header-bg-2"></div>
                                    <div class="menu-header-content text-dark">
                                        <h5 class="menu-header-title">{{ $t('Notifications') }}</h5>
                                        <h6 v-if="unread_message_count > 0" class="menu-header-subtitle">{{ $t('You have') }} <b>{{ unread_message_count }}</b> {{ $t('unread messages') }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="nav-justified">
                                <div class="tabs card-header-tab-animation">
                                    <div class="card-header">
                                        <ul role="tablist" class="nav nav-tabs card-header-tabs">
                                            <li role="presentation" class="nav-item">
                                                <a role="tab"   data-toggle="tab" href="#tab-messages-header" class="nav-link" :class="[is_msg ? 'active' : '']" @click="checkTab('msg')">{{ $t('Messages') }}</a>
                                            </li>
                                            <li role="presentation" class="nav-item">
                                                <a role="tab"  data-toggle="tab" href="#tab-events-header" class="nav-link" :class="[is_event ? 'active' : '']" @click="checkTab('msg')">{{ $t('Events') }}</a>
                                            </li>
                                            <li role="presentation" class="nav-item">
                                                <a role="tab"  data-toggle="tab" href="#tab-status-header" class="nav-link" :class="[is_stat ? 'active' : '']" @click="checkTab('msg')">{{ $t('Status') }}</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="tab-content">
                                        <div role="tabpanel"  class="tab-pane p-3 card-body show fade" :class="[is_msg ? 'active' : '']" id="tab-messages-header">
                                            <div class="scroll-gradient">
                                                <div class="scroll-area-sm">
                                                    <section class="ps-container scrollbar-container ps ps--theme_default">
                                                        <div class="vertical-time-simple vertical-without-time vertical-timeline vertical-timeline--animate vertical-timeline--one-column">
                                                            
                                                            <div v-for="message in messages" v-bind:key="message.message_id" class="vertical-timeline-item vertical-timeline-element">
                                                                <div>
                                                                    <span class="vertical-timeline-element-icon bounce-in"></span>
                                                                    <div class="vertical-timeline-element-content bounce-in">
                                                                        <p>              
                                                                            <a v-if="message.isRead == 1" :href="'/index.php/messages/view/id/' + message.message_id">
                                                                                {{ message.subject }} (from {{ message.from }})
                                                                            </a>
                                                                            <a v-else :href="'/index.php/messages/view/id/' + message.message_id">
                                                                                <b>{{ message.subject }} (from {{ message.from }})</b> 
                                                                            </a>
                                                                        </p>
                                                                        <span class="vertical-timeline-element-date"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                        </div>
                                                    </section>
                                                </div>
                                            </div>
                                        </div>
                                        <div role="tabpanel" class="tab-pane p-3 card-body fade" :class="[is_event ? 'active' : '']" id="tab-events-header">
                                            <div class="scroll-gradient">
                                                <div class="scroll-area-sm">
                                                    <section class="ps-container scrollbar-container ps ps--theme_default">
                                                        <div class="vertical-without-time vertical-timeline vertical-timeline--animate vertical-timeline--one-column">
                                                            <div class="vertical-timeline-item vertical-timeline-element">
                                                                <div>
                                                                    <span class="vertical-timeline-element-icon bounce-in">
                                                                        <i class="badge badge-dot badge-dot-xl badge-success"></i>
                                                                    </span>
                                                                    <div class="vertical-timeline-element-content bounce-in">
                                                                        <h4 class="timeline-title">{{ $t('All Hands Meeting') }}</h4>
                                                                        <p>Lorem ipsum dolor sic amet, today at <a href="javascript:void(0);">12:00 PM</a></p>
                                                                        <span class="vertical-timeline-element-date"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="vertical-timeline-item vertical-timeline-element">
                                                                <div>
                                                                    <span class="vertical-timeline-element-icon bounce-in">
                                                                        <i class="badge badge-dot badge-dot-xl badge-warning"></i>
                                                                    </span>
                                                                    <div class="vertical-timeline-element-content bounce-in">
                                                                        <p>{{ $t('Another meeting today') }}, at <b class="text-danger">12:00 PM</b></p>
                                                                        <p>{{ $t('Yet another one') }}, at <span class="text-success">15:00 PM</span></p>
                                                                        <span class="vertical-timeline-element-date"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </section>
                                                </div>
                                            </div>
                                        </div>
                                        <div role="tabpanel" class="tab-pane pt-3 card-body fade" :class="[is_stat ? 'active' : '']" id="tab-status-header">
                                            <div>
                                                <div class="no-results">
                                                    <div class="swal2-icon swal2-success swal2-animate-success-icon">
                                                        <div class="swal2-success-circular-line-left" style="background-color: rgb(255, 255, 255);"></div>
                                                        <span class="swal2-success-line-tip"></span>
                                                        <span class="swal2-success-line-long"></span>
                                                        <div class="swal2-success-ring"></div>
                                                        <div class="swal2-success-fix" style="background-color: rgb(255, 255, 255);"></div>
                                                        <div class="swal2-success-circular-line-right" style="background-color: rgb(255, 255, 255);"></div>
                                                    </div>
                                                    <div class="results-subtitle">{{ $t('All caught up!') }}</div>
                                                    <div class="results-title">{{ $t('There are no system errors!') }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <ul class="nav flex-column">
                                    <li class="nav-item-divider nav-item mt-0"></li>
                                    <li class="nav-item-btn text-center nav-item">
                                        <button class="btn-shadow btn-wide btn-pill btn btn-focus btn-sm">{{ $t('View Latest Changes') }}</button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!--notification end-->

                <!--language selection start -->
                    <div class="dropdown">
                        <button type="button" data-toggle="dropdown" class="p-0 mr-2 btn btn-link" aria-expanded="false">
                            <span class="icon-wrapper icon-wrapper-alt rounded-circle">
                                <div class="icon-wrapper icon-wrapper-alt rounded-circle">
                                    <div class="icon-wrapper-bg bg-focus"></div>
                                    <div class="language-icon">
                                        <span  data-v-ef4411aa="" v-if="this.$user.default_lang === 'dutch'"   class="opacity-8 flag flag-nl normal-flag" style="transform: scale(0.5);"></span>
                                        <span data-v-ef4411aa="" v-else class="mr-3 opacity-8 flag flag-us normal-flag" style="transform: scale(0.5);" ></span>
                                    </div>
                                </div>
                            </span>
                        </button>
                        <div tabindex="-1" role="menu" aria-hidden="true" class="rm-pointers dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(42px, 46px, 0px);">
                            <div class="dropdown-menu-header">
                                <div class="dropdown-menu-header-inner pt-4 pb-4 bg-focus">
                                    <div class="menu-header-image opacity-05"></div>
                                    <div class="menu-header-content text-center text-white">
                                        <h6 class="menu-header-subtitle mt-0">
                                            {{ $t('Choose Language') }}
                                        </h6>
                                    </div>
                                </div>
                            </div>
                            <button type="button" tabindex="0" class="dropdown-item" @click="switch_lang('dutch')">
                                <span data-v-ef4411aa="" class="mr-3 opacity-8 flag flag-nl normal-flag" style="transform: scale(0.5);"></span>
                                <span>{{ $t('Netherlands') }}</span>
                            </button>
                            <button type="button" tabindex="0" class="dropdown-item"  @click="switch_lang('english')">
                                <span data-v-ef4411aa="" class="mr-3 opacity-8 flag flag-us normal-flag" style="transform: scale(0.5);"></span>
                                <span>{{ $t('English') }}</span>
                            </button>
                        </div>
                    </div>
                    <!--language selection end-->
                </div>
                <!-- <HeaderDots/> -->
                <UserArea/>
            </div>
        </div>
        <div class="app-header__mobile-menu">
            <div>
                <button type="button" class="hamburger close-sidebar-btn hamburger--elastic" v-bind:class="{ 'is-active' : isOpen }" @click="toggleMobile('closed-sidebar-open')">
                    <span class="hamburger-box">
                        <span class="hamburger-inner"></span>
                    </span>
                </button>
            </div>
        </div>
        <div class="app-header__menu">
            <span>
                <b-button class="btn-icon btn-icon-only" variant="primary" size="sm" v-bind:class="{ 'active' : isOpenMobileMenu }" @click="toggleMobile2('header-menu-open')">
                    <div class="btn-icon-wrapper">
                        <font-awesome-icon icon="ellipsis-v"/>
                    </div>
                </b-button>
            </span>
        </div>
    </div>
</template>

<script>
    // import SearchBox from './Header/SearchBox';
    // import MegaMenu from './Header/MegaMenu';
    // import HeaderDots from './Header/HeaderDots';
    import Vue from 'vue';
    import UserArea from './Header/HeaderUserArea';
    import {library} from '@fortawesome/fontawesome-svg-core';
    import {faEllipsisV} from '@fortawesome/free-solid-svg-icons';
    import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
    import axios from 'axios';
    import vClickOutside from 'v-click-outside';
    Vue.use(vClickOutside);
    library.add(
        faEllipsisV,
    );
    export default {
        name: "Header",
        components: {
            // SearchBox,
            // MegaMenu,
            // HeaderDots,
            UserArea,
            'font-awesome-icon': FontAwesomeIcon,
        },
        data() {
            return {
                isOpen: false,
                isOpenMobileMenu: false,
                is_show: false,
                is_msg: true,
                is_event: false,
                is_stat: false,
                permission_projects: [],
                current_project_id: localStorage.getItem('permission_project_id') ? localStorage.getItem('permission_project_id') : 0,
                site_url: localStorage.getItem('site_url'),
                user_role: '',
                unread_message_count: 0,
                messages: [],
            }
        },
        props: {
            headerbg: String
        },
        directives: {
            clickOutside: vClickOutside.directive
        },
        methods: {
            toggleMobile(className) {
                const el = document.body;
                this.isOpen = !this.isOpen;

                if (this.isOpen) {
                    el.classList.add(className);
                } else {
                    el.classList.remove(className);
                }
            },
            toggleMobile2(className) {
                const el = document.body;
                this.isOpenMobileMenu = !this.isOpenMobileMenu;

                if (this.isOpenMobileMenu) {
                    el.classList.add(className);
                } else {
                    el.classList.remove(className);
                }
            },
            switch_lang: function(lang) {
                let fd = new FormData();
                fd.append('lang', lang);
                axios.post('/index.php/switch-language', fd).then(function () {
                    window.location.reload();
                })
            },
            isShow: function() {
                this.is_show = true;
            },
            checkTab: function(tab) {
                if(tab === 'msg')
                {
                    this.is_msg = true;
                    this.is_event = false;
                    this.is_stat = false;
                }
                else if(tab === 'event')
                {
                    this.is_msg = false;
                    this.is_event = true;
                    this.is_stat = false;
                }
                else
                {
                    this.is_msg = false;
                    this.is_event = false;
                    this.is_stat = true;
                }
            },
            onClickOutside () {
                this.is_show = false;
            },
            get_projects: function() {
                let fd = new FormData();
                var obj = this;

                axios.post('/index.php/get-projects', fd)
                .then(function (response) {

                    if (response.data.length > 0) {
                        for (var i = 0; i < response.data.length; i++) {                        
                            obj.permission_projects.push(response.data[i]);
                        }

                        localStorage.setItem("first_project_id", response.data[0]['id']);
                    }

                    // window.location.reload();
                })
            },
            setProjectId: function(permission_project_id) {

                let fd = new FormData();
                fd.append('selected_project_id', permission_project_id);

                axios.post('/index.php/set-project-id', fd)
                .then(function (response) {

                    if (response.data.success) {
                        localStorage.setItem("permission_project_id", permission_project_id);
                        let current_url = window.location.href;
                        let res = current_url.split("?");
                        let parameter_array = res[0].split("/");
                        let update_url = '';

                        if (parameter_array.includes("projects") && parameter_array.includes("id")) {

                            parameter_array[parameter_array.length-1] = permission_project_id;
                            update_url = parameter_array.join("/");

                        } else {
                            update_url = res[0] + "?selected_project_id=" + permission_project_id;
                        }

                        window.location.href = update_url;
                    }
                });
            },
            user_role_update() {
                let fd = new FormData();
                var obj = this;

                axios.post('/index.php/sidevar-menu-update', fd)
                .then(function (response) {
                    obj.user_role = response.data.role;
                });                
            },
            notification_update() {
                let fd = new FormData();
                var obj = this;

                axios.post('/index.php/notification-update', fd)
                .then(function (response) {
                    obj.unread_message_count = response.data.unread_message_count;
                    for (var i = 0; i < response.data.messages.length; i++) {
                        obj.messages.push(response.data.messages[i]);
                    }                    
                });                
            },
        },
        created() {
            this.get_projects();

        },
        mounted() {
            this.user_role_update();
            this.notification_update();
        },
    };

</script>
