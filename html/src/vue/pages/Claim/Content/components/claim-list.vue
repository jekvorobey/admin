<template>
    <div class="col-12" style="max-height:300px;overflow:auto;padding:10px;border:1px solid #ccc">
        <span>{{ claimsList }}</span>
    </div>
</template>

<script>
    export default {
        props: {
            claims: Array,
        },
        computed: {
            claimsList() {
                let listedClaims = this.claims.map(claim => '#' + claim.id);
                return listedClaims.join(', ');
            }
        }
    }
</script>
