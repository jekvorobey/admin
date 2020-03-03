<template>
    <div>
        <div class="card">
            <div class="card-body">
                <b-form @submit.prevent="">
                    <b-row>
                        <b-col cols="3">
                            <label for="chat-theme">Тема</label>
                        </b-col>
                        <b-col cols="4">
                            <b-form-input id="chat-theme" v-model="form.theme" placeholder="Введите тему чата"/>
                        </b-col>
                    </b-row>

                    <b-row>
                        <b-col cols="3">
                            <label for="chat-status">Статус</label>
                        </b-col>
                        <b-col cols="4">
                            <b-form-select v-model="form.status_id" id="chat-status">
                                <b-form-select-option :value="null">Все</b-form-select-option>
                                <b-form-select-option :value="status.id" v-for="status in availableStatuses" :key="status.id">
                                    {{ status.name }}
                                    <template v-if="status.channel_id">
                                        (
                                        {{ channels[status.channel_id].name }}
                                        )
                                    </template>
                                </b-form-select-option>
                            </b-form-select>
                        </b-col>
                    </b-row>

                    <b-button type="submit" variant="dark">Добавить чат</b-button>
                </b-form>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    name: 'communication-chat-creator',
    data() {
        return {
            form: {
                theme: '',
            }
        }
    },
    computed: {
        ...mapGetters(['getRoute']),
        availableStatuses() {
            return Object.values(this.statuses).filter(status => {
                return !this.form.channel_id ||
                    !status.channel_id ||
                    Number(status.channel_id) === Number(this.form.channel_id);
            })
        }
    }
};
</script>

<style scoped>

</style>
