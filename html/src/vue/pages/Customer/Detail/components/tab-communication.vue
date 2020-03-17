<template>
    <div>
        <communication-chat-creator kind='selectedUser' :customer="customer"/>
        <communication-chat-list :filter="{user_id: customer.user_id}"/>
    </div>
</template>

<script>
import ModalBrands from './modal-brands.vue';
import ModalCategories from './modal-categories.vue';
import CommunicationChatCreator from '../../../../components/communication-chat-creator/communication-chat-creator.vue';
import CommunicationChatList from '../../../../components/communication-chat-list/communication-chat-list.vue';

export default {
    name: 'tab-communication',
    components: {CommunicationChatList, CommunicationChatCreator, ModalCategories, ModalBrands},
    props: ['customer'],
};
</script>

<style scoped>

</style>