<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Clínica Vida Salud</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { display: flex; min-height: 100vh; background: #ecf0f1; }
        
        .sidebar {
            width: 260px; background: white; 
            display: flex; flex-direction: column; position: fixed; height: 100vh;
            border-right: 1px solid #ddd;
        }
        .sidebar-header {
            padding: 20px; background: #1a5276; color: white; text-align: center;
        }
        .sidebar-header .logo { font-size: 24px; font-weight: bold; }
        .sidebar-menu { padding: 15px 0; overflow-y: auto; flex: 1; }
        .menu-section { 
            padding: 10px 20px 5px; font-size: 11px; color: #95a5a6; 
            text-transform: uppercase; letter-spacing: 1px; font-weight: 600;
        }
        .menu-item {
            padding: 10px 20px; color: #7f8c8d; text-decoration: none;
            display: block; transition: all 0.3s; font-size: 14px;
        }
        .menu-item:hover, .menu-item.active {
            background: #ecf0f1; color: #2c3e50; border-left: 4px solid #3498db;
        }
        .menu-item button {
            width: 100%; border: none; background: none; color: #7f8c8d; 
            text-align: left; cursor: pointer; font-size: 14px; padding: 0;
        }
        .menu-item button:hover { color: #2c3e50; }
        
        .main-content { margin-left: 260px; flex: 1; display: flex; flex-direction: column; }
        
        .top-header {
            background: #1a5276; padding: 15px 30px; 
            display: flex; justify-content: space-between; align-items: center;
        }
        .top-header h1 { color: white; font-size: 20px; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .user-info span { color: #bdc3c7; font-size: 13px; }
        .user-info strong { color: white; background: #2980b9; padding: 4px 12px; border-radius: 4px; }
        
        .content { padding: 30px; flex: 1; }
        
        .card {
            background: white; border-radius: 5px; padding: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 25px;
        }
        
        .btn {
            padding: 8px 16px; border: none; border-radius: 4px;
            cursor: pointer; font-size: 13px; font-weight: 600;
            transition: all 0.3s; text-decoration: none; display: inline-block;
        }
        .btn-primary { background: #2980b9; color: white; }
        .btn-primary:hover { background: #1e5f8e; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-danger:hover { background: #c0392b; }
        .btn-secondary { background: #95a5a6; color: white; }
        .btn-secondary:hover { background: #7f8c8d; }
        
        .alert {
            padding: 12px 15px; border-radius: 4px; margin-bottom: 20px; font-size: 14px;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo">✚ Clínica Vida Salud</div>
        </div>
        <nav class="sidebar-menu">
            <a href="{{ route('recepcionista.dashboard') }}" class="menu-item {{ request()->routeIs('recepcionista.dashboard') ? 'active' : '' }}">
                Inicio
            </a>
            
            <div class="menu-section">Pacientes</div>
            <a href="{{ route('recepcionista.pacientes.index') }}" class="menu-item {{ request()->routeIs('recepcionista.pacientes.*') ? 'active' : '' }}">
                Pacientes
            </a>
            
            <div class="menu-section">Citas</div>
            <a href="#" class="menu-item">Disponibilidad</a>
            <a href="#" class="menu-item">Programar cita</a>
            <a href="#" class="menu-item">Citas registradas</a>
            <a href="#" class="menu-item">Agenda médica</a>
            <a href="#" class="menu-item">Historial de citas</a>
            
            <div class="menu-section">Caja y Pagos</div>
            <a href="{{ route('recepcionista.pagos.index') }}" class="menu-item {{ request()->routeIs('recepcionista.pagos.*') ? 'active' : '' }}">
                Registrar Pago / Historial
            </a>

            <div class="menu-section">Sesión</div>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="menu-item" style="width: 100%; border: none; background: none; color: #7f8c8d; text-align: left; cursor: pointer; font-size: 14px; padding: 10px 20px;">
                    Cerrar sesión
                </button>
            </form>
        </nav>
    </aside>

    <div class="main-content">
        <header class="top-header">
            <h1>@yield('page-title', 'Panel de Control')</h1>
            <div class="user-info">
                <span>{{ auth()->user()->nombre_usuario }}</span>
                <strong>{{ auth()->user()->rol->nombre }}</strong>
            </div>
        </header>

        <main class="content">
            @if(session('exito'))
                <div class="alert alert-success">{{ session('exito') }}</div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            
            @yield('content')
        </main>
    </div>

    @yield('scripts')
</body>
</html>