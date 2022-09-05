<template>
    <layout-main>
        <communication-chat-list
            :filter="{theme: theme}"
            :defaultCustomFilter="2"
            :roles="roles"
            :merchants="merchants"
        />
    </layout-main>
</template>

<script>
import CommunicationChatList from '../../../components/communication/communication-chat-list/communication-chat-list.vue';

export default {
    components: {CommunicationChatList},
    props: ['roles', 'theme', 'merchants'],
};
</script>
