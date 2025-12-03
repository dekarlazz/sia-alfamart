<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIA Alfamart Modern</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #d7111b; /* Merah Alfamart */
            --secondary: #005eb8; /* Biru Alfamart */
            --bg-gradient: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-gradient);
            background-attachment: fixed;
            color: #333;
            min-height: 100vh;
        }

        /* Efek Kaca (Glassmorphism) */
        .glass-card {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 16px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }
        
        .navbar-glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .btn-alfa-red {
            background-color: var(--primary);
            color: white;
            border-radius: 8px;
            border: none;
        }
        
        .btn-alfa-red:hover {
            background-color: #b00d15;
            color: white;
        }

        .text-alfa-blue { color: var(--secondary); }
        .text-alfa-red { color: var(--primary); }
        
        /* Tabel Transparan */
        table.table { --bs-table-bg: transparent; }
    </style>
</head>
<body>
    <!-- Navigasi Atas -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-glass fixed-top mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">
                <span class="text-alfa-red">ALFA</span><span class="text-alfa-blue">MART</span> SIA
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto gap-3">
                    <li class="nav-item"><a class="nav-link fw-bold" href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('laporan.jurnal') }}">Jurnal Umum</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('laporan.buku_besar') }}">Buku Besar</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('laporan.keuangan') }}">Laporan Keuangan</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Konten Utama -->
    <div class="container py-5 mt-5">
        @if(session('success'))
        <div class="alert alert-success glass-card border-0 d-flex align-items-center mb-4">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
        @endif

        @yield('content')
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @stack('scripts')
</body>
</html>