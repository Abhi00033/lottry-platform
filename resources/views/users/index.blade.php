@extends('layouts.app')

@section('content')
    <div class="container py-3">

        {{-- ===== Top Header ===== --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <h3 class="fw-bold text-warning mb-0">Users</h3>
                <span class="badge bg-dark border border-warning p-2" style="font-size: 1rem;">
                    My Balance: <span class="text-warning">₹{{ number_format(auth()->user()->balance, 2) }}</span>
                </span>
            </div>

            @if (auth()->user()->role_id == 1 || auth()->user()->role_id == 2)
                <a href="{{ route('users.create') }}" class="btn-lotto-green btn-boxed">
                    + Register User
                </a>
            @endif
        </div>

        {{-- ===== Search & Filter Bar ===== --}}
        <div class="card bg-dark border-secondary mb-3 px-3 py-2">
            <form action="{{ route('users.index') }}" method="GET" id="filterForm">
                <div class="row g-2 align-items-end">

                    {{-- Search --}}
                    <div class="col-md-5 col-sm-12">
                        <label class="text-white-50 small mb-1">
                            <i class="fas fa-search me-1 text-warning"></i> Search
                        </label>
                        <input type="text" name="search"
                            class="form-control form-control-sm bg-dark text-white border-secondary"
                            placeholder="Name, username, mobile, unique ID..." value="{{ $search ?? '' }}">
                    </div>

                    {{-- Role Filter (Admin only) --}}
                    @if (auth()->user()->role_id == 1)
                        <div class="col-md-3 col-sm-6">
                            <label class="text-white-50 small mb-1">
                                <i class="fas fa-filter me-1 text-warning"></i> Role
                            </label>
                            <select name="role" class="form-select form-select-sm bg-dark text-white border-secondary"
                                onchange="document.getElementById('filterForm').submit()">
                                <option value="">All Roles</option>
                                @foreach ($roles as $r)
                                    <option value="{{ $r->id }}" {{ ($role ?? '') == $r->id ? 'selected' : '' }}>
                                        {{ $r->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Buttons --}}
                    <div class="col-md-4 col-sm-6 d-flex gap-2">
                        <button type="submit" class="btn btn-warning btn-sm fw-bold px-3">
                            <i class="fas fa-search me-1"></i> Search
                        </button>
                        @if (!empty($search) || !empty($role))
                            <a href="{{ route('users.index') }}" class="btn btn-outline-danger btn-sm px-3">
                                <i class="fas fa-times me-1"></i> Clear
                            </a>
                        @endif
                    </div>

                </div>
            </form>

            {{-- Result count --}}
            @if (!empty($search) || !empty($role))
                <div class="mt-2">
                    <small class="text-white-50">
                        Found <strong class="text-warning">{{ $users->total() }}</strong> result(s)
                        @if (!empty($search))
                            for "<strong class="text-white">{{ $search }}</strong>"
                        @endif
                        @if (!empty($role))
                            in role <strong class="text-white">{{ $roles->find($role)?->name }}</strong>
                        @endif
                    </small>
                </div>
            @endif
        </div>

        {{-- ===== Table ===== --}}
        <div class="table-responsive">
            <table class="table table-dark table-bordered table-hover align-middle text-center mb-0">
                <thead style="background-color: #1e1e2e;">
                    <tr>
                        <th class="text-warning">Sr NO.</th>
                        <th class="text-warning">Name & Username</th>
                        <th class="text-warning">Created By</th>
                        <th class="text-warning">Role</th>
                        <th class="text-warning">Balance</th>
                        <th class="text-info">Total Play</th>
                        <th class="text-success">Total Win</th>
                        <th class="text-warning">House P/L</th>
                        <th class="text-warning">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $u)
                        <tr>
                            <td class="text-secondary">
                                {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                            </td>
                            <td class="text-start">
                                <span class="fw-bold text-white">{{ $u->first_name }} {{ $u->last_name }}</span><br>
                                <small class="text-warning">{{ $u->username }}</small>
                            </td>
                            <td>
                                @if ($u->parent)
                                    <span class="badge bg-info text-dark">{{ $u->parent->username }}</span>
                                @else
                                    <span class="badge bg-secondary">System</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $u->role->name ?? '—' }}</span>
                            </td>
                            <td class="fw-bold text-success">₹{{ number_format($u->balance, 2) }}</td>
                            <td class="text-info">₹{{ number_format($u->total_play ?? 0, 2) }}</td>
                            <td class="text-success">₹{{ number_format(($u->total_win_points ?? 0) * 90, 2) }}</td>
                            <td class="{{ ($u->house_profit ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                ₹{{ number_format($u->house_profit ?? 0, 2) }}
                            </td>
                            <td>
                                <div class="d-flex gap-1 justify-content-center flex-wrap">

                                    @if (auth()->user()->role_id == 1)
                                        <a href="{{ route('users.oversight', $u->id) }}" class="btn btn-info btn-sm"
                                            title="Oversight">
                                            <i class="fa fa-eye text-white"></i>
                                        </a>
                                    @endif

                                    @if (auth()->user()->role_id == 1 || auth()->user()->role_id == 2)
                                        <a href="{{ route('users.edit', $u->id) }}" class="btn-lotto btn-sm">Edit</a>

                                        @php
                                            $canDelete =
                                                auth()->user()->role_id == 1 ||
                                                (auth()->user()->role_id == 2 && $u->parent_id == auth()->id());
                                        @endphp

                                        @if ($canDelete)
                                            <form action="{{ route('users.destroy', $u->id) }}" method="POST"
                                                class="d-inline deleteForm">
                                                @csrf @method('DELETE')
                                                <button type="button" class="btn-lotto-red btn-sm deleteBtn">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">No Action</span>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="fas fa-users fa-2x mb-2 d-block text-secondary"></i>
                                No users found
                                @if (!empty($search))
                                    for "<strong class="text-warning">{{ $search }}</strong>"
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ===== Pagination ===== --}}
        @if ($users->hasPages())
            <div class="mt-3 d-flex justify-content-end">
                {{ $users->links() }}
            </div>
        @endif

    </div>

    <script>
        document.querySelectorAll('.deleteBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                let form = this.closest('form');
                Swal.fire({
                    title: "Are you sure?",
                    text: "User will be deleted!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, delete"
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });
    </script>
@endsection
