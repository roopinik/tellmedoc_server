import AgoraRTC from "agora-rtc-sdk-ng";
import EventEmitter from "events";
import store from "../../store"

var localTracks = {
  videoTrack: null,
  audioTrack: null,
};

export default class RTCCLient {
  isMicMuted = false;
  isCameraMuted = false;
  client;

  async init() {

    this.currentCamera = null;
    this.cams = null;
    this.currentMic = null;
    this.mics = null;
    var m = await initMic();
    if (m != null) {
      this.currentMic = m[0];
      this.mics = m[1];
    }
    var c = await initCamera();
    if (c != null) {
      this.currentCamera = c[0];
      this.cams = c[1];
    }
    this.client = AgoraRTC.createClient({
      mode: "rtc",
      codec: "vp9",
    });
    if (localTracks.videoTrack != null)
      localTracks.videoTrack.play("join-form-vplayer");
  }
  videoProfiles = [
    {
      label: "360p_8",
      detail: "480×360, 30fps, 490Kbps",
      value: "360p_8",
    },
    {
      label: "720p_2",
      detail: "1280×720, 30fps, 2000Kbps",
      value: "720p_2",
    },
    {
      label: "1080p_1",
      detail: "1920×1080, 15fps, 2080Kbps",
      value: "1080p_1",
    },
  ];
  curVideoProfile;
  currentMic;
  mics;
  currentCamera;
  cams;
  remoteUsers = {};
  async switchCamera(label) {
    this.currentCam = this.cams.find((cam) => cam.label === label);
    await localTracks.videoTrack.setDevice(this.currentCam.deviceId);
  }

  async switchMicrophone(label) {
    this.currentMic = this.mics.find((mic) => mic.label === label);
    await localTracks.audioTrack.setDevice(this.currentMic.deviceId);
  }

  async setMicEnabled(flag) {
    this.isMicMuted = flag;
    localTracks.audioTrack.setEnabled(this.isMicMuted);
  }

  async setVideoEnabled(flag) {
    this.isCameraMuted = flag;
    localTracks.videoTrack.setEnabled(this.isCameraMuted);
  }

  async join(token) {
    setOnCameraMicChanged();
    this.client.on("user-published", this.handleUserPublished);
    this.client.on("user-unpublished", this.handleUserUnpublished);
    var userId = await this.client.join(
      store.state.callSettings.appid,
      store.state.selectedCheckin.join_channel,
      token || null,
      store.state.callSettings.userid || null
    );
    store.commit("updateCallUserId", userId)
    var t = [];
    if (localTracks.videoTrack != null) {
      t.push(localTracks.videoTrack);
      localTracks.videoTrack.play("local-player");
    }
    if (localTracks.audioTrack != null) {
      t.push(localTracks.audioTrack);
    }
    var res = await this.client.publish(t);
  }
  async leave() {
    for (var trackName in localTracks) {
      var track = localTracks[trackName];
      if (track) {
        track.stop();
        track.close();
        localTracks[trackName] = undefined;
      }
    }
    await this.client.leave();
  }
  async subscribe(user, mediaType) {
    const userid = user.userid;
    if (user.userid === store.state.callSettings.userid) {
      return;
    }
    // subscribe to a remote user
    await this.client.subscribe(user, mediaType);
    if (mediaType === "video") {
      user.videoTrack.play("remote-player");
    }
    if (mediaType === "audio") {
      user.audioTrack.play();
    }
  }
  async handleUserPublished(user, mediaType) {
    const id = user.userid;
    this.remoteUsers[id] = user;
    await window.rtcClient.subscribe(user, mediaType);
  }
  handleUserUnpublished() {}
}

function setOnCameraMicChanged() {
  AgoraRTC.onMicrophoneChanged = async (changedDevice) => {
    // When plugging in a device, switch to a device that is newly plugged in.
    if (changedDevice.state === "ACTIVE") {
      localTracks.audioTrack.setDevice(changedDevice.device.deviceId);
      // Switch to an existing device when the current device is unplugged.
    } else if (
      changedDevice.device.label === localTracks.audioTrack.getTrackLabel()
    ) {
      const oldMicrophones = await AgoraRTC.getMicrophones();
      oldMicrophones[0] &&
        localTracks.audioTrack.setDevice(oldMicrophones[0].deviceId);
    }
  };
  AgoraRTC.onCameraChanged = async (changedDevice) => {
    // When plugging in a device, switch to a device that is newly plugged in.
    if (changedDevice.state === "ACTIVE") {
      localTracks.videoTrack.setDevice(changedDevice.device.deviceId);
      // Switch to an existing device when the current device is unplugged.
    } else if (
      changedDevice.device.label === localTracks.videoTrack.getTrackLabel()
    ) {
      const oldCameras = await AgoraRTC.getCameras();
      oldCameras[0] && localTracks.videoTrack.setDevice(oldCameras[0].deviceId);
    }
  };
}

async function initMic() {
  var mics = await AgoraRTC.getMicrophones();
  if (mics.length == 0) {
    return null;
  }
  if (!localTracks.audioTrack) {
    localTracks.audioTrack = await AgoraRTC.createMicrophoneAudioTrack({
      encoderConfig: "music_standard",
    });
  }
  const audioTrackLabel = localTracks.audioTrack.getTrackLabel();
  var currentMic = mics.find((item) => item.label === audioTrackLabel);
  return [currentMic, mics];
}

async function initCamera() {
  var cams = await AgoraRTC.getCameras();
  if (cams.length == 0) {
    return null;
  }
  if (!localTracks.videoTrack) {
    localTracks.videoTrack = await AgoraRTC.createCameraVideoTrack({
      encoderConfig: "720p_2",
    });
  }
  const videoTrackLabel = localTracks.videoTrack.getTrackLabel();
  var currentCam = cams.find((item) => item.label === videoTrackLabel);
  return [currentCam, cams];
}
