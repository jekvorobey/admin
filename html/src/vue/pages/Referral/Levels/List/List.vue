<template>
    <layout-main>
        <select v-model="level_id" @change="loadLevel" class="form-control">
            <option :value="null">Выберите уровень</option>
            <option v-for="level in levels" :value="level.id">{{ level.name }}</option>
        </select>
        <div v-if="level">

        </div>
    </layout-main>
</template>

<script>

import Services from '../../../../../scripts/services/services.js';

export default {
    props: ['levels'],
    data() {
        return {
            level_id: null,
            level: null,
        };
    },
    methods: {
        loadLevel() {
            this.level = null;
            Services.showLoader();
            Services.net().post()
        }
    }
};
</script>
