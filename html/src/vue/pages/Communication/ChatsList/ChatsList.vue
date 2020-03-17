<template>
    <layout-main>
        <communication-chat-list
            :filter="{unread_admin: 1}"
        />
        <br>
        <communication-chat-creator/>
    </layout-main>
</template>

<script>
import CommunicationChatCreator from '../../../components/communication-chat-creator/communication-chat-creator.vue';
import CommunicationChatList from '../../../components/communication-chat-list/communication-chat-list.vue';

export default {
    components: {CommunicationChatList, CommunicationChatCreator},
};
</script>
