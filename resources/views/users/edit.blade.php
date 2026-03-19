@extends('layouts.app')

@section('content')
    <div class="container py-3">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold text-warning m-0">Edit User</h3>
            <a href="{{ route('users.index') }}" class="btn btn-outline-warning btn-sm">
                <i class="fa fa-arrow-left"></i> Back to Users
            </a>
        </div>

        <form id="editUserForm" method="POST" action="{{ route('users.update', $user->id) }}" novalidate>
            @csrf @method('PUT')

            <div class="row g-3">

                <div class="col-md-4">
                    <label class="fw-bold">Unique ID</label>
                    <input type="text" class="form-control" value="{{ $user->unique_id }}" disabled>
                </div>

                <div class="col-md-4">
                    <label class="fw-bold">First Name</label>
                    <input name="first_name" class="form-control capitalize"
                        value="{{ old('first_name', $user->first_name) }}" required>
                    <small class="text-danger d-none" data-error="first_name"></small>
                </div>

                <div class="col-md-4">
                    <label class="fw-bold">Last Name</label>
                    <input name="last_name" class="form-control capitalize"
                        value="{{ old('last_name', $user->last_name) }}">
                    <small class="text-danger d-none" data-error="last_name"></small>
                </div>

                <div class="col-md-4">
                    <label class="fw-bold">Email</label>
                    <input name="email" class="form-control" value="{{ old('email', $user->email) }}">
                    <small class="text-danger">
                        @error('email')
                            {{ $message }}
                        @enderror
                    </small>
                    <small class="text-danger d-none" data-error="email"></small>
                </div>

                <div class="col-md-4">
                    <label class="fw-bold">Mobile</label>
                    <input name="mobile" class="form-control only-numbers" value="{{ old('mobile', $user->mobile) }}"
                        maxlength="10" inputmode="numeric" pattern="[0-9]{10}" placeholder="10 digits">
                    <small class="text-danger">
                        @error('mobile')
                            {{ $message }}
                        @enderror
                    </small>
                    <small class="text-danger d-none" data-error="mobile"></small>
                </div>

                <div class="col-md-4">
                    <label class="fw-bold">Username</label>
                    <input name="username" class="form-control" value="{{ old('username', $user->username) }}" required>
                    <small class="text-danger">
                        @error('username')
                            {{ $message }}
                        @enderror
                    </small>
                    <small class="text-danger d-none" data-error="username"></small>
                </div>

                <div class="col-md-4">
                    <label class="fw-bold">Role</label>
                    <select name="role_id" class="form-select" required>
                        @foreach ($roles as $r)
                            <option value="{{ $r->id }}" @selected(old('role_id', $user->role_id) == $r->id)>
                                {{ $r->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-danger d-none" data-error="role_id"></small>
                </div>

                {{-- Commission Field --}}
                <div class="col-md-4">
                    <label class="fw-bold">Commission (%)</label>
                    <div class="input-group">
                        <input type="number" name="commision" value="{{ old('commision', $user->commision) }}"
                            class="form-control" min="0" max="100" step="0.01" inputmode="decimal"
                            placeholder="e.g. 10">
                        <span class="input-group-text">%</span>
                    </div>
                    <small class="text-danger">
                        @error('commision')
                            {{ $message }}
                        @enderror
                    </small>
                    <small class="text-danger d-none" data-error="commision"></small>
                </div>

                <div class="col-md-4">
                    <label class="fw-bold">Status</label>
                    <select name="general_status_id" class="form-select" required>
                        @foreach ($statuses as $s)
                            <option value="{{ $s->id }}" @selected(old('general_status_id', $user->general_status_id) == $s->id)>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-danger d-none" data-error="general_status_id"></small>
                </div>
            </div>

            <button class="btn-lotto-yellow btn-boxed mt-3" type="submit">Update User</button>
        </form>

        <hr class="mt-4">
        {{-- ================ HIERARCHICAL WALLET UPDATE SECTION ================= --}}
        @php
            $authUser = auth()->user();
            $canEditWallet = false;

            // 1. Admin (Role 1) can edit ANY wallet, including their own
            if ($authUser->role_id == 1) {
                $canEditWallet = true;
            }
            // 2. Agents (Role 2) can ONLY edit their own Retailers (Role 3)
            elseif ($authUser->role_id == 2 && $user->role_id == 3 && $user->parent_id == $authUser->id) {
                $canEditWallet = true;
            }
        @endphp

        @if ($canEditWallet)
            <h5 class="text-warning fw-bold mb-2">Wallet Balance: ₹{{ number_format($user->balance, 2) }}</h5>

            <div class="card bg-dark border-warning p-3 mb-4">
                <form method="POST" action="{{ route('users.balance.update', $user->id) }}">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-3">
                            <input type="number" step="0.01" min="0" name="amount" class="form-control"
                                placeholder="Amount" required>
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="remarks" class="form-control" placeholder="Remarks (optional)">
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button name="action" value="add" class="btn-lotto-green btn-boxed w-50">Add (+)</button>
                            <button name="action" value="deduct" class="btn-lotto-red btn-boxed w-50">Deduct
                                (-)</button>
                        </div>
                    </div>
                </form>

                @if ($authUser->role_id == 2)
                    <small class="text-info mt-2">
                        <i class="fa fa-info-circle"></i> Note: Adding balance will deduct from your agent wallet
                        (₹{{ number_format($authUser->balance, 2) }}).
                    </small>
                @else
                    <small class="text-warning mt-2">
                        <i class="fa fa-shield-halved"></i> Admin Mode: Direct balance adjustment.
                    </small>
                @endif
            </div>
        @else
            <h5 class="text-warning fw-bold mb-2">Wallet Balance: ₹{{ number_format($user->balance, 2) }}</h5>
            <p class="text-muted small">You do not have permission to modify this wallet.</p>
        @endif

        @if (session('balance_message'))
            <div class="alert alert-info mt-2">{{ session('balance_message') }}</div>
        @endif

        <hr class="mt-4">

        {{-- ================ BALANCE LEDGER TABLE ================= --}}
        <h5 class="text-warning fw-bold">Balance Ledger</h5>

        <table class="table table-bordered table-dark">
            <thead>
                <tr>
                    <th>Sr No.</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Balance After</th>
                    <th>Date</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $index => $t)
                    <tr>
                        <td>{{ $transactions->firstItem() + $index }}</td>
                        <td>{{ ucfirst($t->type) }}</td>
                        <td>{{ $t->amount }}</td>
                        <td>{{ $t->balance_after }}</td>
                        <td>{{ $t->created_at->format('d-m-Y H:i') }}</td>
                        <td>{{ $t->remarks }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $transactions->links() }}

    </div>
@endsection
<script>
    document.querySelectorAll(".capitalize").forEach(input => {
        input.addEventListener("input", function() {
            this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1);
        });
    });

    document.querySelectorAll(".only-numbers").forEach(input => {
        input.addEventListener("keypress", function(e) {
            if (!/[0-9]/.test(e.key) || this.value.length >= 10) e.preventDefault();
        });
        input.addEventListener("paste", function(e) {
            let paste = (e.clipboardData || window.clipboardData).getData('text');
            if (!/^[0-9]{10}$/.test(paste)) e.preventDefault();
        });
    });
</script>
