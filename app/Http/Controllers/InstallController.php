<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Installer\InstallationService;
use Illuminate\Support\Facades\Session;

class InstallController extends Controller
{
    protected $installationService;

    public function __construct(InstallationService $installationService)
    {
        $this->installationService = $installationService;
    }

    public function index()
    {
        return view('vendor.installer.welcome');
    }

    public function requirements()
    {
        $requirements = $this->installationService->checkRequirements();
        $allPassed = !in_array(false, array_column($requirements, 'check'));

        return view('vendor.installer.requirements', compact('requirements', 'allPassed'));
    }

    public function database()
    {
        return view('vendor.installer.database');
    }

    public function postDatabase(Request $request)
    {
        $request->validate([
            'db_host' => 'required',
            'db_name' => 'required',
            'db_user' => 'required',
            'db_pass' => 'nullable',
        ]);

        $config = [
            'host' => $request->db_host,
            'database' => $request->db_name,
            'username' => $request->db_user,
            'password' => $request->db_pass,
        ];

        if (!$this->installationService->testDatabaseConnection($config)) {
            return back()->withErrors(['connection' => 'Could not connect to the database. Please check your credentials.'])->withInput();
        }

        Session::put('installer.database', $config);

        return redirect()->route('install.environment');
    }

    public function environment()
    {
        return view('vendor.installer.environment');
    }

    public function postEnvironment(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'app_frontend_url' => 'required|url',
            'app_admin_url' => 'required|url',
        ]);

        $dbConfig = Session::get('installer.database');
        if (!$dbConfig) {
            return redirect()->route('install.database');
        }

        $envData = [
            'APP_NAME' => $request->app_name,
            'APP_URL' => $request->app_url,
            'APP_FRONTEND_URL' => $request->app_frontend_url,
            'APP_ADMIN_URL' => $request->app_admin_url,
            'SANCTUM_STATEFUL_DOMAINS' => parse_url($request->app_frontend_url, PHP_URL_HOST) . ':' . (parse_url($request->app_frontend_url, PHP_URL_PORT) ?: (parse_url($request->app_frontend_url, PHP_URL_SCHEME) === 'https' ? '443' : '80')) . ',' .
                                          parse_url($request->app_admin_url, PHP_URL_HOST) . ':' . (parse_url($request->app_admin_url, PHP_URL_PORT) ?: (parse_url($request->app_admin_url, PHP_URL_SCHEME) === 'https' ? '443' : '80')),
            'DB_HOST' => $dbConfig['host'],
            'DB_DATABASE' => $dbConfig['database'],
            'DB_USERNAME' => $dbConfig['username'],
            'DB_PASSWORD' => $dbConfig['password'],
            'PURCHASE_CODE' => $request->purchase_code,
            'JWT_SECRET' => $request->jwt_secret ?? 'your_jwt_token',
        ];

        $this->installationService->updateEnv($envData);

        return redirect()->route('install.migration');
    }

    public function migration()
    {
        return view('vendor.installer.migration');
    }

    public function runMigration()
    {
        $result = $this->installationService->setupDatabase();
        if (!$result['status']) {
            return response()->json(['status' => false, 'message' => $result['message']], 500);
        }

        return response()->json(['status' => true]);
    }

    public function admin()
    {
        return view('vendor.installer.admin');
    }

    public function postAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!$this->installationService->createAdmin($request->only('name', 'email', 'password'))) {
            return back()->withErrors(['admin' => 'Failed to create admin account.'])->withInput();
        }

        return redirect()->route('install.finish');
    }

    public function finish()
    {
        $this->installationService->finalizeInstallation();
        Session::forget('installer');
        
        return view('vendor.installer.finish');
    }
}
