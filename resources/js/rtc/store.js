import { createApp } from 'vue'
import { createStore } from 'vuex'

// Create a new store instance.
const store = createStore({
  state :{
    checkins:[],
    appointment:{},
    callSettings:{},
    user:{},
    activeCallId:null
  },
  mutations: {
    addCheckIns (state, data) {
      state.checkins = data;
    },
    setSelectedCheckin(state, data) {
      state.selectedCheckin = data
    },
    setActiveCall(state, data) {
      state.activeCallId = data
    },
    setAppointment (state, data) {
      state.appointment = Object.assign(state.appointment, data)
    },
    setUser (state, data) {
      state.user = Object.assign(state.user, data)
    },
    updateCallUserId(state, data) {
      state.callSettings.userId = data
    },
    updateCallSettings (state, data) {
      state.callSettings = Object.assign(state.callSettings, data)
    }

  },
  actions: {
    async refreshCheckins (context) {
      var url = ""
      if (window.userRole == "Doctor")
          url = window.baseUrl + "api/user/get/dotor/checkins";
      else
          url = window.baseUrl + "api/user/get/checkins/" + window.doctorId
      var results = await fetch(url)
      var data = (await results.json()).map((checkin) => {
          checkin["join_channel"] = checkin.doctor + "_" + checkin.latest_checkin.id
          if(context.state.user.uid == checkin.created_by){
              context.commit("setSelectedCheckin", Object.assign({},checkin))
          }
          return checkin;
      });
      context.commit('addCheckIns', data)
    },
    async refreshAppointment (context) {
      var data = {};
      data.appointmentId = window.appointmentId;
      data.token = window.token;
      data.userToken = window.userToken;
      data.channel = window.channel;
      data.userName = window.userName;
      data.userRole = window.userRole;
      data.uid = window.uid;
      context.commit('setAppointment', data)
    },
    async setUserInfo (context) {
      var data = {};
      data.token = window.token;
      data.userName = window.userName;
      data.userRole = window.userRole;
      data.userToken = window.userToken;
      // if(data.userRole == "Doctor" )
      // data.userToken = null;
      data.uid = window.uid;
      context.commit('setUser', data)
    },
    async initCallSettings(context){
      context.commit('updateCallSettings', {
        appid: window.appId,
        token: context.state.user.token,
        userid: context.state.user.uid,
      });
    },

    async startCall(context){
      var token = await fetchCallToken(context.state.selectedCheckin.checkin_id, context.state.user.userToken)
      window.rtcClient.join(token);
      if (context.state.user.userRole == "Doctor") {
        window.socket.emit("call_started",context.state.selectedCheckin.checkin_id)
      }
    },

    async leaveCall(context){
      window.rtcClient.leave();
      if (context.state.user.userRole == "Doctor") {
        window.socket.emit("call_ended",context.state.selectedCheckin.checkin_id)
      }
    },

    async onUserConnected(context, data){
      context.dispatch("refreshCheckins")
      context.commit("setActiveCall",data.activeCall)
    },

    async onUserDisconnected(context, data){
      context.dispatch("refreshCheckins")
    },

    async onCallStarted(context, checkinId){
      context.commit("setActiveCall", checkinId)
    },

    async onCallEnded(context, checkinId){
      context.commit("setActiveCall",null)
    },

  }

})

async function fetchCallToken(checkinId, userToken){
  var url = window.baseUrl + `/apiv1/get/calltoken/${checkinId}?_format=json`;
  var options = {
    method: "GET",
  }
  if(userToken!=null)
  options["headers"] = {
    "X-Access-Auth-Token": userToken,
  };
  var response = await fetch(
    url,options
  )
  var data = await response.json();
  return data
}

export default store;