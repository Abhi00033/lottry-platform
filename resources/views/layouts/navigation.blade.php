<style>
    :root {
        /* Theme: Midnight Navy & Burnished Gold */
        --bg-main: linear-gradient(135deg, #1a1c2c, #4a192c);
        --bg-secondary: #ffb703;
        --bg-card-dark: #2a2d3e;
        --text-light: #ffffff;
        --text-dark: #222;

        --btn-yellow: #ffb703;
        --btn-yellow-hover: #e0a500;
        --btn-red: #dc3545;
        --btn-red-hover: #bb2d3b;
        --btn-green: #198754;
    }

    /* Modal Content Styling */
    .modal-content-custom {
        background: var(--bg-card-dark) !important;
        color: white !important;
        border: 1px solid var(--btn-yellow) !important;
        backdrop-filter: blur(10px);
    }

    .pass-field {
        padding-right: 40px;
    }

    .toggle-eye {
        position: absolute;
        top: 38px;
        right: 12px;
        cursor: pointer;
        color: var(--btn-yellow);
        font-size: 1.1rem;
    }

    /* Button & UI Styles */
    .btn-lotto {
        background: #ffffff;
        color: var(--text-dark);
        border-radius: 4px;
        border: 1px solid rgba(0, 0, 0, 0.2);
        padding: 6px 18px;
        font-size: 0.95rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
    }

    .btn-lotto-yellow {
        background: var(--btn-yellow);
        color: var(--text-dark);
        padding: 6px 18px;
        font-weight: 600;
        text-decoration: none;
    }

    .btn-lotto-red {
        background: var(--btn-red);
        color: #fff;
        padding: 6px 18px;
        font-weight: 600;
        text-decoration: none;
    }

    .btn-lotto-green {
        background: var(--btn-green);
        color: #fff;
        padding: 8px 20px;
        font-weight: 600;
        text-decoration: none;
        border-radius: 4px;
    }

    .btn-boxed {
        border: 1px solid rgba(0, 0, 0, 0.3);
        box-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
    }

    /* The Slot Box */
    #slotWrapper {
        background: #121212;
        color: var(--btn-yellow);
        padding: .35rem 1rem;
        border-radius: 4px;
        border: 1px solid var(--btn-yellow);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    #currentDrawSlot {
        font-weight: 800;
        font-size: 1.1rem;
    }
</style>

<nav class="shadow-sm" style="background: var(--bg-main); color: var(--text-light);">
    <div class="d-flex justify-content-between align-items-center px-3">
        <a href="{{ route('dashboard') }}" class="text-decoration-none">
            <div class="d-flex align-items-center">
                <img src="{{ asset('build/assets/images/logo.png') }}" height="60" class="rounded me-3" alt="Rwinlot">
                <span class="fw-bold fs-3" style="color: var(--btn-yellow);">Rwinlot</span>
            </div>
        </a>


        <div class="flex-grow-1 text-center">
            <span class="fw-bold fs-5">
                WELCOME {{ strtoupper(Auth::user()->first_name ?? Auth::user()->name) }}
                ( {{ Auth::user()->unique_id ?? Auth::user()->username }})
            </span>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if (Auth::user()->role_id == 1 || Auth::user()->role_id == 2)
                <a href="{{ route('users.index') }}" class="btn-lotto-green btn-boxed">+ Register User</a>
            @endif
            <span id="currentTime" class="fw-semibold px-2 font-monospace"></span>
            <a href="{{ route('dashboard') }}" class="btn-lotto-yellow btn-boxed">Back</a>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button class="btn-lotto-red btn-boxed">Logout</button>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-between px-2 py-1 align-items-center" style="background: var(--bg-secondary);">

        <div id="slotWrapper">
            <span style="font-size: 0.75rem; color: #fff; text-transform: uppercase; font-weight: bold;">Result Draw
                Slot</span>
            <div id="currentDrawSlot">--:-- --</div>
        </div>

        <div class="d-flex gap-1">
            <a href="{{ route('lotto.accounts') }}" class="btn-lotto btn-boxed">Accounts</a>
            <button class="btn-lotto btn-boxed" data-bs-toggle="modal" data-bs-target="#passwordModal">Password</button>
            <a href="{{ route('transactions.index') }}" class="btn-lotto btn-boxed">TrDetails</a>
            <a href="#" class="btn-lotto btn-boxed">Reprint</a>
            {{-- <a href="#" class="btn-lotto btn-boxed">Cancel</a> --}}
            <a href="{{ route('results.index') }}" class="btn-lotto btn-boxed">Results</a>
            <a href="#" class="btn-lotto btn-boxed">Claim</a>
        </div>
    </div>
