<template>
  <!-- <div class="links">
    <router-link to="/">
      home
    </router-link>
    <router-link to="/video-conference">
      conference
    </router-link>
    <router-link to="/prescriptions">
      Prescriptions
    </router-link>
  </div> -->
  <div class="container-fluid pt-3">
    <div class="row">
      <div class="col-12 text-center">
        <p>
          <span class="fs-4 fw-bold">
            Sanjeevini Hospital - Dr. Anushka
          </span>
          <span class="d-block text-right">Jan 09, 2024</span>
        </p>
      </div>
    </div>
    <router-view></router-view>
    <div v-if="canShowCallToast()" class="row">
      <div class="p-3 active-user d-flex flex-row justify-content-between call-toast" role="alert">
      <h5>Incoming call from doctor</h5> <button @click="openConference" class="btn btn-success w-25 mb-2">Start</button>
      </div>
    </div>
  </div>
</template>

<script>

export default {
  name: 'App',
  components: {
  },
  methods:{
    openConference(){
      this.$router.push({ name: 'conference'}) 
    },
    canShowCallToast(){
      if(this.$route.name === 'conference'){
        return false
      }
      if(this.$store.state.user.userRole == 'Patient' &&  this.$store.state.selectedCheckin?.checkin_id == this.$store.state.activeCallId)
      {
        return true
      }
      return false
    }
  }
}
</script>

<style></style>
