<?php

namespace App\Providers;

use Google_Client;
use Hypweb\Flysystem\GoogleDrive\GoogleDriveAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use App\Models\Setting;

class GoogleDriveServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('google', function($app, $config) {
            $google_client_id = Setting::where('key', 'google_client_id')->first()->value;
            $google_client_secret = Setting::where('key', 'google_client_secret')->first()->value;
            $google_refresh_token = Setting::where('key', 'google_refresh_token')->first()->value;

            $google_drive_folder_id = Setting::where('key', 'google_drive_folder_id')->first()->value;

            $client = new Google_Client();
            $client->setClientId($google_client_id);
            $client->setClientSecret($google_client_secret);
            $client->refreshToken($google_refresh_token);

            // check if refreshToken is expired
            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                $google_refresh_token = $client->getRefreshToken();
                Setting::where('key', 'google_refresh_token')->update(['value' => $google_refresh_token]);
            }

            $service = new \Google_Service_Drive($client);


            $options = [];
            if(isset($config['teamDriveId'])) {
                $options['teamDriveId'] = $config['teamDriveId'];
            }

            $adapter = new GoogleDriveAdapter($service, $google_drive_folder_id, $options);

            return new Filesystem($adapter);
        });
    }
}
