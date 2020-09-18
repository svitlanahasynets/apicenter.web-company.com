<template>
    <div>
        <transition name="fade" mode="out-in" appear>
            <div class="vh-100">
                <b-row class="h-100 no-gutters justify-content-md-center">
                    <b-col lg="8" md="12" class="vh-100 d-flex bg-white justify-content-center align-items-center">
                        <b-col lg="9" md="10" sm="12" class="mx-auto app-login-box">
                            <div class="app-logo"/>
                            <h4 class="mb-0">
                                <span>Inloggen in APIcenter.</span>
                            </h4>
                            <div class="divider"/>
                            <div>
                                <form method="POST" action="/login/loginaction">
                                    <b-row form>
                                        <b-col md="6">
                                            <b-form-group>
                                                <Label for="exampleEmail">Email</Label>
                                                <b-form-input type="text" name="username" id="exampleEmail"
                                                              placeholder="Username"/>
                                            </b-form-group>
                                        </b-col>
                                        <b-col md="6">
                                            <b-form-group>
                                                <Label for="examplePassword">Password</Label>
                                                <b-form-input type="password" name="password" id="examplePassword"
                                                              placeholder="Password here..."/>
                                            </b-form-group>
                                        </b-col>
                                    </b-row>
                                    <b-form-checkbox name="check" id="exampleCheck">
                                        Keep me logged in
                                    </b-form-checkbox>
                                    <div class="divider"/>
                                    <div class="d-flex align-items-center">
                                        <div class="ml-auto">
                                            <a href="javascript:void(0);" class="btn-lg btn btn-link">Recover
                                                Password</a>
                                            <b-button type="submit" variant="primary" size="lg">Login to Dashboard</b-button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </b-col>
                    </b-col>
                </b-row>
            </div>
        </transition>
    </div>
</template>

<script>

    import Slick from 'vue-slick';

    export default {
        components: {
            Slick
        },
        data: () => ({

            slickOptions6: {
                dots: true,
                infinite: true,
                speed: 500,
                arrows: true,
                slidesToShow: 1,
                slidesToScroll: 1,
                fade: true,
                initialSlide: 0,
                autoplay: true,
                adaptiveHeight: true
            },

            slide: 0,
            sliding: null,
            // loginForm: {
            //     login: '',
            //     password: ''
            // }
        }),

        methods: {
            handleClick(newTab) {
                this.currentTab = newTab;
            },
            next() {
                this.$refs.slick.next();
            },

            prev() {
                this.$refs.slick.prev();
            },

            reInit() {
                this.$nextTick(() => {
                    this.$refs.slick.reSlick();
                });
            },

            onSlideStart(slide) {
                this.sliding = true
            },
            onSlideEnd(slide) {
                this.sliding = false
            },
            // login() {
            //     let data = {
            //         username: this.login,
            //         password: this.password
            //     }
            //     fetch('/login/loginaction', {
            //         method: 'post',
            //         body: JSON.stringify(data)
            //     }).then(function(response) {
            //         return response.json();
            //     }).then(function(data) {
            //         console.log(data);
            //     });
            // }
        }
    }
</script>
