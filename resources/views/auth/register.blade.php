<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Lottery Game</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #20002c, #4b006e);
            color: #fff;
            font-family: "Poppins", sans-serif;
            overflow-x: hidden;
            margin: 0;
        }

        .header-logo {
            width: 220px;
            display: block;
            margin: 40px auto 10px;
        }

        .register-box {
            width: 100%;
            max-width: 750px;
            background: rgba(255, 255, 255, 0.08);
            padding: 35px;
            border-radius: 14px;
            border: 2px solid rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(4px);
            margin: 0 auto;
        }

        .register-title {
            text-align: center;
            font-weight: 700;
            letter-spacing: 2px;
            color: #ffb703;
        }

        .form-label {
            font-weight: 600;
        }

        .form-control {
            height: 48px;
            background: rgba(255, 255, 255, 0.18);
            border: 2px solid #ffb703;
            color: #fff;
            border-radius: 8px;
        }

        .form-control:focus {
            border-color: #fff;
            box-shadow: 0 0 8px rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.22);
            color: #fff;
        }

        .btn-register {
            background: #ffb703;
            border: none;
            color: #000;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 14px;
            border-radius: 8px;
        }

        .btn-register:hover {
            background: #ff9e00;
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

    <!-- LOGO -->
    <img src="/logo.png" class="header-logo" alt="Lottery Logo">
    <!-- Change /logo.png with your logo -->

    <div class="register-box">

        <h3 class="register-title mb-2">CREATE LOTTERY ACCOUNT</h3>
        <p class="text-center text-white-50 mb-4">Play • Win • Enjoy</p>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name"
                        class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}"
                        required>
                    @error('first_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                        value="{{ old('last_name') }}">
                    @error('last_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                        value="{{ old('username') }}" required>
                    @error('username')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Mobile Number</label>
                    <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror"
                        value="{{ old('mobile') }}">
                    @error('mobile')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                        required>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

            </div>

            <button type="submit" class="btn btn-register w-100 mt-4">Create Account</button>

            <p class="text-center mt-3">
                Already registered?
                <a href="{{ route('login') }}">Login here</a>
            </p>

        </form>
    </div>

</body>

</html>
