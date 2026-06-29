<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\GeneralStatus;
use App\Models\UserBalanceTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

    public function index(Request $request): View
    {
        $auth   = auth()->user();
        $search = $request->get('search');
        $role   = $request->get('role');

        $query = User::with(['role', 'status', 'parent'])->withOversightStats();

        // Role-based scope
        if ($auth->role_id == 1) {
            // Admin sees everyone — apply optional role filter
            if ($role) {
                $query->where('role_id', $role);
            }
        } elseif ($auth->role_id == 2) {
            // Agent sees only their own retailers
            $query->where('parent_id', $auth->id);
        } else {
            // Retailer sees only themselves
            $query->where('id', $auth->id);
        }

        // Search: name, username, mobile, unique_id
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name',  'like', "%{$search}%")
                    ->orWhere('username',   'like', "%{$search}%")
                    ->orWhere('mobile',     'like', "%{$search}%")
                    ->orWhere('unique_id',  'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('id', 'DESC')
            ->paginate(20)
            ->appends($request->only(['search', 'role']));

        // Pass roles list for filter dropdown (admin only)
        $roles = Role::all();

        return view('users.index', compact('users', 'roles', 'search', 'role'));
    }

    public function oversight(Request $request, $id): View
    {
        $auth = auth()->user();
        $user = User::with(['role', 'parent'])->findOrFail($id);

        if ($auth->role_id == 2 && $user->parent_id !== $auth->id) {
            abort(403);
        }
        // Add this line right after:
        if ($auth->role_id == 3 && $user->id !== $auth->id) {
            abort(403);
        }

        $startDate = $request->input('start_date', now()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $search = $request->input('search');
        $drawTimeFilter = $request->input('draw_time'); // New Filter

        $backUrl = url()->previous();
        if (str_contains($backUrl, 'oversight')) {
            $backUrl = route('users.index');
        }

        // Base Query for the selected user and dates
        $betQuery = $user->bets()
            ->whereDate('draw_time', '>=', $startDate)
            ->whereDate('draw_time', '<=', $endDate);

        // Get list of all available draw times in this date range for the dropdown
        $availableDrawTimes = (clone $betQuery)
            ->select('draw_time')
            ->groupBy('draw_time')
            ->orderBy('draw_time', 'desc')
            ->get();

        // Apply Draw Time Filter if selected
        $betQuery->when($drawTimeFilter, function ($query, $time) {
            return $query->where('draw_time', $time);
        });

        // Filtered Query for the Table search
        $tableQuery = (clone $betQuery)->when($search, function ($query, $search) {
            return $query->where(function ($q) use ($search) {
                $q->where('number', 'LIKE', "%{$search}%")
                    ->orWhere('series_group', 'LIKE', "%{$search}%")
                    ->orWhere('status', 'LIKE', "%{$search}%");
            });
        });

        // 1. Aggregations (Reflects the filters applied)
        $totalBets = (clone $betQuery)->count();
        $totalQty = (clone $betQuery)->sum('qty');
        $totalPlay = (clone $betQuery)->sum('total_amount');

        $totalWinPoints = (clone $betQuery)->where('status', 'won')->sum('points');
        $totalWinAmount = $totalWinPoints * 90;

        $commissionRate   = (float) ($user->commision ?? 0);
        $commissionAmount = $totalPlay * ($commissionRate / 100);

        $periodProfit = $totalPlay - $totalWinAmount - $commissionAmount;

        // 2. Heat Map: Top 10 Heaviest Numbers for the specific Draw or Period
        $topNumbers = (clone $betQuery)
            ->select('number', 'series_group', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(total_amount) as total_spent'))
            ->groupBy('number', 'series_group')
            ->orderBy('total_spent', 'DESC')
            ->take(10)
            ->get();

        // 3. Paginated Results
        $recentBets = $tableQuery->latest()->paginate(50)->withQueryString();

        return view('users.oversight', compact(
            'user',
            'totalBets',
            'totalQty',
            'recentBets',
            'startDate',
            'endDate',
            'periodProfit',
            'commissionAmount',  // ← new
            'totalWinAmount',
            'totalPlay',
            'backUrl',
            'search',
            'topNumbers',
            'availableDrawTimes',
            'drawTimeFilter'
        ));
    }

    // 🧾 Create Form
    public function create(): View
    {
        $auth = auth()->user();

        if ($auth->role_id == 1) {
            // Admin can create Agents and Retailers
            $roles = Role::whereIn('id', [2, 3])->get();
        } else {
            // Agents can ONLY create Retailers
            $roles = Role::where('id', 3)->get();
        }

        $statuses = GeneralStatus::all();
        return view('users.create', compact('roles', 'statuses'));
    }

    // 🟢 Store New User
    public function store(Request $request): RedirectResponse
    {
        // validate input
        $validated = $request->validate([
            'unique_id' => 'nullable|string|max:10|unique:users,unique_id',
            'first_name' => 'required|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => 'nullable|email|max:255|unique:users,email',
            'mobile'     => 'nullable|digits:10|unique:users,mobile',   // EXACTLY 10 digits
            'username'   => 'required|string|max:100|unique:users,username',
            'password'   => 'required|confirmed|min:6',
            'role_id' => ['required', 'integer', Rule::in(
                auth()->user()->role_id == 1 ? [2, 3] : [3]
            )],
            'commision'          => 'nullable|numeric|min:0|max:100',
            'general_status_id' => 'required|integer',
        ]);

        // Auto-generate unique_id
        $unique = $request->unique_id ?: strtoupper(substr(md5(uniqid()), 0, 6));


        // Create User
        $user = User::create([
            'parent_id'         => auth()->id(),
            'unique_id'         => $unique,
            'role_id'           => $request->role_id,
            'general_status_id' => $request->general_status_id,
            'first_name'        => $request->first_name,
            'last_name'         => $request->last_name,
            'email'             => $request->email,
            'mobile'            => $request->mobile,
            'username'          => $request->username,
            'password'          => Hash::make($request->password),
            'balance'           => 0,
            'commision'          => $request->commision ?? 0,
        ]);

        // Save starting transaction
        UserBalanceTransaction::create([
            'user_id'       => $user->id,
            'type'          => 'initial',
            'amount'        => 0,
            'balance_after' => 0,
            'remarks'       => 'Initial account creation'
        ]);

        return redirect()->route('users.index')->with('success', 'User Created Successfully!');
    }

    // 🟡 Edit
    public function edit($id): View
    {
        $auth = auth()->user();
        $user = User::findOrFail($id);

        if ($auth->role_id == 2 && $user->parent_id !== $auth->id) abort(403);
        if ($auth->role_id == 3 && $user->id !== $auth->id) abort(403);

        // ✅ Admin editing another admin → show all roles including admin
        // Everyone else → exclude role 1 (admin) from options
        if ($auth->role_id == 1 && $user->role_id == 1) {
            $roles = Role::all();
        } else {
            $roles = Role::where('id', '!=', 1)->get();
        }

        $statuses     = GeneralStatus::all();
        $transactions = UserBalanceTransaction::where('user_id', $id)->latest()->paginate(15)->withQueryString();

        return view('users.edit', compact('user', 'roles', 'statuses', 'transactions'));
    }

    // 🛠 Update
    public function update(Request $request, $id): RedirectResponse
    {
        $auth = auth()->user();
        $user = User::findOrFail($id);

        // Add this
        if ($auth->role_id == 2 && $user->parent_id !== $auth->id) abort(403);
        if ($auth->role_id == 3 && $user->id !== $auth->id) abort(403);

        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'mobile'     => 'nullable|digits_between:8,15|unique:users,mobile,' . $user->id,
            'username'   => 'required|string|max:100|unique:users,username,' . $user->id,
            'commision' => 'nullable|numeric|min:0|max:100',
            'role_id' => ['required', 'integer', Rule::in(
                auth()->user()->role_id == 1 ? [1, 2, 3] : [3]
            )],
            'general_status_id' => 'required|integer',
        ]);

        $user->update($request->only(['first_name', 'last_name', 'email', 'mobile', 'username', 'commision', 'role_id', 'general_status_id']));

        return redirect()->route('users.index')->with('success', 'User Updated Successfully!');
    }

    public function balanceUpdate(Request $request, $id): RedirectResponse
    {
        // ✅ CHANGE 1 — wrap everything in a transaction
        return DB::transaction(function () use ($request, $id) {

            $targetUser = User::findOrFail($id);
            $authUser   = auth()->user();
            $amount     = abs($request->amount);
            $action     = $request->action;

            if ($amount <= 0) {
                return back()->with('error', 'Enter a valid amount!');
            }

            if ($authUser->role_id == 2) {
                if ($authUser->id == $targetUser->id) {
                    return back()->with('error', 'Agents cannot update their own wallet balance.');
                }
                if ($targetUser->parent_id !== $authUser->id) {
                    return back()->with('error', 'Unauthorized access.');
                }

                if ($action === 'add') {
                    if ($authUser->balance < $amount) {
                        return back()->with('error', 'Insufficient balance in your agent wallet!');
                    }
                    $authUser->decrement('balance', $amount);
                    $targetUser->increment('balance', $amount);
                    $authUser->refresh(); // ✅ CHANGE 2

                    UserBalanceTransaction::create([
                        'user_id'       => $authUser->id,
                        'type'          => 'transfer_out',
                        'amount'        => $amount,
                        'balance_after' => $authUser->balance, // now fresh
                        'remarks'       => 'Sent to Retailer: ' . $targetUser->username,
                    ]);
                } else {
                    if ($targetUser->balance < $amount) {
                        return back()->with('error', 'Retailer has insufficient balance.');
                    }
                    $targetUser->decrement('balance', $amount);
                    $authUser->increment('balance', $amount);
                    $authUser->refresh(); // ✅ CHANGE 3

                    UserBalanceTransaction::create([
                        'user_id'       => $authUser->id,
                        'type'          => 'transfer_in',
                        'amount'        => $amount,
                        'balance_after' => $authUser->balance, // now fresh
                        'remarks'       => 'Recovered from Retailer: ' . $targetUser->username,
                    ]);
                }
            } elseif ($authUser->role_id == 1) {
                if ($action === 'add') {
                    $targetUser->increment('balance', $amount);
                } else {
                    if ($targetUser->balance < $amount) {
                        return back()->with('error', 'Insufficient balance to deduct.');
                    }
                    $targetUser->decrement('balance', $amount);
                }
            } else {
                return back()->with('error', 'Unauthorized role.');
            }

            $targetUser->refresh(); // ✅ CHANGE 4 — outside both role blocks, covers agent + admin

            UserBalanceTransaction::create([
                'user_id'       => $targetUser->id,
                'type'          => ($action === 'add') ? 'credit' : 'debit',
                'amount'        => $amount,
                'balance_after' => $targetUser->balance, // now fresh
                'remarks'       => $request->remarks ?: 'Balance adjusted by ' . $authUser->username,
            ]);

            return back()->with('success', 'Balance updated successfully.');
        });
    }


    // 🗑 Delete
    public function destroy($id): RedirectResponse
    {
        try {
            $u = User::findOrFail($id);
            $authUser = auth()->user();

            // --- SECURITY CHECK ---
            $allowed = false;

            // Admin can delete anyone
            if ($authUser->role_id == 1) {
                $allowed = true;
            }
            // Agent can only delete their own Retailers
            elseif ($authUser->role_id == 2 && $u->parent_id == $authUser->id) {
                $allowed = true;
            }

            if (!$allowed) {
                return redirect()->route('users.index')->with('error', 'Unauthorized! You can only delete your own retailers.');
            }

            $u->delete();
            return redirect()->route('users.index')->with('success', 'User Deleted Successfully!');
        } catch (\Exception $e) {
            return redirect()->route('users.index')->with('error', 'Failed to delete user!');
        }
    }
}
