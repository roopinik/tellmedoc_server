// Install Pusher in your client app:
// npm install pusher-js

// Example usage in a JavaScript/Vue.js application:

import Pusher from 'pusher-js';

// Initialize Pusher with your credentials
const pusher = new Pusher('da40edd73f5716cf1922', {
  cluster: 'ap2',
  encrypted: true
});

/**
 * Subscribe to a hospital's channel
 * @param {number} hospitalId - The hospital ID to subscribe to
 * @returns {Object} - The Pusher channel object
 */
function subscribeToHospital(hospitalId) {
  // Subscribe to the hospital's channel
  const channel = pusher.subscribe(`hospital.${hospitalId}`);
  
  // Listen for queued appointment updates
  channel.bind('queued-appointment-update', function(data) {
    // Only log the necessary data we're interested in
    console.log({
      action: data.action,
      doctor_id: data.doctor_id,
      hospital_id: data.hospital_id
    });
    
    // Handle the update
    if (data.action === 'appointments_updated') {
      refreshAppointmentList(data.hospital_id, data.doctor_id);
    }
  });
  
  return channel;
}

// Function to refresh appointment list
function refreshAppointmentList(hospitalId, doctorId) {
  // Here you would update your UI based on the new appointment data
  console.log(`Refreshing appointments for hospital ${hospitalId} and doctor ${doctorId}`);
  
  // Example API call
  fetch(`/api/television/queued-appointments?hospital_id=${hospitalId}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        console.log('Retrieved appointments:', data.data);
        // Update your UI here
      }
    })
    .catch(error => {
      console.error('Error fetching appointments:', error);
    });
}

// Example usage:
// const hospitalId = 7;
// const channel = subscribeToHospital(hospitalId);

// When component unmounts or you're done:
// pusher.unsubscribe(`hospital.${hospitalId}`); 