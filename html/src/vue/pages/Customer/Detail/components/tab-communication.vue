<template>
    <div>
        <communication-chat-list :filter="{user_ids: [customer.user_id]}"
                                 :chatUsers="[{user_id: customer.user_id, email: customer.email}]"/>
    </div>
</template>

<script>
import CommunicationChatList from '../../../../components/communication/communication-chat-list/communication-chat-list.vue';

export default {
    name: 'tab-communication',
    components: {CommunicationChatList},
    props: ['customer'],
};
</script>

<style scoped>

</style>