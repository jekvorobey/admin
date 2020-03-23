<template>
    <div>
        <communication-chat-creator kind='selectedUser'
                                    :customer="customer"
                                    :channels = "channels"
                                    :themes = "themes"
                                    :statuses = "statuses"
                                    :types = "types"/>
        <communication-chat-list :filter="{user_id: customer.user_id}"
                                 :customer="customer"
                                 :channels = "channels"
                                 :themes = "themes"
                                 :statuses = "statuses"
                                 :types = "types"/>
    </div>
</template>

<script>
import ModalBrands from './modal-brands.vue';
import ModalCategories from './modal-categories.vue';
import CommunicationChatCreator from '../../../../components/communication-chat-creator/communication-chat-creator.vue';
import CommunicationChatList from '../../../../components/communication-chat-list/communication-chat-list.vue';
import Services from "../../../../../scripts/services/services";

export default {
    name: 'tab-communication',
    components: {CommunicationChatList, CommunicationChatCreator, ModalCategories, ModalBrands},
    props: ['customer'],
    data() {
        return {
            channels: {},
            themes: {},
            statuses: {},
            types: {},
        }
    },
    created() {
        Services.net().get(this.getRoute('communications.chats.directories')).then(data => {
            this.channels = data.channels;
            this.themes = data.themes;
            this.statuses = data.statuses;
            this.types = data.types;
        }).finally(() => {
            Services.hideLoader();
        });
    },
};
</script>

<style scoped>

</style>