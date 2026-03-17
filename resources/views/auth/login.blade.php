<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Lottery Game</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #20002c, #4b006e);
            color: #fff;
            font-family: "Poppins", sans-serif;
            overflow-x: hidden;
        }

        .header-logo {
            width: 220px;
            margin: 0 auto 20px;
            display: block;
        }

        .login-box {
            width: 100%;
            max-width: 550px;
            margin: 10px auto;
            background: rgba(255, 255, 255, 0.08);
            padding: 35px;
            border-radius: 14px;
            border: 2px solid rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(4px);
        }

        .login-title {
            font-weight: 700;
            text-align: center;
            margin-bottom: 15px;
            letter-spacing: 2px;
            color: #ffb703;
        }

        .form-control {
            height: 48px;
            font-size: 15px;
            background: rgba(255, 255, 255, 0.18);
            border: 2px solid #ffb703;
            color: white;
            border-radius: 8px;
        }

        .form-control:focus {
            border-color: #fff;
            box-shadow: 0 0 8px rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.25);
            color: #fff;
        }

        label {
            font-weight: 600;
        }

        .btn-login {
            background: #ffb703;
            font-weight: 600;
            padding: 14px;
            font-size: 1.1rem;
            border-radius: 8px;
            border: none;
        }

        .btn-login:hover {
            background: #ff9e00;
        }

        .footer-links {
            text-align: center;
            margin-top: 15px;
        }

        a {
            color: #ffb703;
            text-decoration: none;
        }

        a:hover {
            color: #fff;
        }
    </style>
</head>

<body>

    <div class="container">

        <!-- LOGO -->
        <img src="{{ asset('build/assets/images/logo.png') }}" class="header-logo" alt="Rwinlot Logo">
        <!-- CHANGE ABOVE IMAGE TO YOUR LOGO PATH -->

        <div class="login-box">

            <h3 class="login-title">LOGIN TO Rwinlot</h3>

            <!-- Session Status -->
            @if (session('status'))
                <div class="alert alert-success text-center">
                    {{ session('status') }}
                </div>
            @endif

            <!-- FORM -->
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label>Email or Login ID</label>
                    <input type="text" name="login" class="form-control @error('login') is-invalid @enderror"
                        value="{{ old('login') }}" required autofocus>
                    @error('login')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                        required>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="remember" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>

                    {{-- @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}">Forgot Password?</a>
                    @endif --}}
                </div>

                <button type="submit" class="btn btn-login w-100">Log In</button>

                {{-- <div class="footer-links mt-3">
                    Don’t have an account?
                    <a href="{{ route('register') }}">Register</a>
                </div> --}}
            </form>

        </div>

    </div>

</body>

</html>
