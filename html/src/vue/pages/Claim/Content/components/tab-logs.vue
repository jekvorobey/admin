<template>
    <div>
        <table class="table table-condensed">
            <thead>
            <tr>
                <th>Дата</th>
                <th>Пользователь</th>
                <th>Действие</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="event in history">
                <td>{{ event.created_at }}</td>
<!--                <td>{{ userRoleName(event.userRoleIds) }}</td>-->
                <td>{{ event.userName }}</td>
                <td>{{ eventMessage(event) }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    export default {
        props: {
            history: Array,
            historyMeta: {},
            roleNames: {}
        },
        methods: {
            userRoleName(userRoleIds) {
                if (userRoleIds.length < 1) {
                    return 'N/A';
                }
                return this.roleNames[userRoleIds[0]['id']];
            },
            eventMessage(event) {
                let eventTypeMeta = this.historyMeta[event.type];
                let message = eventTypeMeta.message;

                if (eventTypeMeta.fields.length < 1) {
                    return message;
                }

                let eventData;
                for (let [k,v] of Object.entries(event.data)) {
                    if (eventTypeMeta.fields.includes(k)) {
                        eventData = v;
                    }
                }
                if (eventTypeMeta.needsSemantic) {
                    eventData = eventTypeMeta.semantics[eventData];
                }
                return message = message + " " + this.wrapData(eventData);
            },
            wrapData(data) {
                return "\"" + data + "\"";
            }
        }
    }
</script>