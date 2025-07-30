<!DOCTYPE html>
<html lang="" class="w-100 h-100">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link rel="icon" href="/favicon.ico" />
    <title>conference-app</title>
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"
    />
    <script>
      const isMobile = navigator.userAgentData.mobile;
      window.appId = "ceb09549f3034cfe847ff2cc4a776627";
      window.baseUrl = `{{env('APP_URL')}}/`;
      window.socketUrl = "http://192.168.0.108:3000";
      window.appointmentId = 63;
      window.userToken =
        "a49848e97abe284785f31b7594c444bcb367a5923afbb9fa72184fd21d51f312";
      window.userName = "Patient 0";
      window.userRole = "Patient";
      window.uid = 2;
      window.doctorId = 3;
      window.doctorName = "Dr. Anushka";
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

  </head>
  <body class="w-100 h-100">
    <noscript
      ><strong
        >We're sorry but conference-app doesn't work properly without JavaScript
        enabled. Please enable it to continue.</strong
      ></noscript
    >
    <div id="app" class="w-100 h-100 relative"></div>
  </body>
</html>