</nav>

<div class="modal fade" id="passwordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-custom">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold">Change Password</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3 position-relative">
                        <label class="fw-bold text-warning">Old Password</label>
                        <input type="password" name="current_password"
                            class="form-control pass-field bg-dark text-white border-secondary" required>
                        <span class="toggle-eye" onclick="togglePassword(this)"><i class="fa fa-eye"></i></span>
                    </div>
                    <div class="mb-3 position-relative">
                        <label class="fw-bold text-warning">New Password</label>
                        <input type="password" name="password"
                            class="form-control pass-field bg-dark text-white border-secondary" required>
                        <span class="toggle-eye" onclick="togglePassword(this)"><i class="fa fa-eye"></i></span>
                    </div>
                    <div class="mb-3 position-relative">
                        <label class="fw-bold text-warning">Confirm Password</label>
                        <input type="password" name="password_confirmation"
                            class="form-control pass-field bg-dark text-white border-secondary" required>
                        <span class="toggle-eye" onclick="togglePassword(this)"><i class="fa fa-eye"></i></span>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-lotto-green border-0">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // 1. Get Config values from Laravel
    const DRAW_CONF = {
        start: "{{ config('app.draw_start') }}", // e.g., '08:00'
        end: "{{ config('app.draw_end') }}" // e.g., '22:30'
    };

    function updateCurrentSlot() {
        const now = new Date();

        // Format current time to HH:MM for string comparison
        const currentTimeStr = now.getHours().toString().padStart(2, '0') + ':' +
            now.getMinutes().toString().padStart(2, '0');

        const slotElement = document.getElementById("currentDrawSlot");

        // 2. CHECK: Is current time outside active Draw Hours?
        if (currentTimeStr < DRAW_CONF.start || currentTimeStr > DRAW_CONF.end) {
            slotElement.innerText = "--:-- --";
            return;
        }

        // 3. Logic: Find the current active slot (Round DOWN to nearest 15 mins)
        // This ensures the dashboard slot matches the backend Result row
        const slotTime = new Date(now);
        let minutes = now.getMinutes();

        // Math.floor(9/15)*15 = 0. So 08:09 becomes 08:00 slot
        slotTime.setMinutes(Math.floor(minutes / 15) * 15, 0, 0);

        let h = slotTime.getHours();
        let ampm = h >= 12 ? "PM" : "AM";
        h = h % 12 || 12; // Convert to 12-hour format
        let min = slotTime.getMinutes().toString().padStart(2, "0");

        slotElement.innerText = `${h}:${min} ${ampm}`;
    }

    // Live Clock for the header
    function updateTime() {
        const currentTimeElement = document.getElementById('currentTime');
        if (currentTimeElement) {
            currentTimeElement.innerHTML = new Date().toLocaleTimeString();
        }
    }

    // Toggle Password Visibility logic
    function togglePassword(el) {
        const input = el.previousElementSibling;
        const icon = el.querySelector("i");
        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace("fa-eye", "fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.replace("fa-eye-slash", "fa-eye");
        }
    }

    // Initialize intervals
    setInterval(updateTime, 1000);
    setInterval(updateCurrentSlot, 1000);

    // Run immediately on load
    updateTime();
    updateCurrentSlot();
</script>
