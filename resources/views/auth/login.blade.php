<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - NuupNet</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-main: linear-gradient(135deg, #020d24 0%, #071a3b 100%);
            --card-bg: rgba(30, 49, 84, 0.95);
            --primary: #4a86f7;
            --primary-hover: #2f73f5;
            --text-main: #f5f7fb;
            --border-soft: rgba(255, 255, 255, 0.08);
            --radius-lg: 24px;
            --shadow-main: 0 20px 45px rgba(0, 0, 0, 0.4);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-main);
            color: var(--text-main);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
        }

        .login-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-soft);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-main);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }

        .brand-icon {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            background: linear-gradient(180deg, #63a4ff 0%, #3f7ef3 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            box-shadow: 0 10px 24px rgba(74, 134, 247, .35);
            margin: 0 auto 20px;
        }

        .brand-title {
            text-align: center;
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 30px;
            color: #fff;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #fff;
            border-radius: 14px;
            min-height: 50px;
            padding-left: 45px;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            border-color: #4a86f7;
            box-shadow: 0 0 0 .2rem rgba(74, 134, 247, .20);
        }

        .form-control::placeholder {
            color: #9eb0ca;
        }

        .input-group {
            position: relative;
            margin-bottom: 25px;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #9eb0ca;
            z-index: 10;
        }

        .btn-main {
            background: linear-gradient(90deg, #4a86f7 0%, #3b7bf3 100%);
            border: none;
            color: white;
            padding: 12px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 1rem;
            width: 100%;
            box-shadow: 0 10px 24px rgba(74, 134, 247, .30);
            transition: all 0.3s ease;
        }

        .btn-main:hover {
            background: linear-gradient(90deg, #3d7cf4 0%, #2f73f5 100%);
            color: white;
            transform: translateY(-2px);
        }

        .error-message {
            color: #ff6b6b;
            font-size: 0.85rem;
            margin-top: -15px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="brand-icon">
            <i class="bi bi-bar-chart-fill"></i>
        </div>
        <h2 class="brand-title">NuupNet</h2>

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            
            <p class="text-center text-muted mb-4" style="font-size: 0.9rem;">
                Ingresa la contraseña de la red Wi-Fi para continuar.
            </p>

            <div class="input-group">
                <i class="bi bi-lock-fill input-icon"></i>
                <input type="password" name="password" class="form-control" placeholder="Contraseña de red" required>
            </div>

            @if($errors->any())
                <div class="error-message">
                    <i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}
                </div>
            @endif

            <button type="submit" class="btn btn-main">Ingresar al panel</button>
        </form>
    </div>

</body>
</html>
