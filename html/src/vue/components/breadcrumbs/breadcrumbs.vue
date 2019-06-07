<template>
    <ul class="breadcrumbs">
        <li v-for="(item, index) in items" class="breadcrumbs__item" :class="{ active: index === items.length - 1 }">
            <a :href="item.url">{{ item.title }}</a>
        </li>
    </ul>
</template>

<script>

export default {
    name: 'breadcrumbs',
    props: {
        items: {
            type: Array,
        },
    },
    data() {
        return {};
    },
};
</script>
