<template>
    <div>
        <component :is="layout">
            <page-title :heading=heading :subheading=subheading :icon=icon></page-title>
            <div class="row">
                <div class="col-sm-12 col-md-7">
                    <div class="row">
                        <div class="col-sm-12 col-md-6">
                            <div class="card-shadow-primary mb-3 widget-chart widget-chart2 text-left card">
                                <div class="widget-chat-wrapper-outer">
                                    <div class="widget-chart-content"><h6 class="widget-subheading">{{ $t('Incoming API Calls') }}</h6>
                                        <div class="widget-chart-flex">
                                            <div class="widget-numbers mb-0 w-100">
                                                <div class="widget-chart-flex">
                                                    <div class="fsize-4">
                                                        <small class="opacity-5"></small>
                                                        {{ total_api_calls_received_count }}
                                                    </div>
                                                    <div class="ml-auto">
                                                        <div class="widget-title ml-auto font-size-lg font-weight-normal text-muted">
                                                            <span class="text-success pl-2">+14%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <div class="card-shadow-primary mb-3 widget-chart widget-chart2 text-left card">
                                <div class="widget-chat-wrapper-outer">
                                    <div class="widget-chart-content">
                                        <h6 class="widget-subheading">{{ $t('Sending API Calls') }}</h6>
                                        <div class="widget-chart-flex">
                                            <div class="widget-numbers mb-0 w-100">
                                                <div class="widget-chart-flex">
                                                    <div class="fsize-4 text-danger">
                                                        <small class="opacity-5 text-muted"></small>
                                                        {{ total_api_calls_send_count }}
                                                    </div>
                                                    <div class="ml-auto">
                                                        <div class="widget-title ml-auto font-size-lg font-weight-normal text-muted">
                                                            <span class="text-danger pl-2">
                                                                <span class="pr-1">
                                                                    <i class="fa fa-angle-up"></i>
                                                                </span>
                                                                8%
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <div class="card-shadow-primary mb-3 widget-chart widget-chart2 text-left card">
                                <div class="widget-chat-wrapper-outer">
                                    <div class="widget-chart-content">
                                        <h6 class="widget-subheading">{{ $t('Products Synced') }}</h6>
                                        <div class="widget-chart-flex">
                                            <div class="widget-numbers mb-0 w-100">
                                                <div class="widget-chart-flex">
                                                    <div class="fsize-4">
                                                                <span class="text-success pr-2">
                                                                    <font-awesome-icon icon="angle-down" />
                                                                </span>
                                                        <small class="opacity-5"></small>
                                                        {{ total_products_synced_count }}
                                                    </div>
                                                    <div class="ml-auto">
                                                        <div class="widget-title ml-auto font-size-lg font-weight-normal text-muted">
                                                            <span class="text-success pl-2">
                                                                <span class="pr-1">
                                                                    <font-awesome-icon icon="angle-down" />
                                                                </span>
                                                                15%
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <div class="card-shadow-primary mb-3 widget-chart widget-chart2 text-left card">
                                <div class="widget-chat-wrapper-outer">
                                    <div class="widget-chart-content">
                                        <h6 class="widget-subheading">{{ $t('Orders Synced') }}</h6>
                                        <div class="widget-chart-flex">
                                            <div class="widget-numbers mb-0 w-100">
                                                <div class="widget-chart-flex">
                                                    <div class="fsize-4">
                                                        <small class="opacity-5">$</small>
                                                        {{ total_orders_synced_count }}
                                                    </div>
                                                    <div class="ml-auto">
                                                        <div class="widget-title ml-auto font-size-lg font-weight-normal text-muted">
                                                            <span class="text-warning pl-2">+76%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-5">
                    <div class="mb-3 card">
                        <div class="card-body">
                            <div class="widget-chart widget-chart2 text-left p-0">
                                <div class="widget-chat-wrapper-outer">
                                    <div class="widget-chart-content">
                                        <div class="widget-chart-flex">
                                            <div class="widget-numbers mt-0">
                                                <div class="widget-chart-flex">
                                                    <div>
                                                        <small class="opacity-5"></small>
                                                        <span>{{ total_api_calls_count }}</span>
                                                    </div>
                                                    <div class="widget-title ml-2 opacity-5 font-size-lg text-muted">{{ $t('Total API Calls') }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="widget-chart-wrapper widget-chart-wrapper-lg opacity-10 m-0">
                                        <div id="dashboard-sparkline-carousel-3" style="min-height: 120px;">
                                            <chart1 :height="165"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-sm-12 col-lg-6">
                    <div class="mb-3 card">
                        
                        <div class="p-0 d-block card-footer">
                            <div class="grid-menu grid-menu-2col">
                                <div class="no-gutters row">
                                    <div class="p-2 col-sm-6">
                                        <button @click="projectSettings()" class="btn-icon-vertical btn-transition-text btn-transition btn-transition-alt pt-2 pb-2 btn btn-outline-dark">
                                            <i class="lnr-apartment text-dark opacity-7 btn-icon-wrapper mb-2"> </i>
                                            {{ $t('Project settings') }}
                                        </button>
                                    </div>
                                    <div class="p-2 col-sm-6">
                                        <button @click="support()" class="btn-icon-vertical btn-transition-text btn-transition btn-transition-alt pt-2 pb-2 btn btn-outline-dark">
                                            <i class="lnr-database text-dark opacity-7 btn-icon-wrapper mb-2"> </i>
                                            {{ $t('Support') }}
                                        </button>
                                    </div>
                                    <div class="p-2 col-sm-6">
                                        <button class="btn-icon-vertical btn-transition-text btn-transition btn-transition-alt pt-2 pb-2 btn btn-outline-dark">
                                            <i class="lnr-printer text-dark opacity-7 btn-icon-wrapper mb-2"> </i>
                                            {{ $t('Activities') }}
                                        </button>
                                    </div>
                                    <div class="p-2 col-sm-6">
                                        <button class="btn-icon-vertical btn-transition-text btn-transition btn-transition-alt pt-2 pb-2 btn btn-outline-dark">
                                            <i class="lnr-store text-dark opacity-7 btn-icon-wrapper mb-2"> </i>
                                            {{ $t('Marketing') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-6">
                    <div class="mb-3 card">
                        
                        <div class="p-0 d-block card-footer">
                            <div class="grid-menu grid-menu-2col">
                                <div class="no-gutters row">
                                    <div class="p-2 col-sm-6">
                                        <button @click="support()" class="btn-icon-vertical btn-transition-text btn-transition btn-transition-alt pt-2 pb-2 btn btn-outline-success">
                                            <i class="lnr-lighter text-success opacity-7 btn-icon-wrapper mb-2"> </i>
                                            {{ $t('Support') }}
                                        </button>
                                    </div>
                                    <div class="p-2 col-sm-6">
                                        <button @click="contact()" class="btn-icon-vertical btn-transition-text btn-transition btn-transition-alt pt-2 pb-2 btn btn-outline-warning">
                                            <i class="lnr-construction text-warning opacity-7 btn-icon-wrapper mb-2"> </i>
                                            {{ $t('New link') }}
                                        </button>
                                    </div>
                                    <div class="p-2 col-sm-6">
                                        <button @click="invoices()" class="btn-icon-vertical btn-transition-text btn-transition btn-transition-alt pt-2 pb-2 btn btn-outline-info">
                                            <i class="lnr-bus text-info opacity-7 btn-icon-wrapper mb-2"> </i>
                                            {{ $t('Invoices') }}
                                        </button>
                                    </div>
                                    <div class="p-2 col-sm-6">
                                        <button @click="affiliate()" class="btn-icon-vertical btn-transition-text btn-transition btn-transition-alt pt-2 pb-2 btn btn-outline-alternate">
                                            <i class="lnr-gift text-alternate opacity-7 btn-icon-wrapper mb-2"> </i>
                                            {{ $t('Affiliate') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    <div class="mb-3 card pa-3">
                        
                        <h6 class="widget-subheading mb-3">{{ $t('Message center') }}</h6>
                        <table class="table b-table b-table-stacked-md">
                            <thead>
                                <tr>
                                    <th>{{ $t('Date') }}</th>
                                    <th>{{ $t('From') }}</th>
                                    <th>{{ $t('Subject') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>

                                <tr v-for="message in messages" v-bind:key="message.message_id" style="cursor: pointer;" class="">
                                    <td>
                                        <a v-if="message.isRead == 1" :href="'/index.php/messages/view/id/' + message.message_id">
                                            {{ message.date }}
                                        </a>
                                        <a v-else :href="'/index.php/messages/view/id/' + message.message_id">
                                            <b>{{ message.date }}</b> 
                                        </a>
                                    </td>
                                    <td>
                                        <a v-if="message.isRead == 1" :href="'/index.php/messages/view/id/' + message.message_id">
                                            {{ message.message_sender }}
                                        </a>
                                        <a v-else :href="'/index.php/messages/view/id/' + message.message_id">
                                            <b>{{ message.message_sender }}</b> 
                                        </a>
                                    </td>
                                    <td>
                                        <a v-if="message.isRead == 1" :href="'/index.php/messages/view/id/' + message.message_id">
                                            {{ message.subject }}
                                        </a>
                                        <a v-else :href="'/index.php/messages/view/id/' + message.message_id">
                                            <b>{{ message.subject }}</b> 
                                        </a>
                                    </td>
                                    <td>
                                        <a :href="'/index.php/messages/delete/id/' + message.message_id" class="btn btn-warning delete-message"> Delete <i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </component>
    </div>
</template>

<script>
    import PageTitle from "../../Layout/Components/PageTitle.vue";
    import chart1 from './Analytics/chart2';
    import axios from 'axios';

    import {library} from '@fortawesome/fontawesome-svg-core';
    import {
        faTrashAlt,
        faCheck,
        faCalendarAlt,
        faAngleDown
    } from '@fortawesome/free-solid-svg-icons'
    import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';

    library.add(
        faTrashAlt,
        faCheck,
        faAngleDown,
        faCalendarAlt,
    );
    const default_layout = "default";
    export default {
        computed: {
            layout() {
                return default_layout + '-layout';
            }
        },
        components: {
            PageTitle,
            'font-awesome-icon': FontAwesomeIcon,
            chart1
        },
        data: () => ({
            heading: '',
            // subheading: 'This is an example dashboard created using build-in elements and components.',
            icon: 'pe-7s-display1 icon-gradient bg-mixed-hopes',
            total_api_calls_send_count: 0,
            total_api_calls_received_count: 0,
            total_api_calls_count: 0,
            total_products_synced_count: 0,
            total_orders_synced_count: 0,
            messages: [],
        }),

        methods: {
            get_dashboard_parameters: function() {

                let fd = new FormData();
                var obj = this;

                let current_project_id = localStorage.getItem('permission_project_id') ? localStorage.getItem('permission_project_id') : localStorage.getItem('first_project_id');

                fd.append('current_project_id', current_project_id);

                axios.post('/index.php/get-dashboard-parameters', fd)
                .then(function (response) {
                    obj.total_api_calls_send_count = response.data.total_api_calls_send_count;
                    obj.total_api_calls_received_count = response.data.total_api_calls_received_count;
                    obj.total_api_calls_count = response.data.total_api_calls_count;
                    obj.total_products_synced_count = response.data.total_products_synced_count;
                    obj.total_orders_synced_count = response.data.total_orders_synced_count;

                    for (var i = 0; i < response.data.messages.length; i++) {
                        obj.messages.push(response.data.messages[i]);
                    }
                })
            },
            projectSettings: function() {

                let domain_url = localStorage.getItem('site_url');
                let update_url = '';

                if (localStorage.getItem("permission_project_id")) {
                    update_url = domain_url + '/projects/view/id/' + localStorage.getItem("permission_project_id");
                } else {
                    update_url = domain_url + '/projects/view/id/' + localStorage.getItem("first_project_id");
                }

                window.location.href = update_url;
            },
            support: function() {

                window.location.href = 'https://apicenter.web-company.com/index.php/whmcsauth/submitticket';
            },
            contact: function() {

                window.open('https://web-company.com/nl/koppelingen-overzicht/');
            },
            invoices: function() {

                window.location.href = 'https://account.web-company.com/clientarea.php?action=invoices';
            },
            affiliate: function() {

                window.location.href = 'https://account.web-company.com/affiliates.php';
            }
        },
        created() {

            this.get_dashboard_parameters();
            this.heading = this.$i18n.t('Dashboard');
        }

    }
</script>
