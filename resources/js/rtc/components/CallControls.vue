<template>
  <div class="call-controls text-center w-100">
    <div @click="toggleMuteMic()" :class="['control-button', isMicMuted ? 'bg-green' : 'bg-grey']">
      <i class="fa bi bi-mic-mute" aria-hidden="true"></i>
    </div>
    <div @click="endCall()" class="control-button bg-red">
      <i class="bi bi-telephone" aria-hidden="true"></i>
    </div>
    <div @click="toggleMuteVideo()" :class="['control-button', isCameraMuted ? 'bg-green' : 'bg-grey']">
      <i class="bi bi-camera-video-off"></i>
    </div>

    <div>
      <div data-bs-toggle="modal" data-bs-target="#join-form-modal" id="join" class="control-button bg-grey">
        <i class="bi bi-sliders2" aria-hidden="true"></i>
      </div>
    </div>
  </div>
  <div class="modal fade" id="join-form-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Settings</h5>
          <button type="button" class="btn-close" @click="closeModal()" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-12" v-show="!isConnected">
              <div id="join-form-vplayer"></div>
            </div>
            <div class="col-12 join-form-controls d-flex justify-content-center mt-2">
              <div v-show="!isConnected" @click="toggleMuteMic()" :class="[
                'control-button-sm',
                isMicMuted ? 'bg-green' : 'bg-grey',
              ]">
                <i class="fa bi bi-mic-mute" aria-hidden="true"></i>
              </div>
              <div v-show="!isConnected" @click="toggleMuteVideo()" :class="[
                'control-button-sm',
                isCameraMuted ? 'bg-green' : 'bg-grey',
              ]">
                <i class="bi bi-camera-video-off"></i>
              </div>
            </div>

            <div class="col-12 text-start mt-2">
              <label>Camera</label>
              <select class="form-select" aria-label="Default select example" @change="changeCamera($event)">
                <option v-for="cam in cams" :key="cam.deviceId">
                  {{ cam.label }}
                </option>
              </select>
            </div>
            <div class="col-12 text-start mt-2">
              <label>Microphone</label>
              <select class="form-select" aria-label="Default select example" @change="changeMic($event)">
                <option v-for="device in mics" :key="device.deviceId">
                  {{ device.label }}
                </option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer" v-show="!isConnected">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
            Leave Meeting
          </button>
          <button type="button" :disabled="buttonsDisabled" class="btn btn-success" @click="joinMeeting()">
            Join
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
  
<script>
import { Modal } from "bootstrap";

export default {
  name: "call-controls",
  props: {
    // isOpened: { type: Boolean, default: false },
    msg: String,
  },
  data() {
    return {
      isMicMuted: false,
      isCameraMuted: false,
      cams: this.$rtcClient.cams,
      mics: this.$rtcClient.mics,
      buttonsDisabled: true,
      isConnected: false,
      channel: null
    };
  },
  mounted() {
    var s = this;
    this.$rtcClient.init().then((r) => {
      s.buttonsDisabled = false;
      s.cams = this.$rtcClient.cams;
      s.mics = this.$rtcClient.mics;
    });
    this.channel = this.$route.params["channel"];
    this.joinFormModal = new Modal(document.getElementById("join-form-modal"));
    this.joinFormModal.show();
  },
  methods: {
    toggleMuteMic() {
      this.isMicMuted = !this.isMicMuted;
      this.$rtcClient.setMicEnabled(!this.isMicMuted);
    },
    toggleMuteVideo() {
      this.isCameraMuted = !this.isCameraMuted;
      this.$rtcClient.setVideoEnabled(!this.isCameraMuted);
    },
    changeCamera(event) {
      this.$rtcClient.switchCamera(event.target.value);
    },
    changeMic(event) {
      this.$rtcClient.switchMicrophone(event.target.value);
    },
    toggleSpeakerPhone() {

    },
    joinMeeting() {
      this.isConnected = true;
      this.joinFormModal.hide();
      this.$store.dispatch('startCall')
    },
    closeModal() {
      this.joinFormModal.hide();
    },
    async endCall() {
      this.$store.dispatch('leaveCall')
      this.$router.push({ name: 'queue' });
    },
  },
};
</script>
  
  <!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
h3 {
  margin: 40px 0 0;
}

ul {
  list-style-type: none;
  padding: 0;
}

li {
  display: inline-block;
  margin: 0 10px;
}

a {
  color: #42b983;
}
</style>
  