<?php

namespace App\Http\Controllers\Api;

use App\Events\QueuedAppointmentEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TelevisionController extends Controller
{
    /**
     * Get hospital_id, client_id, and television_configuration based on email
     * 
     * @param Request $request
     * @param string|null $email
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserConfigByEmail(Request $request, $email = null)
    {
        try {
            // Get email from URL parameter or query string
            $email = $email ?? $request->query('email');
            
            if (empty($email)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email is required.',
                ], 400)->header('Access-Control-Allow-Origin', '*')
                     ->header('Access-Control-Allow-Methods', 'GET, OPTIONS');
            }

            // Fetch user data from filament_users table
            $userData = DB::table('filament_users')
                ->where('email', $email)
                ->select('client_id', 'hospital_id', 'television_configuration')
                ->first();

            if (!$userData) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found with the provided email.',
                ], 404)->header('Access-Control-Allow-Origin', '*')
                     ->header('Access-Control-Allow-Methods', 'GET, OPTIONS');
            }

            return response()->json([
                'success' => true,
                'data' => $userData
            ])->header('Access-Control-Allow-Origin', '*')
               ->header('Access-Control-Allow-Methods', 'GET, OPTIONS');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user configuration',
                'error' => $e->getMessage()
            ], 500)->header('Access-Control-Allow-Origin', '*')
                 ->header('Access-Control-Allow-Methods', 'GET, OPTIONS');
        }
    }

    /**
     * Get queued appointments with hospital ID matching the user's hospital ID
     * 
     * @param Request $request
     * @param string|null $email
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQueuedAppointmentsByEmail(Request $request, $email = null)
    {
        try {
            // Get email from URL parameter or query string
            $email = $email ?? $request->query('email');
            
            if (empty($email)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email is required.',
                ], 400)->header('Access-Control-Allow-Origin', '*')
                     ->header('Access-Control-Allow-Methods', 'GET, OPTIONS');
            }

            // First fetch the hospital_id from filament_users table based on email
            $userData = DB::table('filament_users')
                ->where('email', $email)
                ->select('hospital_id', 'client_id')
                ->first();

            if (!$userData) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found with the provided email.',
                ], 404)->header('Access-Control-Allow-Origin', '*')
                     ->header('Access-Control-Allow-Methods', 'GET, OPTIONS');
            }

            $hospitalId = $userData->hospital_id;
            $clientId = $userData->client_id;

            // Now fetch queued appointments from whats_app_appointments table with doctor's name
            $queuedAppointments = DB::table('whats_app_appointments')
                ->where('whats_app_appointments.status', 'Queued')
                ->where('whats_app_appointments.hospital_id', $hospitalId)
                ->where('whats_app_appointments.client_id', $clientId)
                ->leftJoin('filament_users', 'whats_app_appointments.doctor_id', '=', 'filament_users.id')
                ->select(
                    'whats_app_appointments.hospital_id',
                    'whats_app_appointments.client_id',
                    'whats_app_appointments.token',
                    'whats_app_appointments.doctor_id',
                    'filament_users.first_name as doctor_first_name',
                    'filament_users.last_name as doctor_last_name'
                )
                ->get();

            return response()->json([
                'success' => true,
                'data' => $queuedAppointments
            ])->header('Access-Control-Allow-Origin', '*')
              ->header('Access-Control-Allow-Methods', 'GET, OPTIONS');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch queued appointments',
                'error' => $e->getMessage()
            ], 500)->header('Access-Control-Allow-Origin', '*')
                 ->header('Access-Control-Allow-Methods', 'GET, OPTIONS');
        }
    }
    
    /**
     * Get header image from television_configuration based on email
     * 
     * @param Request $request
     * @param string|null $email
     * @return \Illuminate\Http\Response
     */
    public function getHeaderImageByEmail(Request $request, $email = null)
    {
        try {
            // Get email from URL parameter or query string
            $email = $email ?? $request->query('email');
            
            if (empty($email)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email is required.',
                ], 400);
            }

            // Fetch television_configuration from filament_users table
            $userData = DB::table('filament_users')
                ->where('email', $email)
                ->select('television_configuration')
                ->first();

            if (!$userData) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found with the provided email.',
                ], 404);
            }

            // Parse the television_configuration JSON
            $tvConfig = json_decode($userData->television_configuration, true);
            
            // Extract header_image if it exists
            $headerImage = $tvConfig['header_image'] ?? null;
            
            if (empty($headerImage)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Header image not found in the configuration.',
                ], 404);
            }
            
            // Check if Filament stores this as an array (new versions do this)
            if (is_array($headerImage) && isset($headerImage[0])) {
                $headerImage = $headerImage[0];
            }
            
            // Check if the image path is relative or absolute
            if (filter_var($headerImage, FILTER_VALIDATE_URL)) {
                // It's a URL, redirect to it
                return redirect($headerImage);
            }
            
            // Try different paths to locate the image
            $possiblePaths = [
                // Standard public disk storage path
                storage_path('app/public/' . $headerImage),
                // Check if file is stored directly in public
                public_path($headerImage),
                // Try with direct public path
                base_path('public/' . $headerImage),
                // Check if stored without the television_uploads prefix
                strpos($headerImage, 'television_uploads/') === 0 
                    ? storage_path('app/public/' . substr($headerImage, 19))
                    : null,
                // Try adding television_uploads if it's missing
                strpos($headerImage, 'television_uploads/') !== 0
                    ? storage_path('app/public/television_uploads/' . basename($headerImage))
                    : null
            ];
            
            // Debug information
            $triedPaths = [];
            
            // Try each path
            foreach ($possiblePaths as $path) {
                if (!$path) continue;
                
                $triedPaths[] = $path;
                
                if (file_exists($path)) {
                    // Found the file! Serve it
                    $imageContent = file_get_contents($path);
                    $extension = pathinfo($path, PATHINFO_EXTENSION);
                    $mimeType = $this->getMimeType($extension);
                    
                    return response($imageContent)
                        ->header('Content-Type', $mimeType)
                        ->header('Access-Control-Allow-Origin', '*')
                        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
                        ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept');
                }
            }
            
            // If we get here, the image wasn't found
            return response()->json([
                'success' => false,
                'message' => 'Image file not found at path: ' . $headerImage,
                'debug_info' => [
                    'tried_paths' => $triedPaths,
                    'header_image_value' => $headerImage,
                    'tv_config' => $tvConfig,
                    'current_path' => public_path(),
                    'storage_path' => storage_path('app/public')
                ]
            ], 404);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch header image',
                'error' => $e->getMessage(),
                'trace' => $e->getTrace()
            ], 500);
        }
    }
    
    /**
     * Upload or create a test image for the given email
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createSampleImage(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'email' => 'required|email',
            ]);

            $email = $request->email;

            // Fetch user data
            $userData = DB::table('filament_users')
                ->where('email', $email)
                ->select('id', 'television_configuration')
                ->first();

            if (!$userData) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found with the provided email.',
                ], 404);
            }

            // Parse existing config
            $tvConfig = json_decode($userData->television_configuration, true) ?? [];

            // Generate a filename based on user id
            $filename = strtoupper(md5($userData->id . time())) . '.PNG';
            $relativePath = 'television_uploads/' . $filename;
            $fullPath = storage_path('app/public/' . $relativePath);

            // Ensure directory exists
            if (!is_dir(storage_path('app/public/television_uploads'))) {
                mkdir(storage_path('app/public/television_uploads'), 0755, true);
            }

            // Create a sample image
            $image = imagecreatetruecolor(600, 150);
            $bgColor = imagecolorallocate($image, 240, 240, 255); // Light blue background
            $textColor = imagecolorallocate($image, 0, 0, 128);   // Dark blue text
            
            // Fill background
            imagefilledrectangle($image, 0, 0, 600, 150, $bgColor);
            
            // Add text
            $text = "TelmeDoc Header Image";
            // Use the simpler imagestring instead of imagettftext
            imagestring($image, 5, 20, 60, $text, $textColor);
            imagestring($image, 3, 20, 80, $email, $textColor);
            
            // Save the image
            imagepng($image, $fullPath);
            imagedestroy($image);

            // Update user's configuration
            $tvConfig['header_image'] = $relativePath;
            DB::table('filament_users')
                ->where('email', $email)
                ->update(['television_configuration' => json_encode($tvConfig)]);

            return response()->json([
                'success' => true,
                'message' => 'Sample header image created successfully.',
                'data' => [
                    'path' => $relativePath,
                    'full_path' => $fullPath,
                    'url' => url('api/television/header-image/' . $email)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create sample image',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    
    /**
     * Get MIME type based on file extension
     *
     * @param string $extension
     * @return string
     */
    private function getMimeType($extension)
    {
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
        ];
        
        return $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
    }
    
    /**
     * Test broadcasting with Pusher
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testBroadcast(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'hospital_id' => 'required|numeric',
                'client_id' => 'required|numeric',
                'doctor_id' => 'required|numeric'
            ]);

            $hospitalId = $request->hospital_id;
            $clientId = $request->client_id;
            $doctorId = $request->doctor_id;
            
            // Broadcast the test event
            event(new QueuedAppointmentEvent(
                $hospitalId,
                $clientId,
                $doctorId
            ));

            return response()->json([
                'success' => true,
                'message' => 'Test broadcast sent to hospital.'.$hospitalId,
                'data' => [
                    'action' => 'appointments_updated',
                    'doctor_id' => (string) $doctorId,
                    'hospital_id' => $hospitalId
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test broadcast',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 