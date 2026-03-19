@extends('layouts.app')

@section('content')
    <div class="container py-3">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold text-warning mb-0">Register User</h3>

            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
        </div>

        <form id="userForm" method="POST" action="{{ route('users.store') }}" novalidate>
            @csrf

            <div class="row g-3">

                <div class="col-md-4">
                    <label class="fw-bold">Unique ID (optional)</label>
                    <input type="text" name="unique_id" value="{{ old('unique_id') }}" class="form-control">
                    <small class="text-danger">
                        @error('unique_id')
                            {{ $message }}
                        @enderror
                    </small>
                    <small class="text-danger d-none" data-error="unique_id"></small>
                </div>

                <div class="col-md-4">
                    <label class="fw-bold">First Name</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" class="form-control capitalize"
                        required>
                    <small class="text-danger">
                        @error('first_name')
                            {{ $message }}
                        @enderror
                    </small>
                    <small class="text-danger d-none" data-error="first_name"></small>
                </div>

                <div class="col-md-4">
                    <label class="fw-bold">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" class="form-control capitalize">
                    <small class="text-danger">
                        @error('last_name')
                            {{ $message }}
                        @enderror
                    </small>
                    <small class="text-danger d-none" data-error="last_name"></small>
                </div>

                <div class="col-md-4">
                    <label class="fw-bold">Username</label>
                    <input type="text" name="username" value="{{ old('username') }}" class="form-control" required>
                    <small class="text-danger">
                        @error('username')
                            {{ $message }}
                        @enderror
                    </small>
                    <small class="text-danger d-none" data-error="username"></small>
                </div>

                <div class="col-md-4">
                    <label class="fw-bold">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control">
                    <small class="text-danger">
                        @error('email')
                            {{ $message }}
                        @enderror
                    </small>
                    <small class="text-danger d-none" data-error="email"></small>
                </div>

                <div class="col-md-4">
                    <label class="fw-bold">Mobile</label>
                    <input type="text" name="mobile" value="{{ old('mobile') }}" class="form-control only-numbers"
                        maxlength="10" minlength="10" inputmode="numeric" pattern="[0-9]{10}"
                        placeholder="Enter 10 digit mobile no.">
                    <small class="text-danger">
                        @error('mobile')
                            {{ $message }}
                        @enderror
                    </small>
                    <small class="text-danger d-none" data-error="mobile"></small>
                </div>

                <div class="col-md-4">
                    <label class="fw-bold">Role</label>
                    <select name="role_id" class="form-select" required>
                        @foreach ($roles as $r)
                            <option value="{{ $r->id }}" @selected(old('role_id') == $r->id)>{{ $r->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-danger">
                        @error('role_id')
                            {{ $message }}
                        @enderror
                    </small>
                    <small class="text-danger d-none" data-error="role_id"></small>
                </div>

                <div class="col-md-4">
                    <label class="fw-bold">Status</label>
                    <select name="general_status_id" class="form-select" required>
                        @foreach ($statuses as $s)
                            <option value="{{ $s->id }}" @selected(old('general_status_id') == $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-danger">
                        @error('general_status_id')
                            {{ $message }}
                        @enderror
                    </small>
                    <small class="text-danger d-none" data-error="general_status_id"></small>
                </div>

                {{-- Commission Field --}}
                <div class="col-md-4">
                    <label class="fw-bold">Commission (%)</label>
                    <div class="input-group">
                        <input type="number" name="commision" value="{{ old('commision') }}"
                            class="form-control only-numbers-commission" min="0" max="100" step="0.01"
                            inputmode="decimal" placeholder="e.g. 10">
                        <span class="input-group-text">%</span>
                    </div>
                    <small class="text-danger">
                        @error('commision')
                            {{ $message }}
                        @enderror
                    </small>
                    <small class="text-danger d-none" data-error="commision"></small>
                </div>

                {{-- Password with Eye Toggle --}}
                <div class="col-md-4">
                    <label class="fw-bold">Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" required>
                        <button type="button" class="btn btn-outline-secondary px-3"
                            onclick="togglePassword('password', 'eyeIcon1')" tabindex="-1">
                            <i class="fa fa-eye" id="eyeIcon1"></i>
                        </button>
                    </div>
                    <small class="text-danger">
                        @error('password')
                            {{ $message }}
                        @enderror
                    </small>
                    <small class="text-danger d-none" data-error="password"></small>
                </div>

                {{-- Confirm Password with Eye Toggle --}}
                <div class="col-md-4">
                    <label class="fw-bold">Confirm Password</label>
                    <div class="input-group">
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="form-control" required>
                        <button type="button" class="btn btn-outline-secondary px-3"
                            onclick="togglePassword('password_confirmation', 'eyeIcon2')" tabindex="-1">
                            <i class="fa fa-eye" id="eyeIcon2"></i>
                        </button>
                    </div>
                    <small class="text-danger d-none" data-error="password_confirmation"></small>
                </div>

            </div>

            <button class="btn-lotto-green btn-boxed mt-3" type="submit">Create User</button>
        </form>

    </div>

    <script>
        /* ===== Toggle Password Visibility ===== */
        function togglePassword(fieldId, iconId) {
            const input = document.getElementById(fieldId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        /* ===== Auto Capitalize ===== */
        document.querySelectorAll(".capitalize").forEach(input => {
            input.addEventListener("input", function() {
                this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1);
            });
        });

        /* ===== Mobile: 10 digits only ===== */
        document.querySelectorAll(".only-numbers").forEach(input => {
            input.addEventListener("keypress", function(e) {
                if (!/[0-9]/.test(e.key) || this.value.length >= 10) e.preventDefault();
            });
            input.addEventListener("paste", function(e) {
                let paste = (e.clipboardData || window.clipboardData).getData('text');
                if (!/^[0-9]{10}$/.test(paste)) e.preventDefault();
            });
        });

        /* ===== Commission: 0–100 only ===== */
        document.querySelectorAll(".only-numbers-commission").forEach(input => {
            input.addEventListener("input", function() {
                let val = parseFloat(this.value);
                if (val > 100) this.value = 100;
                if (val < 0) this.value = 0;
            });
        });

        /* ===== Form Submit Validation ===== */
        document.getElementById("userForm").addEventListener("submit", function(e) {
            let valid = true;
            const form = this;

            const showError = (field, msg) => {
                valid = false;
                const input = form.querySelector(`[name="${field}"]`);
                const error = form.querySelector(`[data-error="${field}"]`);
                if (input) input.classList.add("is-invalid");
                if (error) {
                    error.classList.remove("d-none");
                    error.innerHTML = msg;
                }
            };

            const clearError = (field) => {
                const input = form.querySelector(`[name="${field}"]`);
                const error = form.querySelector(`[data-error="${field}"]`);
                if (input) input.classList.remove("is-invalid");
                if (error) error.classList.add("d-none");
            };

            ["first_name", "email", "mobile", "username", "password", "password_confirmation", "commision"].forEach(
                clearError);

            /* First Name */
            if (!form.first_name.value.trim().match(/^[A-Za-z]+$/))
                showError("first_name", "Enter valid first name");

            /* Email */
            let email = form.email.value.trim();
            if (email && !email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/))
                showError("email", "Invalid email format");

            /* Mobile */
            if (!/^[0-9]{10}$/.test(form.mobile.value.trim()))
                showError("mobile", "Mobile must be exactly 10 digits");

            /* Commission */
            let comm = form.commision.value.trim();
            if (comm !== '' && (isNaN(comm) || parseFloat(comm) < 0 || parseFloat(comm) > 100))
                showError("commision", "Commission must be between 0 and 100");

            /* Password */
            let pass = form.password.value;
            let confirm = form.password_confirmation.value;
            if (pass.length < 6) showError("password", "Password must be minimum 6 characters");
            if (pass !== confirm) showError("password_confirmation", "Passwords do not match");

            if (!valid) e.preventDefault();
        });
    </script>
@endsection
