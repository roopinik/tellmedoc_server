import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import store from "./store";
import RTCCLient from "./assets/js/agora-main.js";
import "./style.css";

import CallControls from "./components/CallControls.vue";
import CheckInCard from "./components/CheckInCard.vue";
import "bootstrap";
import "bootstrap/dist/css/bootstrap.min.css";
import io from "socket.io-client";

const app = createApp(App);
var rtcClient = new RTCCLient();
app.config.globalProperties.$rtcClient = rtcClient;
window.rtcClient = rtcClient;


var token = window.userToken;

var socket = io(window.socketUrl, {
  query: `token=${token}&appointment_id=${window.appointmentId}`,
});

socket.connect();

socket.on("user_connected", function(data){
  store.dispatch("onUserConnected",data)
})

socket.on("user_disconnected", function(data){
  store.dispatch("onUserDisconnected",data)
})

socket.on("call_started", function(data){
  store.dispatch("onCallStarted",data)
})

socket.on("call_ended", function(data){
  store.dispatch("onCallEnded",data)
})
// socket.on("connect", function(data){
// })

// socket.on("disconnect", function(){
  
// })

app.config.globalProperties.$socket = socket;
window.socket = socket;

app.use(router);
app.use(store);
app.component("call-controls", CallControls);
app.component("checkin-card", CheckInCard);
app.mount("#app");
store.dispatch("setUserInfo")
store.dispatch("refreshCheckins")
store.dispatch("refreshAppointment")
store.dispatch("initCallSettings")
