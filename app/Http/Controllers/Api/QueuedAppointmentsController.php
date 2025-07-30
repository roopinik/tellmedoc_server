<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppAppointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // Import the Auth facade

class QueuedAppointmentsController extends Controller
{
    /**
     * Get appointments with status Queued and selected fields
     * Fetch hospital_id from doctor_hospitals table based on doctor_id
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQueuedAppointments()
    {
        try {
            // Get the authenticated user
            $user = Auth::user();

            // Check if a user is authenticated
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please log in.',
                ], 401); // 401 Unauthorized
            }

            // Get the client_id and hospital_id from the authenticated user.
            // This assumes your authenticated user model has 'client_id' and 'hospital_id' attributes.
            $clientId = $user->client_id;
            $userHospitalId = $user->hospital_id; // Get the hospital_id of the logged-in user

            // Check if client_id or hospital_id exist for the user
            if (empty($clientId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Client ID not found for the authenticated user.',
                ], 403); // 403 Forbidden
            }
            if (empty($userHospitalId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hospital ID not found for the authenticated user.',
                ], 403); // 403 Forbidden
            }

            // Get counts for debugging, now filtered by client_id and hospital_id
            // Use withoutGlobalScopes() to prevent potential ambiguous client_id from global scopes
            $totalAppointments = WhatsAppAppointment::withoutGlobalScopes()
                ->where('whats_app_appointments.client_id', $clientId)
                ->where('whats_app_appointments.hospital_id', $userHospitalId) // Filter by hospital_id
                ->count();

            $queuedCount = WhatsAppAppointment::withoutGlobalScopes()
                ->where('whats_app_appointments.client_id', $clientId)
                ->where('whats_app_appointments.hospital_id', $userHospitalId) // Filter by hospital_id
                ->where('whats_app_appointments.status', 'Queued')
                ->count();
            
            // doctor_hospitals count is not filtered by client_id/hospital_id, as it's a lookup table
            $doctorHospitalsCount = DB::table('doctor_hospitals')->count(); 
            // filament_users count (optional, for debugging)
            $filamentUsersCount = DB::table('filament_users')->count();
            
            // Fetch hospital details (name and logo) from the 'clients' table
            // assuming 'clients' table stores hospital/client specific details like name and logo
            $hospitalDetails = DB::table('clients')
                                ->where('id', $clientId)
                                ->select('name as hospital_name', 'logo_path as hospital_logo')
                                ->first();

            $queuedAppointments = WhatsAppAppointment::withoutGlobalScopes() // Temporarily remove all global scopes
                ->where('whats_app_appointments.status', 'Queued')
                // Re-add the filter for client_id explicitly and qualified
                ->where('whats_app_appointments.client_id', $clientId)
                // Add the filter for hospital_id explicitly and qualified
                ->where('whats_app_appointments.hospital_id', $userHospitalId) 
                ->leftJoin('doctor_hospitals', 'whats_app_appointments.doctor_id', '=', 'doctor_hospitals.doctor_id')
                // Join filament_users table to get doctor's name
                ->leftJoin('filament_users', function($join) use ($clientId, $userHospitalId) {
                    $join->on('whats_app_appointments.doctor_id', '=', 'filament_users.id')
                         // Explicitly qualify client_id for filament_users to avoid ambiguity
                         ->where('filament_users.client_id', $clientId)
                         // Also filter doctors by hospital_id for consistency, if applicable
                         ->where('filament_users.hospital_id', $userHospitalId); 
                })
                ->select(
                    'whats_app_appointments.token', 
                    'whats_app_appointments.doctor_id', 
                    'whats_app_appointments.id',
                    'whats_app_appointments.client_id', // Include client_id in select for verification
                    'whats_app_appointments.hospital_id as appointment_hospital_id', // Always use this for appointment's hospital_id
                    'filament_users.first_name', // Fetch doctor's first name
                    'filament_users.last_name' // Fetch doctor's last name
                )
                ->distinct() // Add distinct to ensure unique rows
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $queuedAppointments,
                'hospital_info' => $hospitalDetails, // Added hospital name and logo
                'user_info' => $user, // Added the authenticated user's details
                'debug' => [
                    'client_id_filter' => $clientId, // Added the client_id used for filtering
                    'hospital_id_filter' => $userHospitalId, // Added the hospital_id used for filtering
                    'total_appointments_for_client_hospital' => $totalAppointments,
                    'queued_count_for_client_hospital' => $queuedCount,
                    'doctor_hospitals_count' => $doctorHospitalsCount,
                    'filament_users_count' => $filamentUsersCount // Added for debugging
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch queued appointments',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
