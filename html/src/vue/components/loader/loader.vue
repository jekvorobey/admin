<template>
    <v-svg class="loader" name="refresh" />
</template>

<script>
import VSvg from '../controls/VSvg/VSvg.vue';

import '../../../images/sprite/refresh.svg';
import './loader.css';

export default {
    name: 'loader',
    components: {
        VSvg,
    },
};
</script>