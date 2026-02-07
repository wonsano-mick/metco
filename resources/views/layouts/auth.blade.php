<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>METCO | Login Page</title>
     <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/metco_logo.png') }}" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen flex items-center justify-center" style="background: radial-gradient(circle at center, #1e3a8a 0%, #020617 100%); opacity: 1;">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="flex justify-center">
                <div class="w-60 h-25 flex items-center justify-center">
                    <img 
                        src="{{ asset('images/metco_logo.png') }}" 
                        alt="METCU Logo"
                        class="max-w-full max-h-full object-contain"
                        onerror="this.onerror=null; this.src='https://via.placeholder.com/192x64?text=METCU+Logo'; this.alt='METCU Logo Placeholder';"
                    >
                </div>
            </div>
            {{-- <h1 class="text-2xl font-bold text-gray-900">METCO</h1>
            <p class="text-gray-600">Digital Banking Solutions</p> --}}
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                    <span class="text-red-700 font-medium">Login failed</span>
                </div>
                <ul class="mt-2 text-sm text-red-600">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('status'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    <span class="text-green-700">{{ session('status') }}</span>
                </div>
            </div>
        @endif

        <!-- Login Form -->
        {{ $slot }}
    </div>
</body>
</html>