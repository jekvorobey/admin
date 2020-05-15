<template>
    <div>
        <communication-chat-list :filter="{user_ids: userIds}"
                                 :usersProp="operators"/>
    </div>
</template>

<script>
    import CommunicationChatList from '../../../../components/communication/communication-chat-list/communication-chat-list.vue';

    export default {
        name: 'tab-communication',
        props: ['operators'],
        components: {CommunicationChatList},
        data() {
            let userIds = [];
            Object.values(this.operators).forEach(operator => {
                userIds.push(operator.id);
            });

            return {
                userIds: userIds,
            }
        },
    };
</script>