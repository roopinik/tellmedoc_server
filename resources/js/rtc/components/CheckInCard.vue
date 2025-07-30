<template>
    <div class="row">
        <div class="col-9">
            <div class="d-block">
                <h5 class="d-inline">{{ checkIn.person.name == ""?"Person "+checkIn.last_checkin.id:checkIn.person.name }}</h5>
                <p class="m-0 p-0 d-inline"> ({{ checkIn.creator.mobile }})</p>
            </div>
            <p class="m-0 p-0"><span class="badge bg-info">Appointment: {{ checkIn.id }}</span> Appointment Date: {{
                    checkIn.appointment_date }}</p>
            <p class="m-0 p-0"><span :class="getStatusClass(checkIn.latest_checkin.checkin_status)" class="badge">{{ checkIn.latest_checkin.checkin_status == 'checkedin'?'online':'offline' }}</span>
                <span :class="getCallClass(checkIn.latest_checkin.checkin_status)" class="badge ms-2 d-inline-block">{{ checkIn.latest_checkin.call_status }}</span>
                <span class = "ms-2 d-inline-block"><b>Checked In At : {{ timeConverter(checkIn.latest_checkin.checked_in_at) }}</b></span></p>
        </div>
        <div v-if="canStart(checkIn)" class="col-3 text-center">
            <button @click="openConference()"
                class="btn btn-primary btn-lg">Start</button>
        </div>
    </div>
</template>

<script>
import moment from 'moment'

export default {
    name: 'CheckInCard',
    props: {
        checkIn: null
    },
    data(){
        return {
            role: ""
        }
    },
    mounted() {
        this.role = this.$store.state.user.userRole;
    },
    methods: {
        canStart(checkin){
           
            if(this.role == "Doctor"){
                return true;
            }

            if(this.$store.state.user.uid == checkin.patient_id && checkin.checkin_id == this.$store.state.activeCall){
                return true
            }
            else {
                return false
            }
        },
        getStatusClass(checkInStatus){
            switch (checkInStatus){
                case "checkedin":
                    return ["bg-success"]
                case "disconnected":
                    return "bg-danger"
                default:
                    return "bg-secondary"
            }
        },
        getCallClass(checkInStatus){
            switch (checkInStatus){
                case "waiting":
                    return ["bg-warning","text-dark"]
                case "completed":
                    return "bg-info"
                default:
                    return "bg-secondary"
            }
        },
        openConference(){
            this.$store.commit('setSelectedCheckin',Object.assign({},this.checkIn))
            this.$router.push({ name: 'conference'}) 
        },
        formatDate(value) {
            if (value) {
                return moment(String(value)).calendar()
            }
        },
        formatAppointmentDate(value) {
            if (value) {
                return moment(String(value)).format("LLL")
            }
        },
        timeConverter(UNIX_timestamp) {
            var a = new Date(UNIX_timestamp * 1000);
            var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            var year = a.getFullYear();
            var month = months[a.getMonth()];
            var date = a.getDate();
            var hour = a.getHours();
            var min = a.getMinutes();
            var sec = a.getSeconds();
            var time = date + ' ' + month + ' ' + year + ' ' + hour + ':' + min + ':' + sec;
            return time;
        }
    },
}


</script>

<style scoped></style>