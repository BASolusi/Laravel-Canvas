<template>
    <div>
        <page-header>
            <template slot="action">
                <router-link
                    :to="{ name: 'create-post' }"
                    class="btn btn-sm btn-outline-success font-weight-bold my-auto"
                    >{{ i18n.new_post }}
                </router-link>
            </template>
        </page-header>

        <main class="py-4">
            <div class="col-xl-8 offset-xl-2 col-lg-10 offset-lg-1 col-md-12">
                <div class="my-3">
                    <h1>{{ i18n.stats }}</h1>
                    <p class="text-secondary">{{ i18n.click_to_see_insights }}</p>
                </div>

                <div v-if="isReady">
                    <div v-if="posts.length">
                        <div class="card-deck mt-5">
                            <div class="card shadow" :class="borderColor">
                                <div
                                    class="card-header pb-0 bg-transparent d-flex justify-content-between align-middle border-0"
                                >
                                    <p class="font-weight-bold text-muted small text-uppercase">{{ i18n.views }}</p>
                                    <p>
                                        <span class="badge badge-pill badge-success p-2 font-weight-bold">
                                            {{ i18n.last_thirty_days }}
                                        </span>
                                    </p>
                                </div>
                                <div class="card-body pt-0 pb-2">
                                    <p class="card-text display-4">{{ suffixedNumber(viewCount) }}</p>
                                </div>
                            </div>
                            <div class="card shadow" :class="borderColor">
                                <div
                                    class="card-header pb-0 bg-transparent d-flex justify-content-between align-middle border-0"
                                >
                                    <p class="font-weight-bold text-muted small text-uppercase">{{ i18n.visitors }}</p>
                                    <p>
                                        <span class="badge badge-pill badge-primary p-2 font-weight-bold">{{
                                            i18n.last_thirty_days
                                        }}</span>
                                    </p>
                                </div>
                                <div class="card-body pt-0 pb-2">
                                    <p class="card-text display-4">{{ suffixedNumber(visitCount) }}</p>
                                </div>
                            </div>
                        </div>

                        <line-chart :views="JSON.parse(viewTrend)" :visits="JSON.parse(visitTrend)" class="mt-5" />

                        <div class="mt-5 card shadow" :class="borderColor">
                            <div class="card-body p-0">
                                <div v-for="(post, index) in posts" :key="index">
                                    <router-link
                                        :to="{
                                            name: 'post-stats',
                                            params: { id: post.id },
                                        }"
                                        class="text-decoration-none"
                                    >
                                        <div
                                            v-hover="{ class: `row-hover` }"
                                            class="d-flex p-3 align-items-center"
                                            :class="{
                                                'border-top': index !== 0,
                                                'rounded-top': index === 0,
                                                'rounded-bottom': index === posts.length - 1,
                                            }"
                                        >
                                            <div class="mr-auto pl-2">
                                                <p class="mb-1 mt-2">
                                                    <span class="font-weight-bold text-lg lead">{{
                                                        trim(post.title, 45)
                                                    }}</span>
                                                </p>
                                                <p class="text-secondary mb-2">
                                                    {{ post.read_time }} ―
                                                    {{ i18n.published }}
                                                    {{ moment(post.published_at).format('MMM D, YYYY') }}
                                                </p>
                                            </div>
                                            <div class="ml-auto d-none d-lg-block">
                                                <span class="text-muted mr-3"
                                                    >{{ suffixedNumber(post.views_count) }} {{ i18n.views }}</span
                                                >
                                                <span class="mr-3"
                                                    >{{ i18n.created }}
                                                    {{ moment(post.created_at).format('MMM D, YYYY') }}</span
                                                >
                                            </div>

                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="25"
                                                viewBox="0 0 24 24"
                                                class="icon-cheveron-right-circle"
                                            >
                                                <circle cx="12" cy="12" r="10" style="fill: none;" />
                                                <path
                                                    class="primary"
                                                    d="M10.3 8.7a1 1 0 0 1 1.4-1.4l4 4a1 1 0 0 1 0 1.4l-4 4a1 1 0 0 1-1.4-1.4l3.29-3.3-3.3-3.3z"
                                                />
                                            </svg>
                                        </div>
                                    </router-link>
                                </div>

                                <infinite-loading @infinite="fetchPosts" spinner="spiral">
                                    <span slot="no-more"></span>
                                    <div slot="no-results"></div>
                                </infinite-loading>
                            </div>
                        </div>
                    </div>

                    <div v-else class="card shadow mt-5" :class="borderColor">
                        <div class="card-body p-0">
                            <div class="my-5">
                                <p class="lead text-center text-muted mt-5">{{ i18n.you_have_no_published_posts }}</p>
                                <p class="lead text-center text-muted mt-1">{{ i18n.stats_are_made_available }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script>
import NProgress from 'nprogress';
import isEmpty from 'lodash/isEmpty';
import InfiniteLoading from 'vue-infinite-loading';
import Hover from '../directives/Hover';
import LineChart from '../components/LineChart';
import strings from '../mixins/strings';
import PageHeader from '../components/PageHeader';
import i18n from '../mixins/i18n';
import store from '../store';

export default {
    name: 'all-stats',

    components: {
        LineChart,
        InfiniteLoading,
        PageHeader,
    },

    mixins: [strings, i18n],

    directives: {
        Hover,
    },

    data() {
        return {
            page: 1,
            posts: [],
            viewCount: 0,
            viewTrend: {},
            visitCount: 0,
            visitTrend: {},
            isReady: false,
        };
    },

    async created() {
        await this.fetchStats();
        await this.fetchPosts();

        NProgress.done();
        this.isReady = true;
    },

    computed: {
        borderColor() {
            return store.state.user.darkMode ? 'border-0' : '';
        },
    },

    methods: {
        fetchStats() {
            return this.request()
                .get('/api/stats')
                .then((response) => {
                    this.viewCount = response.data.view_count;
                    this.viewTrend = response.data.view_trend;
                    this.visitCount = response.data.visit_count;
                    this.visitTrend = response.data.visit_trend;

                    NProgress.inc();
                })
                .catch(() => {
                    NProgress.done();
                });
        },

        fetchPosts($state) {
            return this.request()
                .get('/api/posts', {
                    params: {
                        page: this.page,
                        type: 'published',
                    },
                })
                .then((response) => {
                    if (!isEmpty(response.data) && !isEmpty(response.data.posts.data)) {
                        this.page += 1;
                        this.posts.push(...response.data.posts.data);

                        $state.loaded();
                    } else {
                        $state.complete();
                    }

                    if (isEmpty($state)) {
                        NProgress.inc();
                    }
                })
                .catch(() => {
                    NProgress.done();
                });
        },
    },
};
</script>

<style lang="scss" scoped>
@import '../../sass/utilities/variables';

.badge-success {
    background-color: $green-500;
    color: darken($green, 20%);
}

.badge-primary {
    background-color: $blue-500;
    color: darken($blue, 35%);
}
</style>