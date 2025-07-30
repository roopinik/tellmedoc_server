<!DOCTYPE html>
<html>
<head>
    <title>Debug File Uploads</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        h1 {
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .debug-section {
            margin-bottom: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        pre {
            background-color: #f1f1f1;
            padding: 10px;
            border-radius: 3px;
            overflow: auto;
        }
        .image-preview {
            margin-top: 20px;
            border: 1px solid #ddd;
            padding: 10px;
            max-width: 100%;
        }
        .image-preview img {
            max-width: 100%;
            height: auto;
        }
        .form-section {
            margin: 20px 0;
            padding: 15px;
            background-color: #e9f7ef;
            border-radius: 5px;
        }
        form {
            margin-top: 10px;
        }
        button {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Debug File Uploads</h1>

    <div class="debug-section">
        <h2>Storage Information</h2>
        <p><strong>Default Disk:</strong> {{ config('filesystems.default') }}</p>
        <p><strong>Public Disk Path:</strong> {{ config('filesystems.disks.public.root') }}</p>
        <p><strong>Public URL:</strong> {{ config('filesystems.disks.public.url') }}</p>
        <p><strong>Storage Link:</strong> {{ public_path('storage') }} -> {{ storage_path('app/public') }}</p>
    </div>

    <div class="debug-section">
        <h2>Directory Check</h2>
        <p><strong>television_uploads exists in public?</strong> {{ file_exists(public_path('television_uploads')) ? 'Yes' : 'No' }}</p>
        <p><strong>television_uploads exists in storage/app/public?</strong> {{ file_exists(storage_path('app/public/television_uploads')) ? 'Yes' : 'No' }}</p>
        
        <h3>Files in public/television_uploads:</h3>
        <pre>
@if(file_exists(public_path('television_uploads')))
@php
    $files = scandir(public_path('television_uploads'));
    echo implode("\n", $files);
@endphp
@else
Directory doesn't exist
@endif
        </pre>

        <h3>Files in storage/app/public/television_uploads:</h3>
        <pre>
@if(file_exists(storage_path('app/public/television_uploads')))
@php
    $files = scandir(storage_path('app/public/television_uploads'));
    echo implode("\n", $files);
@endphp
@else
Directory doesn't exist
@endif
        </pre>
    </div>
    
    <div class="form-section">
        <h2>Create Sample Image</h2>
        <form action="{{ url('api/television/create-sample-image') }}" method="POST">
            @csrf
            <div>
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <button type="submit">Create Sample Image</button>
        </form>
    </div>
    
    <div class="debug-section">
        <h2>Current Television Configurations</h2>
        
        @php
            $users = DB::table('filament_users')
                ->whereNotNull('television_configuration')
                ->where('television_configuration', '<>', '{}')
                ->where('television_configuration', '<>', 'null')
                ->select('id', 'email', 'television_configuration')
                ->get();
        @endphp
        
        @foreach($users as $user)
            <div style="margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 10px;">
                <p><strong>User:</strong> {{ $user->email }}</p>
                <pre>{{ json_encode(json_decode($user->television_configuration), JSON_PRETTY_PRINT) }}</pre>
                
                @php
                    $config = json_decode($user->television_configuration, true);
                    $headerImage = $config['header_image'] ?? null;
                    
                    // Handle array format
                    if (is_array($headerImage) && isset($headerImage[0])) {
                        $headerImage = $headerImage[0];
                    }
                @endphp
                
                @if($headerImage)
                    <div class="image-preview">
                        <h4>Header Image Preview</h4>
                        <p>Path: {{ $headerImage }}</p>
                        
                        @if(file_exists(storage_path('app/public/' . $headerImage)))
                            <img src="{{ asset('storage/' . $headerImage) }}" alt="Header Image">
                            <p><strong>Source:</strong> storage/app/public/{{ $headerImage }}</p>
                        @elseif(file_exists(public_path($headerImage)))
                            <img src="{{ asset($headerImage) }}" alt="Header Image">
                            <p><strong>Source:</strong> public/{{ $headerImage }}</p>
                        @else
                            <p>Image file not found. Check the path.</p>
                        @endif
                        
                        <p><a href="{{ url('api/television/header-image/' . $user->email) }}" target="_blank">View via API</a></p>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</body>
</html> 