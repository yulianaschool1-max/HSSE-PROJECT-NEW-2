<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\ModuleManager;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Check if current user is authorized as Admin Management.
     */
    protected function authorizeAdminManagement(Request $request): ?Response
    {
        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->isActive() || !$currentUser->isAdminManagement()) {
            if ($request->wantsJson() || $request->ajax()) {
                abort(response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Menu Manajemen Pengguna hanya dapat diakses oleh Admin Management.',
                ], 403));
            }
            abort(403, 'Akses ditolak. Menu Manajemen Pengguna hanya dapat diakses oleh Admin Management.');
        }
        return null;
    }

    /**
     * Display a listing of users with search, filters, statistics, and real-time activity tracking.
     * Accessible exclusively by Admin Management.
     */
    public function index(Request $request): View|JsonResponse|RedirectResponse
    {
        $this->authorizeAdminManagement($request);

        $query = User::query();

        // Search: name or email
        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            });
        }

        // Filter: Role
        if ($request->filled('role') && $request->input('role') !== 'Semua') {
            $roleFilter = $request->input('role');
            if ($roleFilter === User::ROLE_ADMIN_MANAGEMENT || $roleFilter === 'Admin') {
                $query->whereIn('role', [User::ROLE_ADMIN_MANAGEMENT, 'Admin']);
            } elseif ($roleFilter === User::ROLE_USER || $roleFilter === 'User') {
                $query->whereIn('role', [User::ROLE_USER, 'User']);
            } else {
                $query->where('role', $roleFilter);
            }
        }

        // Filter: Status Akun
        if ($request->filled('status') && $request->input('status') !== 'Semua') {
            $query->where('status', $request->input('status'));
        }

        // Real-time activity tracking from sessions table
        $onlineThreshold = now()->subMinutes(5)->timestamp;
        $userSessions = DB::table('sessions')
            ->whereNotNull('user_id')
            ->select('user_id', DB::raw('MAX(last_activity) as last_activity'))
            ->groupBy('user_id')
            ->pluck('last_activity', 'user_id');

        // Filter: Status Aktivitas (Online / Offline)
        if ($request->filled('activity') && $request->input('activity') !== 'Semua') {
            $activity = $request->input('activity');
            $onlineUserIds = $userSessions->filter(fn ($ts) => $ts >= $onlineThreshold)->keys();

            if ($activity === 'Online') {
                $query->whereIn('id', $onlineUserIds);
            } elseif ($activity === 'Offline') {
                $query->whereNotIn('id', $onlineUserIds);
            }
        }

        // Statistics across all users
        $totalUsers = User::count();
        $totalAdminManagement = User::whereIn('role', [User::ROLE_ADMIN_MANAGEMENT, 'Admin'])->count();
        $totalAdminInventory = User::where('role', User::ROLE_ADMIN_INVENTORY)->count();
        $totalRegularUser = User::whereIn('role', [User::ROLE_USER, 'User'])->count();
        $totalActive = User::where('status', 'Aktif')->count();
        $totalInactive = User::where('status', 'Nonaktif')->count();
        $totalOnline = $userSessions->filter(fn ($ts) => $ts >= $onlineThreshold)->count();

        $stats = [
            'total' => $totalUsers,
            'admin' => $totalAdminManagement,
            'admin_management' => $totalAdminManagement,
            'admin_inventory' => $totalAdminInventory,
            'user' => $totalRegularUser,
            'active' => $totalActive,
            'inactive' => $totalInactive,
            'online' => $totalOnline,
        ];

        // Pagination
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [5, 10, 25, 50])) {
            $perPage = 10;
        }

        $users = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        // Attach dynamic activity data to each user in the paginated collection
        $users->getCollection()->transform(function ($u) use ($userSessions, $onlineThreshold) {
            $lastTs = $userSessions->get($u->id);
            $u->last_activity_timestamp = $lastTs;
            $u->is_online = $lastTs ? ($lastTs >= $onlineThreshold) : false;
            $u->last_seen = $lastTs ? Carbon::createFromTimestamp($lastTs)->locale('id')->diffForHumans() : 'Belum ada riwayat login';
            $u->last_seen_date = $lastTs ? Carbon::createFromTimestamp($lastTs)->format('d/m/Y H:i') . ' WIB' : '-';
            return $u;
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'users' => $users,
                'stats' => $stats,
            ]);
        }

        $allRoles = [
            User::ROLE_ADMIN_MANAGEMENT => 'Admin Management',
            User::ROLE_ADMIN_INVENTORY => 'Admin Inventory',
            User::ROLE_USER => 'User',
        ];
        $allStatuses = ['Aktif', 'Nonaktif'];
        $allActivities = ['Online', 'Offline'];
        $dropdownModules = ModuleManager::getDropdownOptions();

        return view('users.index', compact('users', 'stats', 'allRoles', 'allStatuses', 'allActivities', 'dropdownModules'));
    }

    /**
     * Show the form for creating a new user (Admin Management only).
     */
    public function create(Request $request): View
    {
        $this->authorizeAdminManagement($request);

        $allRoles = [
            User::ROLE_ADMIN_MANAGEMENT => 'Admin Management',
            User::ROLE_ADMIN_INVENTORY => 'Admin Inventory',
            User::ROLE_USER => 'User',
        ];
        $allStatuses = ['Aktif', 'Nonaktif'];

        return view('users.create', compact('allRoles', 'allStatuses'));
    }

    /**
     * Store a newly created user in storage (Admin Management only).
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorizeAdminManagement($request);

        if ($request->has('role')) {
            $r = strtolower(trim((string) $request->input('role')));
            if ($r === 'admin' || $r === 'admin management' || $r === 'admin_management') {
                $request->merge(['role' => User::ROLE_ADMIN_MANAGEMENT]);
            } elseif ($r === 'inventory' || $r === 'admin inventory' || $r === 'admin_inventory') {
                $request->merge(['role' => User::ROLE_ADMIN_INVENTORY]);
            } elseif ($r === 'user') {
                $request->merge(['role' => User::ROLE_USER]);
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:' . implode(',', [User::ROLE_ADMIN_MANAGEMENT, User::ROLE_ADMIN_INVENTORY, User::ROLE_USER]),
            'status' => 'required|string|in:Aktif,Nonaktif',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar di sistem.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role.required' => 'Role pengguna wajib dipilih.',
            'role.in' => 'Role pengguna harus Admin Management, Admin Inventory, atau User.',
            'status.required' => 'Status akun wajib dipilih.',
            'status.in' => 'Status akun harus Aktif atau Nonaktif.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'], // Eloquent hashed cast hashes once automatically
            'role' => $validated['role'],
            'status' => $validated['status'],
        ]);

        // Audit activity log
        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => Auth::user()?->name ?? 'System',
                'module' => 'User Management',
                'action' => 'Create User',
                'record_type' => 'User',
                'record_id' => $user->id,
                'description' => "Menambahkan pengguna baru: {$user->name} ({$user->email}) dengan role {$user->role_label}",
                'changes' => [
                    'after' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'status' => $user->status,
                    ],
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Ignore log error
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengguna baru berhasil ditambahkan.',
                'user' => $user,
            ], 201);
        }

        return redirect()->route('users.index')
            ->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    /**
     * Display the specified user details (Admin Management only).
     */
    public function show(Request $request, $id): View|JsonResponse|RedirectResponse
    {
        $this->authorizeAdminManagement($request);

        $user = User::find($id);

        if (!$user) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pengguna tidak ditemukan.',
                ], 404);
            }

            return redirect()->route('users.index')
                ->with('error', 'Data pengguna tidak ditemukan.');
        }

        // Attach live activity info from sessions table
        $onlineThreshold = now()->subMinutes(5)->timestamp;
        $lastTs = DB::table('sessions')
            ->where('user_id', $user->id)
            ->max('last_activity');

        $user->last_activity_timestamp = $lastTs;
        $user->is_online = $lastTs ? ($lastTs >= $onlineThreshold) : false;
        $user->last_seen = $lastTs ? Carbon::createFromTimestamp($lastTs)->locale('id')->diffForHumans() : 'Belum ada riwayat login';
        $user->last_seen_date = $lastTs ? Carbon::createFromTimestamp($lastTs)->format('d/m/Y H:i') . ' WIB' : '-';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'user' => $user,
            ]);
        }

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user (Admin Management only).
     */
    public function edit(Request $request, $id): View|RedirectResponse
    {
        $this->authorizeAdminManagement($request);

        $user = User::find($id);

        if (!$user) {
            return redirect()->route('users.index')
                ->with('error', 'Data pengguna tidak ditemukan.');
        }

        $allRoles = [
            User::ROLE_ADMIN_MANAGEMENT => 'Admin Management',
            User::ROLE_ADMIN_INVENTORY => 'Admin Inventory',
            User::ROLE_USER => 'User',
        ];
        $allStatuses = ['Aktif', 'Nonaktif'];

        return view('users.edit', compact('user', 'allRoles', 'allStatuses'));
    }

    /**
     * Update the specified user in storage (Admin Management only).
     */
    public function update(Request $request, $id): JsonResponse|RedirectResponse
    {
        $this->authorizeAdminManagement($request);

        $user = User::find($id);

        if (!$user) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pengguna tidak ditemukan.',
                ], 404);
            }

            return redirect()->route('users.index')
                ->with('error', 'Data pengguna tidak ditemukan.');
        }

        if ($request->has('role')) {
            $r = strtolower(trim((string) $request->input('role')));
            if ($r === 'admin' || $r === 'admin management' || $r === 'admin_management') {
                $request->merge(['role' => User::ROLE_ADMIN_MANAGEMENT]);
            } elseif ($r === 'inventory' || $r === 'admin inventory' || $r === 'admin_inventory') {
                $request->merge(['role' => User::ROLE_ADMIN_INVENTORY]);
            } elseif ($r === 'user') {
                $request->merge(['role' => User::ROLE_USER]);
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|in:' . implode(',', [User::ROLE_ADMIN_MANAGEMENT, User::ROLE_ADMIN_INVENTORY, User::ROLE_USER]),
            'status' => 'required|string|in:Aktif,Nonaktif',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'password.min' => 'Password baru minimal terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'role.required' => 'Role pengguna wajib dipilih.',
            'role.in' => 'Role pengguna harus Admin Management, Admin Inventory, atau User.',
            'status.required' => 'Status akun wajib dipilih.',
            'status.in' => 'Status akun harus Aktif atau Nonaktif.',
        ]);

        // Security Rule 1: Protect Primary Admin Management (admin@geodipa.co.id)
        if (strtolower($user->email) === 'admin@geodipa.co.id') {
            if ($validated['role'] !== User::ROLE_ADMIN_MANAGEMENT || $validated['status'] !== 'Aktif') {
                $errorMessage = 'Akun Administrator Utama (admin@geodipa.co.id) dilindungi dan tidak dapat dinonaktifkan atau diubah perannya.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $errorMessage], 422);
                }
                return back()->withInput()->with('error', $errorMessage);
            }
        }

        // Security Rule 2: Prevent changing role or deactivating the LAST active Admin Management
        if ($user->isAdminManagement() && $user->isActive()) {
            $isDemoting = $validated['role'] !== User::ROLE_ADMIN_MANAGEMENT;
            $isDeactivating = $validated['status'] === 'Nonaktif';

            if ($isDemoting || $isDeactivating) {
                $activeAdminCount = User::whereIn('role', [User::ROLE_ADMIN_MANAGEMENT, 'Admin'])->where('status', 'Aktif')->count();
                if ($activeAdminCount <= 1) {
                    $errorMessage = 'Tidak dapat mengubah role atau menonaktifkan satu-satunya Admin Management aktif pada sistem.';
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => $errorMessage,
                        ], 422);
                    }

                    return back()->withInput()->with('error', $errorMessage);
                }
            }
        }

        // Apply changes
        $oldData = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
        ];

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->status = $validated['status'];

        $passwordChanged = false;
        if (!empty($validated['password'])) {
            $user->password = $validated['password'];
            $passwordChanged = true;
        }

        $user->save();

        // Audit activity log
        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => Auth::user()?->name ?? 'System',
                'module' => 'User Management',
                'action' => 'Update User',
                'record_type' => 'User',
                'record_id' => $user->id,
                'description' => "Memperbarui data pengguna: {$user->name} ({$user->email})" . ($passwordChanged ? ' (Password diperbarui)' : ''),
                'changes' => [
                    'before' => $oldData,
                    'after' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'status' => $user->status,
                    ],
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Ignore
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data pengguna berhasil diperbarui.',
                'user' => $user->fresh(),
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified user from storage (Admin Management only).
     */
    public function destroy(Request $request, $id): JsonResponse|RedirectResponse
    {
        $this->authorizeAdminManagement($request);

        $user = User::find($id);

        if (!$user) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pengguna tidak ditemukan.',
                ], 404);
            }

            return redirect()->route('users.index')
                ->with('error', 'Data pengguna tidak ditemukan.');
        }

        // Security Rule 1: Protect Primary Admin Management (admin@geodipa.co.id)
        if (strtolower($user->email) === 'admin@geodipa.co.id') {
            $errorMessage = 'Akun Administrator Utama (admin@geodipa.co.id) dilindungi dan tidak dapat dihapus.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $errorMessage], 422);
            }
            return redirect()->route('users.index')->with('error', $errorMessage);
        }

        // Security Rule 2: Prevent user from deleting their own account
        if (Auth::check() && $user->id === Auth::id()) {
            $errorMessage = 'Anda tidak dapat menghapus akun Anda sendiri.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], 422);
            }

            return redirect()->route('users.index')->with('error', $errorMessage);
        }

        // Security Rule 3: Prevent deleting the last Admin Management account
        if ($user->isAdminManagement()) {
            $totalAdmins = User::whereIn('role', [User::ROLE_ADMIN_MANAGEMENT, 'Admin'])->count();
            if ($totalAdmins <= 1) {
                $errorMessage = 'Tidak dapat menghapus akun Admin Management terakhir pada sistem.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                    ], 422);
                }

                return redirect()->route('users.index')->with('error', $errorMessage);
            }
        }

        $userName = $user->name;
        $userEmail = $user->email;
        $userRole = $user->role_label;
        $user->delete();

        // Audit activity log
        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => Auth::user()?->name ?? 'System',
                'module' => 'User Management',
                'action' => 'Delete User',
                'record_type' => 'User',
                'record_id' => $id,
                'description' => "Menghapus akun pengguna: {$userName} ({$userEmail}) - {$userRole}",
                'changes' => [
                    'before' => [
                        'name' => $userName,
                        'email' => $userEmail,
                        'role' => $user->role,
                        'status' => $user->status,
                    ],
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Ignore
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengguna berhasil dihapus.',
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    /**
     * Display activity history for all users and all modules (Admin Management only).
     */
    public function allActivities(Request $request): JsonResponse|View|RedirectResponse
    {
        $this->authorizeAdminManagement($request);

        $query = ActivityLog::with('user');

        // Filter: Pengguna tertentu
        if ($request->filled('user_id') && $request->input('user_id') !== 'Semua') {
            $selectedUserId = $request->input('user_id');
            $selectedUser = User::find($selectedUserId);
            $query->where(function ($q) use ($selectedUserId, $selectedUser) {
                $q->where('user_id', $selectedUserId);
                if ($selectedUser) {
                    $q->orWhere('user_name', $selectedUser->name);
                }
            });
        }

        // Filter: Modul
        if ($request->filled('module') && $request->input('module') !== 'Semua') {
            $resolved = ModuleManager::resolveLogNames($request->input('module'));
            if (!empty($resolved)) {
                $query->whereIn('module', $resolved);
            }
        }

        // Filter: Jenis Aksi (Action)
        if ($request->filled('action') && $request->input('action') !== 'Semua') {
            $act = $request->input('action');
            if (in_array(strtoupper($act), ['TAMBAH', 'CREATE', 'CREATE USER'])) {
                $query->whereIn('action', ['TAMBAH', 'Create User', 'TAMBAH_CATATAN']);
            } elseif (in_array(strtoupper($act), ['UBAH', 'UPDATE', 'UPDATE USER'])) {
                $query->whereIn('action', ['UBAH', 'Update User', 'UBAH_CATATAN']);
            } elseif (in_array(strtoupper($act), ['HAPUS', 'DELETE', 'DELETE USER'])) {
                $query->whereIn('action', ['HAPUS', 'Delete User', 'HAPUS_CATATAN']);
            } else {
                $query->where('action', $act);
            }
        }

        // Filter: Rentang Tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        // Filter: Pencarian teks
        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('description', 'like', "%{$term}%")
                  ->orWhere('user_name', 'like', "%{$term}%")
                  ->orWhere('module', 'like', "%{$term}%")
                  ->orWhere('action', 'like', "%{$term}%");
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50, 100]) ? $perPage : 10;

        $logs = $query->orderBy('created_at', 'desc')->orderBy('id', 'desc')->paginate($perPage);

        $totalLogs = ActivityLog::count();
        $todayLogs = ActivityLog::whereDate('created_at', Carbon::today())->count();
        $modulesCount = ActivityLog::distinct('module')->count('module');
        $usersCount = ActivityLog::whereNotNull('user_id')->distinct('user_id')->count('user_id');

        $dropdownModules = ModuleManager::getDropdownOptions();
        $availableModules = collect($dropdownModules)->pluck('label')->values()->all();
        $availableActions = ActivityLog::select('action')->distinct()->pluck('action');
        $availableUsers = User::select('id', 'name', 'email', 'role')->orderBy('name')->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'logs' => $logs,
                'stats' => [
                    'total' => $totalLogs,
                    'today' => $todayLogs,
                    'modules_count' => $modulesCount,
                    'users_count' => $usersCount,
                ],
                'available_modules' => $availableModules,
                'dropdown_modules' => $dropdownModules,
                'available_actions' => $availableActions,
                'available_users' => $availableUsers,
            ]);
        }

        return view('users.activities', compact(
            'logs',
            'totalLogs',
            'todayLogs',
            'modulesCount',
            'usersCount',
            'availableModules',
            'dropdownModules',
            'availableActions',
            'availableUsers'
        ));
    }

    /**
     * Display activity history for a specific user (Admin Management only).
     */
    public function activities(Request $request, $id): JsonResponse|View|RedirectResponse
    {
        $this->authorizeAdminManagement($request);

        $user = User::find($id);

        if (!$user) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pengguna tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('users.index')->with('error', 'Data pengguna tidak ditemukan.');
        }

        $query = ActivityLog::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('user_name', $user->name);
        });

        if ($request->filled('module') && $request->input('module') !== 'Semua') {
            $resolved = ModuleManager::resolveLogNames($request->input('module'));
            if (!empty($resolved)) {
                $query->whereIn('module', $resolved);
            }
        }

        if ($request->filled('action') && $request->input('action') !== 'Semua') {
            $act = $request->input('action');
            if (in_array(strtoupper($act), ['TAMBAH', 'CREATE', 'CREATE USER'])) {
                $query->whereIn('action', ['TAMBAH', 'Create User', 'TAMBAH_CATATAN']);
            } elseif (in_array(strtoupper($act), ['UBAH', 'UPDATE', 'UPDATE USER'])) {
                $query->whereIn('action', ['UBAH', 'Update User', 'UBAH_CATATAN']);
            } elseif (in_array(strtoupper($act), ['HAPUS', 'DELETE', 'DELETE USER'])) {
                $query->whereIn('action', ['HAPUS', 'Delete User', 'HAPUS_CATATAN']);
            } else {
                $query->where('action', $act);
            }
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('description', 'like', "%{$term}%")
                  ->orWhere('module', 'like', "%{$term}%")
                  ->orWhere('action', 'like', "%{$term}%");
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50, 100]) ? $perPage : 10;

        $logs = $query->orderBy('created_at', 'desc')->orderBy('id', 'desc')->paginate($perPage);

        $totalLogs = ActivityLog::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('user_name', $user->name);
        })->count();

        $modulesCount = ActivityLog::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('user_name', $user->name);
        })->distinct('module')->count('module');

        $dropdownModules = ModuleManager::getDropdownOptions();
        $availableModules = collect($dropdownModules)->pluck('label')->values()->all();
        $availableActions = ActivityLog::select('action')->distinct()->pluck('action');
        $availableUsers = User::select('id', 'name', 'email', 'role')->orderBy('name')->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'role_label' => $user->role_label,
                    'status' => $user->status,
                ],
                'logs' => $logs,
                'stats' => [
                    'total' => $totalLogs,
                    'modules_count' => $modulesCount,
                ],
                'available_modules' => $availableModules,
                'dropdown_modules' => $dropdownModules,
                'available_actions' => $availableActions,
                'available_users' => $availableUsers,
            ]);
        }

        return view('users.activities', compact('user', 'logs', 'totalLogs', 'modulesCount', 'availableModules', 'dropdownModules', 'availableActions', 'availableUsers'));
    }
}
