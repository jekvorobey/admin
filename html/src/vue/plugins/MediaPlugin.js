import Vue from 'vue';
import Variables from '../../scripts/variables';

const MQ = 'GREENSIGHT_MEDIA_MQ';
const MQMAP = 'GREENSIGHT_MEDIA_MQ_MAP';

const MediaPlugin = vue => {
    Object.defineProperty(Vue.prototype, '$mq', {
        get() {
            return this[MQ];
        },
    });

    vue.mixin({
        beforeCreate() {
            const isRoot = this === this.$root;
            const inherited = this.$parent && this.$parent[MQ];

            if (isRoot && this.$options.mq) {
                const mergedKeys = Object.keys(this.$options.mq);
                const observed = mergedKeys.reduce((obs, k) => {
                    obs[k] = false;
                    return obs;
                }, {});

                Object.defineProperty(observed, 'all', {
                    enumerable: true,
                    configurable: true,
                    get() {
                        return Object.keys(this)
                            .filter(k => k !== 'all')
                            .filter(k => this[k]);
                    },
                });

                Vue.util.defineReactive(this, MQ, observed);
            } else if (inherited) Vue.util.defineReactive(this, MQ, this.$parent[MQ]);
        },
        beforeMount() {
            const isRoot = this === this.$root;
            const inherited = this.$parent && this.$parent[MQ];

            if (isRoot && this.$options.mq) {
                this[MQMAP] = {};

                const mergedKeys = Object.keys(this.$options.mq);
                mergedKeys.forEach(k => {
                    const ownQuery = this.$options.mq[k];
                    const mql = ownQuery ? window.matchMedia(ownQuery) : inherited[k];
                    const handler = e => {
                        this[MQ][k] = e.matches;
                    };
                    mql.addListener(handler);

                    this[MQ][k] = mql.matches;
                    this[MQMAP][k] = { mql, handler };
                });
            }
        },
        beforeDestroy() {
            const isRoot = this === this.$root;
            const inherited = this.$parent && this.$parent[MQ];

            if (isRoot && this.$options.mq) {
                Object.keys(this[MQMAP]).forEach(key => {
                    const map = this[MQMAP][key];
                    map.mql.removeListener(map.handler);
                    map.mql = null;
                    map.handler = null;
                });
                this[MQMAP] = null;
                this[MQ] = null;
            } else if (inherited) this[MQ] = null;
        },
    });
};

Vue.use(MediaPlugin);

export default {
    mobile: `(max-width: ${Variables.breakpoints.mobile - 1}px)`,
    mobileLg: `(max-width: ${Variables.breakpoints.mobileLg - 1}px)`,
    tablet: `(max-width: ${Variables.breakpoints.tablet - 1}px)`,
    tabletLg: `(max-width: ${Variables.breakpoints.tabletLg - 1}px)`,
    desktop: `(max-width: ${Variables.breakpoints.desktop - 1}px)`,
    desktopLg: `(max-width: ${Variables.breakpoints.desktopLg - 1}px)`,
    desktopXl: `(max-width: ${Variables.breakpoints.desktopXl - 1}px)`,
};
