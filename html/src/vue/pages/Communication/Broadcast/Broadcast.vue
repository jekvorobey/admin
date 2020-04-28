<template>
    <layout-main>
        <communication-chat-creator :roles="roles"/>
    </layout-main>
</template>

<script>
import CommunicationChatCreator from '../../../components/communication/communication-chat-creator/communication-chat-creator.vue';

export default {
    components: {CommunicationChatCreator},
    props: ['roles'],
};
</script>
