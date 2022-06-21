<template>
    <div :style="{
        color: levels[level]
    }">
        <b-icon-arrow-up-right v-if="differenceCoefficient >= 0" />
        <b-icon-arrow-down-right v-else />

        {{ differencePercent }}%
    </div>
</template>

<script>
import { BIcon, BIconArrowDownRight, BIconArrowUpRight } from 'bootstrap-vue';
import _floor from 'lodash/floor';

export default {
    name: 'value-difference',

    components: {
        BIcon,
        BIconArrowDownRight,
        BIconArrowUpRight,
    },

    props: {
        oldValue: {
            type: Number,
            require: true,
        },

        newValue: {
            type: Number,
            require: true,
        },

        levels: {
            type: Object,
            default() {
                return {
                    0: '#43a047',
                    0.1: '#fdd835',
                    0.3: '#fb8c00',
                    0.5: '#f4511e',
                };
            }
        },
    },

    computed: {
        differenceCoefficient() {
            return (this.newValue - this.oldValue) / this.oldValue;
        },

        differencePercent() {
            return Math.abs(_floor(this.differenceCoefficient * 100));
        },

        level() {
            let level = 0;

            for (const threshold in this.levels) {
                if (Math.abs(this.differenceCoefficient) > threshold) {
                    level = threshold;
                }
            }

            return level;
        }
    },
};
</script>
