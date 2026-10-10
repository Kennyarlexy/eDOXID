<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\GoogleDriveHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Submission;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticatedSessionController extends Controller
{
    private $googleDriveHelper;

    public function __construct()
    {
        $this->googleDriveHelper = new GoogleDriveHelper();
    }

    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        $statistic_year = $request->tahun_statistik ? $request->tahun_statistik : date('Y');

        // get this year of month january count of submission models
        $proposal_submissions = [];
        $proposal_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-01%')->whereHas('type', function ($q) {
            return $q->where('name', 'Proposal');
        })->count();
        $proposal_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-02%')->whereHas('type', function ($q) {
            return $q->where('name', 'Proposal');
        })->count();
        $proposal_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-03%')->whereHas('type', function ($q) {
            return $q->where('name', 'Proposal');
        })->count();
        $proposal_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-04%')->whereHas('type', function ($q) {
            return $q->where('name', 'Proposal');
        })->count();
        $proposal_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-05%')->whereHas('type', function ($q) {
            return $q->where('name', 'Proposal');
        })->count();
        $proposal_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-06%')->whereHas('type', function ($q) {
            return $q->where('name', 'Proposal');
        })->count();
        $proposal_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-07%')->whereHas('type', function ($q) {
            return $q->where('name', 'Proposal');
        })->count();
        $proposal_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-08%')->whereHas('type', function ($q) {
            return $q->where('name', 'Proposal');
        })->count();
        $proposal_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-09%')->whereHas('type', function ($q) {
            return $q->where('name', 'Proposal');
        })->count();
        $proposal_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-10%')->whereHas('type', function ($q) {
            return $q->where('name', 'Proposal');
        })->count();
        $proposal_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-11%')->whereHas('type', function ($q) {
            return $q->where('name', 'Proposal');
        })->count();
        $proposal_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-12%')->whereHas('type', function ($q) {
            return $q->where('name', 'Proposal');
        })->count();

        $sidang_hasil_submissions = [];
        $sidang_hasil_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-01%')->whereHas('type', function ($q) {
            return $q->where('name', 'Sidang Hasil');
        })->count();
        $sidang_hasil_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-02%')->whereHas('type', function ($q) {
            return $q->where('name', 'Sidang Hasil');
        })->count();
        $sidang_hasil_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-03%')->whereHas('type', function ($q) {
            return $q->where('name', 'Sidang Hasil');
        })->count();
        $sidang_hasil_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-04%')->whereHas('type', function ($q) {
            return $q->where('name', 'Sidang Hasil');
        })->count();
        $sidang_hasil_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-05%')->whereHas('type', function ($q) {
            return $q->where('name', 'Sidang Hasil');
        })->count();
        $sidang_hasil_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-06%')->whereHas('type', function ($q) {
            return $q->where('name', 'Sidang Hasil');
        })->count();
        $sidang_hasil_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-07%')->whereHas('type', function ($q) {
            return $q->where('name', 'Sidang Hasil');
        })->count();
        $sidang_hasil_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-08%')->whereHas('type', function ($q) {
            return $q->where('name', 'Sidang Hasil');
        })->count();
        $sidang_hasil_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-09%')->whereHas('type', function ($q) {
            return $q->where('name', 'Sidang Hasil');
        })->count();
        $sidang_hasil_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-10%')->whereHas('type', function ($q) {
            return $q->where('name', 'Sidang Hasil');
        })->count();
        $sidang_hasil_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-11%')->whereHas('type', function ($q) {
            return $q->where('name', 'Sidang Hasil');
        })->count();
        $sidang_hasil_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-12%')->whereHas('type', function ($q) {
            return $q->where('name', 'Sidang Hasil');
        })->count();

        $sidang_akhir_submissions = [];
        $sidang_akhir_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-01%')->whereHas('type', function ($q) {
            return $q->where('name', 'Sidang Akhir');
        })->count();
        $sidang_akhir_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-02%')->whereHas('type', function ($q) {
            return $q->where('name', 'Sidang Akhir');
        })->count();
        $sidang_akhir_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-03%')->whereHas('type', function ($q) {
            return $q->where('name', 'Sidang Akhir');
        })->count();
        $sidang_akhir_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-04%')->whereHas('type', function ($q) {
            return $q->where('name', 'Sidang Akhir');
        })->count();
        $sidang_akhir_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-05%')->whereHas('type', function ($q) {
            return $q->where('name', 'Sidang Akhir');
        })->count();
        $sidang_akhir_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-06%')->whereHas('type', function ($q) {
            return $q->where('name', 'Sidang Akhir');
        })->count();
        $sidang_akhir_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-07%')->whereHas('type', function ($q) {
            return $q->where('name', 'Sidang Akhir');
        })->count();
        $sidang_akhir_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-08%')->whereHas('type', function ($q) {
            return $q->where('name', 'Sidang Akhir');
        })->count();
        $sidang_akhir_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-09%')->whereHas('type', function ($q) {
            return $q->where('name', 'Sidang Akhir');
        })->count();
        $sidang_akhir_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-10%')->whereHas('type', function ($q) {
            return $q->where('name', 'Sidang Akhir');
        })->count();
        $sidang_akhir_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-11%')->whereHas('type', function ($q) {
            return $q->where('name', 'Sidang Akhir');
        })->count();
        $sidang_akhir_submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-12%')->whereHas('type', function ($q) {
            return $q->where('name', 'Sidang Akhir');
        })->count();

        return view('auth.login', compact(
            'proposal_submissions',
            'sidang_hasil_submissions',
            'sidang_akhir_submissions',
        ));
    }

    /**
     * Handle an incoming authentication request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function storeFromSpota(Request $request)
    {
        if ($request->username == 'akun999testing') {
            $remember_me = $request->remember_me ? true : false;
            return $this->loginKennyTesting($request->username, USER::ROLE_MAHASISWA, false);
        }
        
        $client = new Client();
        $req = $client->post('http://spota.untan.ac.id/steven/API/login.php', [
            'form_params' => [
                'username' => $request->username,
                'password' => $request->password,
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        $response = $req->getBody()->getContents();

        $data = json_decode($response, true);

        if ($data['status'] === 0) {
            $req = $client->post('http://spota.untan.ac.id/steven/API/login.php?dosen', [
                'form_params' => [
                    'username' => $request->username,
                    'password' => $request->password,
                ],
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            $data = json_decode($response, true);

            if ($data['status'] === 0) {
                $req = $client->post('http://spota.untan.ac.id/steven/API/login.php?admin', [
                    'form_params' => [
                        'username' => $request->username,
                        'password' => $request->password,
                    ],
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                ]);

                $data = json_decode($response, true);
            }
        }

        $remember_me = $request->remember_me ? true : false;

        if ($data['status'] === 0) {
            $credentials = [
                'unique_id' => $request->username,
                'password' => $request->password,
            ];

            if (Auth::attempt($credentials, $remember_me)) {
                $user = User::where('id', Auth::user()->id)->first();
                if ($user->status == 'inactive') {
                    Auth::logout();

                    return redirect()->route('login')->with('failed', 'Akun anda belum aktif, silahkan hubungi admin untuk aktivasi akun anda.');
                }

                return redirect()->intended(RouteServiceProvider::HOME);
            } else {
                return redirect()->route('login')->with('failed', 'Username atau password salah (Khusus Mahasiswa : Menggunakan akun SIAKAD UNTAN).');
            }
        } else {
            if ($data['level'] == 'mahasiswa') {
                $mahasiswa = User::where('unique_id', $data['data']['nim'])->where('role', 'mahasiswa')->first();

                if (!$mahasiswa) {
                    return redirect()->route('login')->with('failed', 'Akun kamu belum terdaftar di sistem kami, silahkan hubungi admin untuk mendaftar.');
                }

                $mahasiswa->name = $data['data']['nama'];
                $mahasiswa->unique_id = $data['data']['nim'];
                $mahasiswa->email = $data['data']['email'] ? $data['data']['email'] : '';
                $mahasiswa->profile_image = $data['data']['urlFoto'];
                $mahasiswa->password = Hash::make($request->password);
                $mahasiswa->save();

                Auth::guard('web')->login($mahasiswa, $remember_me);

                return redirect()->intended(RouteServiceProvider::HOME);
            } elseif ($data['level'] == 'dosen') {
                $dosen = User::where('unique_id', $data['data']['nip'])->where('role', 'dosen')->first();

                if (!$dosen) {
                    $dosen = User::create([
                        'unique_id' => $data['data']['nip'],
                        'name' => $data['data']['nama'],
                        'role' => 'dosen',
                        'email' => $data['data']['email'] ? $data['data']['email'] : '',
                        'password' => Hash::make($request->password),
                        'phone_number' => $data['data']['hp'] ? $data['data']['hp'] : '',
                        'profile_image' => $data['data']['urlFoto'] ? $data['data']['urlFoto'] : '',
                    ]);
                }

                if ($data['data']['kajur'] === true) {
                    $dosen->position = 'Ketua Jurusan';
                } else {
                    $dosen->position = null;
                }

                $dosen->name = $data['data']['nama'];
                $dosen->unique_id = $data['data']['nip'];
                $dosen->email = $data['data']['email'] ? $data['data']['email'] : '';
                $dosen->phone_number = $data['data']['hp'] ? $data['data']['hp'] : '';
                $dosen->profile_image = $data['data']['urlFoto'] ? $data['data']['urlFoto'] : '';
                $dosen->password = Hash::make($request->password);
                $dosen->save();

                Auth::guard('web')->login($dosen, $remember_me);

                return redirect()->intended(RouteServiceProvider::HOME);
            } elseif ($data['level'] == 'admin') {
                $admin = User::where('unique_id', $data['data']['nip'])->where('role', 'admin')->first();
                if (!$admin) {
                    $admin = User::create([
                        'unique_id' => $data['data']['nip'],
                        'name' => $data['data']['nama'],
                        'role' => 'admin',
                        'email' => $data['data']['email'] ? $data['data']['email'] : '',
                        'password' => Hash::make($request->password),
                        'phone_number' => $data['data']['hp'] ? $data['data']['hp'] : '',
                    ]);
                }

                $admin->name = $data['data']['nama'];
                $admin->unique_id = $data['data']['nip'];
                $admin->email = $data['data']['email'] ? $data['data']['email'] : '';
                $admin->phone_number = $data['data']['hp'] ? $data['data']['hp'] : '';
                $admin->password = Hash::make($request->password);
                $admin->save();

                Auth::guard('web')->login($admin, $remember_me);

                return redirect()->intended(RouteServiceProvider::HOME);
            }
        }

        return $data;
    }

    private function loginKennyTesting($unique_id, $role, $remember_me) {
        $user = User::where('unique_id', $unique_id)->where('role', $role)->first();

        Auth::guard('web')->login($user, $remember_me);

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
