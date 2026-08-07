<style>
    :root {
        --gold: #f5a300;
        --gold-dark: #c78600;
    }

    .main-header {
        background: linear-gradient(135deg, #1a1c2c, #4a192c);
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
    }


    .logo-side {
        width: 120px;
        min-width: 120px;
        background: #fff;
        border-right: 3px solid #000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4px;
    }

    .logo-side img {
        /* width: 195px;
        height: 136px; */
        width: 195px;
        height: 136px;
    }

    .top-welcome-bar {
        background: linear-gradient(to right, #5b0000, #9b0037, #5b0000);
        min-height: 38px;
        color: #fff700;
        font-weight: 900;
        font-size: 1.05rem;
        padding: 0 15px;
    }

    .slot-box {
        width: 190px;
        min-width: 190px;

        background: linear-gradient(135deg, #8a2be2, #b84dff);

        display: flex;
        align-items: center;
        justify-content: center;

        color: #fff;
        font-size: 1.1rem;
        font-weight: 900;

        border-right: 2px solid #c78600;
    }

    /* .slot-box {
        width: 260px;
        min-width: 260px;
        background: linear-gradient(135deg, #8a2be2, #b84dff);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.1rem;
        font-weight: 900;
        border-right: 2px solid #c78600;
    } */

    /* .menu-strip {
        background: var(--gold);
        min-height: 52px;
        padding: 4px;
    } */

    /* .menu-strip {
        background: var(--gold);
        height: 40px;
        padding: 0 6px;

        display: flex;
        align-items: flex-start;

        overflow: hidden;
    } */

    /* .top-nav-btn {
        background: linear-gradient(to bottom, #ffe16a, #ffc400);
        color: #000;
        border: 1px solid #8f6200;
        border-radius: 2px;
        padding: 3px 14px;
        font-size: .85rem;
        font-weight: 700;
        text-decoration: none;
        min-width: 105px;
        text-align: center;
        line-height: 24px;
    } */

    .menu-strip {
        background: var(--gold);

        height: 52px;

        padding: 0 8px;

        display: flex;
        align-items: center;

        gap: 6px;

        overflow: hidden;

        margin: 0;
    }

    /*
    .top-nav-btn {
        background: linear-gradient(to bottom, #ffe16a, #ffc400);
        color: #000;
        border: 1px solid #8f6200;
        border-radius: 2px;

        height: 32px;
        line-height: 30px;

        padding: 0 14px;

        font-size: .85rem;
        font-weight: 700;

        min-width: 105px;

        text-align: center;
        text-decoration: none;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        margin-top: 0;
        margin-bottom: 0;
    } */

    .top-nav-btn {
        background: linear-gradient(to bottom, #ffe16a, #ffc400);

        color: #000;

        border: 1px solid #8f6200;

        border-radius: 2px;

        height: 36px;

        line-height: 34px;

        padding: 0 18px;

        font-size: .92rem;

        font-weight: 700;

        min-width: 130px;

        text-align: center;

        text-decoration: none;

        display: inline-flex;

        align-items: center;

        justify-content: center;

        margin: 0;
    }


    .top-nav-btn:hover {
        color: #000;
    }

    .modal-content-custom {
        background: linear-gradient(135deg, #1b1d2b, #2c2038) !important;
        color: #fff !important;
        border: 2px solid #f5a300 !important;
        border-radius: 8px !important;
        overflow: hidden;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.5);
    }

    .modal-header {
        background: linear-gradient(to right, #5b0000, #8b0030);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
        padding: 12px 18px;
    }

    .modal-title {
        color: #ffdf4d !important;
        font-size: 1.1rem;
        letter-spacing: .5px;
    }

    .modal-body {
        padding: 20px;
    }

    .modal-footer {
        background: rgba(255, 255, 255, 0.03);
        border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
        padding: 12px 18px;
    }

    .pass-field {
        background: #111827 !important;
        border: 1px solid #4b5563 !important;
        color: #fff !important;
        height: 44px;
        border-radius: 5px;
        padding-right: 42px;
    }

    .pass-field:focus {
        border-color: #f5a300 !important;
        box-shadow: 0 0 0 0.15rem rgba(245, 163, 0, .25) !important;
        background: #111827 !important;
        color: #fff !important;
    }

    .toggle-eye {
        position: absolute;
        top: 39px;
        right: 14px;
        cursor: pointer;
        color: #ffc107;
        font-size: 1rem;
        z-index: 5;
    }

    .btn-lotto-green {
        background: linear-gradient(to bottom, #28c76f, #198754);
        color: #fff;
        padding: 8px 18px;
        font-weight: 700;
        border-radius: 4px;
        border: none;
    }

    .btn-lotto-green:hover {
        background: linear-gradient(to bottom, #34d67c, #157347);
    }

    .btn-close-white {
        filter: brightness(0) invert(1);
    }


    .result-strip {
        display: flex;
        align-items: center;

        overflow: hidden;

        margin: 0;
        padding: 0;

        line-height: 0;
    }

    .modal.fade {
        z-index: 99999 !important;
    }

    .modal-backdrop {
        z-index: 99998 !important;
        background: rgba(0, 0, 0, .7) !important;
    }

    @media(max-width:991px) {

        .logo-side {
            width: 85px;
            min-width: 85px;
        }

        .logo-side img {
            width: 70px;
        }

        .slot-box {
            width: 160px;
            min-width: 160px;
            font-size: .9rem;
        }

        .top-nav-btn {
            min-width: 85px;
            font-size: .72rem;
            padding: 2px 8px;
        }

        .top-welcome-bar {
            font-size: .8rem;
        }
    }
</style>

<nav class="main-header">

    <div class="d-flex">

        {{-- LEFT LOGO FOR BOTH ROWS --}}
        <a href="{{ route('dashboard') }}" class="text-decoration-none">

            <div class="logo-side">

                <img src="{{ asset('build/assets/images/logo.png') }}" alt="Logo">

            </div>

        </a>

        {{-- RIGHT CONTENT --}}
        <div class="flex-grow-1">

            {{-- TOP SMALL ROW --}}
            <div class="top-welcome-bar d-flex justify-content-between align-items-center">

                <a href="{{ route('dashboard') }}" class="text-decoration-none text-warning fw-bold">

                    <div class="text-uppercase">
                        Rwinlot
                    </div>

                </a>

                <div class="text-uppercase text-center flex-grow-1">
                    WELCOME
                    {{ strtoupper(Auth::user()->username ?? Auth::user()->name) }}

                    (
                    {{ Auth::user()->unique_id ?? Auth::user()->username }}
                    )
                </div>

                <div class="d-flex align-items-center gap-2">

                    @if (Auth::user()->role_id == 1 || Auth::user()->role_id == 2)
                        <a href="{{ route('users.index') }}"
                            class="btn-lotto-green btn-boxed btn btn-primary btn-sm fw-bold">+ Register User</a>
                    @endif

                    <span id="currentTime" class="text-white fw-bold">
                    </span>

                    <a href="{{ route('dashboard') }}" class="btn btn-warning btn-sm fw-bold">
                        Back
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf

                        <button class="btn btn-danger btn-sm fw-bold">
                            Logout
                        </button>
                    </form>

                </div>

            </div>

            {{-- SECOND MAIN ROW --}}
            <div class="d-flex">

                {{-- LEFT TIME BOX (2 ROW HEIGHT) --}}
                <div class="slot-box"
                    style="
                        height:104px;
                        flex-direction:column;
                        gap:4px;
                    ">

                    <div id="currentDrawSlot">
                        --:-- --
                    </div>

                </div>

                {{-- RIGHT SIDE --}}
                <div class="flex-grow-1 d-flex flex-column">

                    {{-- TOP BUTTON ROW --}}
                    <div class="menu-strip gap-1 flex-wrap">

                        <a href="{{ route('account.index') }}" class="top-nav-btn">
                            Accounts
                        </a>

                        <a href="{{ route('transactions.index') }}" class="top-nav-btn">
                            TrDetails
                        </a>

                        <button class="top-nav-btn" data-bs-toggle="modal" data-bs-target="#passwordModal">
                            Password
                        </button>

                        <a href="{{ route('bets.cancel.page') }}" class="top-nav-btn">
                            Cancel
                        </a>


                        <a href="{{ route('reprint.index') }}" class="top-nav-btn">
                            Reprint
                        </a>

                        <a href="{{ route('results.index') }}" class="top-nav-btn">
                            Results
                        </a>

                        <a href="{{ route('claim.index') }}" class="top-nav-btn">
                            Claim
                        </a>

                    </div>

                    {{-- RESULT ROW --}}
                    <div class="result-strip"
                        style="
                                overflow:hidden;
                                border-top:2px solid #c78600;
                            ">

                        @php
                            $rowColors = [
                                '#d4e157',
                                '#ff8a65',
                                '#7cb342',
                                '#ffab91',
                                '#81c784',
                                '#fff59d',
                                '#f8bbd0',
                                '#d4a017',
                                '#3cb371',
                                '#ffe082',
                            ];
                        @endphp

                        @for ($i = 0; $i < 10; $i++)
                            <div class="text-center border-end border-dark flex-grow-1"
                                style="
                                    background:{{ $rowColors[$i] }};
                                    height:50px;
                                    min-width:95px;
                                ">

                                <div id="top-res-val-{{ $i }}"
                                    style="
                                        font-size:2rem;
                                        font-weight:900;
                                        color:#000;
                                        line-height:50px;
                                        letter-spacing:1px;
                                    ">
                                    --
                                </div>

                            </div>
                        @endfor

                    </div>

                </div>

            </div>

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
    window.lastDrawResults = @json($lastResults ?? []);
</script>
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

    function updateTopResults(baseSeries = 1000) {

        if (!window.lastDrawResults) return;

        for (let i = 0; i < 10; i++) {

            const rowStart = baseSeries + (i * 100);

            const result = window.lastDrawResults[rowStart] ?? '--';

            const topVal = document.getElementById(`top-res-val-${i}`);

            if (topVal) {
                topVal.innerText = result;
            }
        }
    }

    updateTopResults();
</script>
