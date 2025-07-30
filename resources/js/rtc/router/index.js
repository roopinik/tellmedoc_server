import { createRouter, createWebHashHistory } from "vue-router";
// import HomeView from "../views/HomeView.vue";
import ShowQueue from "../views/ShowQueue.vue";
import ConferenceView from "../views/ConferenceView.vue";
import PrescriptionView from "../views/PrescriptionView.vue";

const routes = [
  {
    path: "/",
    name: "queue",
    component: ShowQueue,
  },
  {
    path: "/video-conference",
    name: "conference",
    component: ConferenceView,
    props:true
  },
  {
    path: "/prescriptions",
    name: "prescriptions",
    component: PrescriptionView,
  },
];

const router = createRouter({
  history: createWebHashHistory(),
  routes,
});

export default router;
