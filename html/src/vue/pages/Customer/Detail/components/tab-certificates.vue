<template>
    <div>
        <h2>Сертификаты</h2>
        <span class="subheader">купленные или активированные пользователем</span>
        <card-list
            :records="records"
            :external-filter="{customer_or_recipient_id: id}"
            @data="onDataGet"
        />
    </div>
</template>

<script>
import CardList from '../../../Marketing/Certificate/components/tab-cards.vue';
export default {
    name: 'tab-certificates',
    props: ['id'],
    components: {CardList},
    data () {
        return {
            records: null
        }
    },
    methods: {
        onDataGet(data) {
            this.records = data
        }
    }
};
</script>

<style scoped>
h2 {
    margin-bottom: 0;
}
.subheader {
    color: #666;
}
</style>
