<template>
    <layout-main>
        <communication-chat-list
            :filter="{unread_admin: 1, theme: theme}"
            :roles="roles"
        />
    </layout-main>
</template>

<script>
import CommunicationChatList from '../../../components/communication/communication-chat-list/communication-chat-list.vue';

export default {
    components: {CommunicationChatList},
    props: ['roles', 'theme'],
};
</script>
