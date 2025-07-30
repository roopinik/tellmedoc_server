<script>
// const socket = io("wss://192.160.0.116:3000");

export default {
    name: "conference-view",
    mounted() {
    },
    methods: {
        async synchronizeCheckins() {
           
        },

        getClass(checkin){
            var c = [];
           
            if(checkin.checkin_id == this.$store.state.activeCall)
            {
                return "active-call";
            }
            else if(this.$store.state.user.uid == checkin.patient_id){
                return "active-user"
            }
        }
    }
};
</script>

<template>
    <div class="row">
        <div class="col-lg-8 m-auto col-md-12">
            <ul class="list-group">
                <li :class="getClass(checkin)" class="list-group-item mb-3 border border-info" v-for="checkin in $store.state.checkins" :key="checkin.id">
                    <checkin-card :check-in="checkin"></checkin-card>
                </li>
            </ul>
        </div>
    </div>
</template>