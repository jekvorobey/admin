<template>
    <div v-if="promoSegments.all">
        <small class="text-muted">Всем</small>
    </div>
    <div v-else>
        <small class="text-muted" v-if="promoSegments.brand">Бренд<br></small>
        <small class="text-muted" v-if="promoSegments.category">Категория<br></small>
        <small v-for="level in promoSegments.levels" class="text-muted">
            Уровень: {{ levelName(level) }}
            <br>
        </small>
        <small v-for="activity in promoSegments.activities" class="text-muted">
            Профиль: {{ activityName(activity) }}
            <br>
        </small>
        <small v-if="promoSegments.user_ids" class="text-muted">
            Пользователи: <span>{{ userList }}</span>
        </small>
    </div>
</template>

<script>
    export default {
        name: 'segments-list',
        props: [
            'segments',
            'activities',
            'ref_levels',
        ],
        methods: {
            activityName(activityId) {
                return this.activitiesById[activityId].name || 'N/A';
            },
            levelName(levelId) {
                return this.levelsById[levelId].name || 'N/A';
            },
        },
        computed: {
            promoSegments() {
                let mappedSegments = Object.entries(JSON.parse(this.segments)).map((value) => {
                    let [k, v] = value;
                    if (v === 'true') return [k, true];
                    if (v === 'false') return [k, false];
                    return value;
                });
                return Object.fromEntries(mappedSegments);
            },
            activitiesById() {
                let activitiesById = [
                    {name: 'N/A'}
                ];
                this.activities.forEach((item) => {
                    activitiesById[item.id] = item;
                });
                return activitiesById;
            },
            levelsById() {
                let levelsById = [
                    {name: 'N/A'}
                ];
                this.ref_levels.forEach((item) => {
                    levelsById[item.id] = item;
                });
                return levelsById;
            },
            userList() {
                if (!this.promoSegments.user_ids) return null;
                let listedUsers = this.promoSegments.user_ids.map(id => '#' + id);
                return listedUsers.join(', ');
            }
        },
    };
</script>

<style scoped>

</style>