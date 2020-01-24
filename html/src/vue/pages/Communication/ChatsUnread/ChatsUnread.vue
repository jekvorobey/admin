<template>
    <layout-main>
        <communication-chat-list
            :channels="channels"
            :statuses="statuses"
            :types="types"
            :filter="{unread: true}"
        />
    </layout-main>
</template>

<script>
import CommunicationChatList from '../../../components/communication-chat-list/communication-chat-list.vue';

export default {
    components: {CommunicationChatList},
    props: {
        channels: Object,
        statuses: Object,
        types: Object,
    },
};
</script>
