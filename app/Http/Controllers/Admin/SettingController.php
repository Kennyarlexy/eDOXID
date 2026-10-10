<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SpotaHelper;
use App\Http\Controllers\Controller;
use App\Models\ActivityUserLog;
use App\Models\Setting;
use Google_Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $settings = Setting::get();
        return view('panel.admin.setting.index', compact('settings'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request,   [
            'site_name' => 'required',
            'semester' => 'required',
            'academic_year' => 'required',
        ]);

        Setting::where('key', 'site_name')->first()->update(['value' => $request->site_name]);

        Setting::where('key', 'semester')->first()->update(['value' => $request->semester]);
        Setting::where('key', 'academic_year')->first()->update(['value' => $request->academic_year]);

        if ($request->syarat_dan_ketentuan) {
            Setting::where('key', 'terms_of_service')->first()->update(['value' => $request->syarat_dan_ketentuan]);
        }

        if ($request->hasFile('logo')) {
            $request->validate([
                'logo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            $site_logo = Setting::where('key', 'site_logo')->first();

            if ($site_logo && $site_logo->value) {
                Storage::disk('public')->delete('settings/' . $site_logo->value);
            }

            $site_logo_url = 'site_logo_' . time() . '.' . $request->logo->getClientOriginalExtension();
            $request->logo->storeAs('settings', $site_logo_url, 'public');

            $site_logo->update(['value' => $site_logo_url]);
        }

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Memperbarui pengaturan',
        ]);

        return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui');
    }

    public function whatsappIndex()
    {
        $settings = Setting::get();
        return view('panel.admin.setting.whatsapp', compact('settings'));
    }

    public function whatsappUpdate(Request $request)
    {
        if ($request->wa_api_key) {
            Setting::where('key', 'wa_api_key')->first()->update(['value' => $request->wa_api_key]);
        }

        if ($request->wa_sender_phone_number) {
            Setting::where('key', 'wa_sender_phone_number')->first()->update(['value' => $request->wa_sender_phone_number]);
        }

        return redirect()->back()->with('success', 'Pengaturan API Whatsapp berhasil diperbarui');
    }

    public function googleDriveIndex()
    {
        $settings = Setting::get();
        return view('panel.admin.setting.googledrive', compact('settings'));
    }

    public function googleDriveUpdate(Request $request)
    {
        $request->validate([
            'google_client_id' => 'required',
            'google_client_secret' => 'required',
            'google_refresh_token' => 'required',
            'google_drive_folder_id' => 'required',
        ]);

        // check if access token is valid
        $client = new Google_Client();
        $client->setClientId($request->google_client_id);
        $client->setClientSecret($request->google_client_secret);
        $client->refreshToken($request->google_refresh_token);

        if ($client->isAccessTokenExpired()) {
            return redirect()->back()->with('failed', 'Access token tidak valid dan refresh token tidak valid');
        }

        // update access token
        Setting::where('key', 'google_client_id')->first()->update(['value' => $request->google_client_id]);
        Setting::where('key', 'google_client_secret')->first()->update(['value' => $request->google_client_secret]);
        Setting::where('key', 'google_refresh_token')->first()->update(['value' => $request->google_refresh_token]);
        Setting::where('key', 'google_drive_folder_id')->first()->update(['value' => $request->google_drive_folder_id]);

        Artisan::call('optimize:clear');
        return redirect()->back()->with('success', 'Pengaturan Google Drive berhasil diperbarui');
    }

    public function syncSpota()
    {
        $spotaHelper = new SpotaHelper();
        $spotaHelper->syncAllData();

        $sync_spota_data = Setting::where('key', 'sync_spota_data')->first();
        $sync_spota_data->update(['value' => date('Y-m-d H:i:s')]);

        return redirect()->back()->with('success', 'Data spota berhasil disinkronisasi');
    }
}
