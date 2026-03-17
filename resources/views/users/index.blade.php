@extends('layouts.app')

@section('content')
    <div class="container py-3">

        <div class="d-flex justify-content-between align-items-center mb-3">
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

        <div class="table-responsive">
            <table class="table table-dark table-striped table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Sr No.</th>
                        <th>Name & Username</th>
                        <th>Created By</th>
                        <th>Role</th>
                        <th>Balance</th>
                        {{-- New Performance Columns --}}
                        <th class="text-info">Total Play</th>
                        <th class="text-warning">Total Win</th>
                        <th>House P/L</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $u)
                        <tr>
                            <td>{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                            <td>
                                {{ $u->first_name }} {{ $u->last_name }}<br>
                                <small class="text-warning">{{ $u->username }}</small>
                            </td>
                            <td>
                                @if ($u->parent)
                                    <span class="badge bg-info text-dark">{{ $u->parent->username }}</span>
                                @else
                                    <span class="badge bg-secondary">System</span>
                                @endif
                            </td>
                            <td><span class="badge bg-outline-light">{{ $u->role->name ?? '' }}</span></td>
                            <td class="fw-bold text-success">₹{{ number_format($u->balance, 2) }}</td>

                            {{-- Financial Stats from Model Attributes/Scope --}}
                            <td class="text-info">₹{{ number_format($u->total_play ?? 0, 2) }}</td>
                            <td class="text-warning">₹{{ number_format(($u->total_win_points ?? 0) * 90, 2) }}</td>
                            <td class="{{ $u->house_profit >= 0 ? 'text-success' : 'text-danger' }}">
                                ₹{{ number_format($u->house_profit, 2) }}
                            </td>

                            <td>
                                <div class="d-flex gap-1">
                                    {{-- 👁️ Oversight Button --}}
                                    @if (auth()->user()->role_id == 1 || auth()->user()->role_id == 2)
                                        <a href="{{ route('users.oversight', $u->id) }}" class="btn btn-info btn-sm"
                                            title="Oversight Details">
                                            <i class="fa fa-eye text-white"></i>
                                        </a>
                                    @endif

                                    @if (auth()->user()->role_id == 1 || auth()->user()->role_id == 2)
                                        <a href="{{ route('users.edit', $u->id) }}" class="btn-lotto btn-sm">Edit</a>

                                        @php
                                            $canDelete = false;
                                            if (auth()->user()->role_id == 1) {
                                                $canDelete = true;
                                            } elseif (auth()->user()->role_id == 2 && $u->parent_id == auth()->id()) {
                                                $canDelete = true;
                                            }
                                        @endphp

                                        @if ($canDelete)
                                            <form action="{{ route('users.destroy', $u->id) }}" method="POST"
                                                class="d-inline deleteForm">
                                                @csrf @method('DELETE')
                                                <button type="button"
                                                    class="btn-lotto-red btn-sm deleteBtn">Delete</button>
                                            </form>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">No Action</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $users->links() }}
        </div>
    </div>

    {{-- Keep your existing delete script --}}
    <script>
        document.querySelectorAll('.deleteBtn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
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
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
