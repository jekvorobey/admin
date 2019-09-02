<template>
    <table class="values-table">
        <tbody>
            <tr v-for="(value, key) in values">
                <th>{{ names[key] }}</th>
                <td>{{ value }}</td>
            </tr>
        </tbody>
    </table>
</template>

<script>
    export default {
        props: {
            values: {},
            names: {}
        }
    }
</script>

<style>
    .values-table th {
        text-align: end;
        padding-right: 8px;
    }
    .values-table td {
        padding-left: 8px;
    }
</style>