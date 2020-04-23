<template>
    <layout-main>
        <communication-chat-list
            :filter="{unread_admin: 1}"
            :roles="roles"
        />
    </layout-main>
</template>

<script>
import CommunicationChatList from '../../../components/communication/communication-chat-list/communication-chat-list.vue';

export default {
    components: {CommunicationChatList},
    props: ['roles'],
};
</script>
