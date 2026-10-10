<?php

namespace App\Helpers;

use App\Models\Setting;
use Google_Client;
use Hypweb\Flysystem\GoogleDrive\GoogleDriveAdapter;
use League\Flysystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GoogleDriveHelper
{
    function google_drive_flysystem()
    {
        $google_client_id = Setting::where('key', 'google_client_id')->first()->value;
        $google_client_secret = Setting::where('key', 'google_client_secret')->first()->value;
        $google_refresh_token = Setting::where('key', 'google_refresh_token')->first()->value;

        $google_drive_folder_id = Setting::where('key', 'google_drive_folder_id')->first()->value;

        $client = new Google_Client();
        $client->setClientId($google_client_id);
        $client->setClientSecret($google_client_secret);
        $client->refreshToken($google_refresh_token);

        Log::debug('expires in: ' . $client->getAccessToken()['expires_in']);
        Log::debug('access_token_expired: ' . $client->isAccessTokenExpired());

        // check if refreshToken is expired
        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            $google_refresh_token = $client->getRefreshToken();
            Setting::where('key', 'google_refresh_token')->update(['value' => $google_refresh_token]);
        }


        $service = new \Google_Service_Drive($client);

        $options = [];

        $adapter = new GoogleDriveAdapter($service, $google_drive_folder_id, $options);
        $filesystem = new Filesystem($adapter);

        return $filesystem;
    }

    function file_get_contents_curl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    // untuk mencari file dan mereturn array object data file tersebut
    public function getFile($file_name)
    {
        $filename = $file_name;

        $dir = '/';
        $recursive = true; // Get subdirectories also?
        $contents = collect($this->google_drive_flysystem()->listContents($dir, $recursive));

        $file = $contents
            ->where('type', '=', 'file')
            ->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
            ->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
            ->first(); // there can be duplicate file names!

        return $file; // array with file info
    }

    // untuk mencari folder
    public function searchFolder($folderName)
    {
        // the human readable folder name to get the content of...
        // for simplicity, this folder is assumed to exist in the root directory.
        $folder = (string) $folderName;
        $recursive = true; // Get subdirectories also?

        // get root directory contents..
        $contents = collect($this->google_drive_flysystem()->listContents('/', $recursive));

        // find the folder you are looking for...
        $dir = $contents->where('type', '=', 'dir')
            ->where('name', '=', $folder)
            ->first(); // there could be duplicate directory names

        if (!$dir) {
            return response()->json([
                'status' => 404,
                'message' => 'Folder not found'],
            );
        }

        return $dir;
    }

    // untuk membuat folder
    public function createNewFolder($folderName)
    {
        $nim = explode('-', str_replace(' ', '', $folderName))[0];
        $angkatan = "20" . substr($nim, 5, 2);

        $angkatanFolder = $this->searchFolder($angkatan);
        if (!is_array($angkatanFolder) && $angkatanFolder->getData()->status === 404) {
            $angkatanFolder = $this->google_drive_flysystem()->createDir($angkatan);
        }

        // function that create new sub folder in google drive
        $this->createNewSubFolder($angkatan, $folderName);

        // searching the folder that you has create in google drive and get the id
        $resultFolderSearch = $this->searchFolder($folderName);
        return $resultFolderSearch;
    }

    public function createNewSubFolder($parentFolderName, $subFolderName)
    {
        // Find parent dir for reference
        $dir = '/';
        $recursive = true; // Get subdirectories also?
        $contents = collect($this->google_drive_flysystem()->listContents($dir, $recursive));

        $dir = $contents->where('type', '=', 'dir')
            ->where('name', '=', $parentFolderName)
            ->first(); // There could be duplicate directory names!

        if (!$dir) {
            return response()->json([
                'status' => 404,
                'message' => 'Folder not found'],
            );
        }

        // Create sub dir
        $createSubFolder = $this->google_drive_flysystem()->createDir($dir['path'] . '/' . $subFolderName);
        return $createSubFolder;
    }

    public function uploadFileInSubFolder($uploadFile, $fileName, $subFolderName)
    {
        $dir = '/';
        $recursive = true; // Get subdirectories also?
        $contents = collect($this->google_drive_flysystem()->listContents($dir, $recursive));

        $dir = $contents->where('type', '=', 'dir')
            ->where('name', '=', $subFolderName)
            ->first(); // There could be duplicate directory names!

        if (!$dir) {
            return response()->json([
                'status' => 404,
                'message' => 'Folder not found'],
            );
        }

        $this->google_drive_flysystem()->put($dir['path'] . '/' . $fileName, $this->file_get_contents_curl("$uploadFile"));

        // cari file berdasarkan nama file tadi
        $getFile = $this->getFile($fileName);
        return $getFile;
    }

    public function deleteFile($fileName)
    {
        // Now find that file and use its ID (path) to delete it
        $dir = '/';
        $recursive = true; // Get subdirectories also?
        $contents = collect($this->google_drive_flysystem()->listContents($dir, $recursive));

        $file = $contents
            ->where('type', '=', 'file')
            ->where('filename', '=', pathinfo($fileName, PATHINFO_FILENAME))
            ->where('extension', '=', pathinfo($fileName, PATHINFO_EXTENSION))
            ->first(); // there can be duplicate file names!

        if ($file) {
            $this->google_drive_flysystem()->delete($file['path']);
        }

        return response()->json([
            'status' => 200,
            'message' => 'File was deleted from Google Drive'
        ]);
    }

    public function setPermissionReaderViewing($filename)
    {
        // Get the file to find the ID
        $dir = '/';
        $recursive = true; // Get subdirectories also?
        $contents = collect($this->google_drive_flysystem()->listContents($dir, $recursive));
        $file = $contents
            ->where('type', '=', 'file')
            ->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
            ->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
            ->first(); // there can be duplicate file names!

        // Change permissions
        // - https://developers.google.com/drive/v3/web/about-permissions
        // - https://developers.google.com/drive/v3/reference/permissions
        $service = $this->google_drive_flysystem()->getAdapter()->getService();
        $permission = new \Google_Service_Drive_Permission();
        $permission->setRole('reader');
        $permission->setType('domain');
        $permission->setDomain('untan.ac.id');
        $permission->setAllowFileDiscovery(false);
        $permissions = $service->permissions->create($file['basename'], $permission);

        return $permissions;
    }

    public function setPermissionEdit($filename, $email)
    {
        // Get the file to find the ID
        $dir = '/';
        $recursive = true; // Get subdirectories also?
        $contents = collect($this->google_drive_flysystem()->listContents($dir, $recursive));
        $file = $contents
            ->where('type', '=', 'file')
            ->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
            ->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
            ->first(); // there can be duplicate file names!

        // Change permissions
        // - https://developers.google.com/drive/v3/web/about-permissions
        // - https://developers.google.com/drive/v3/reference/permissions
        $service = $this->google_drive_flysystem()->getAdapter()->getService();
        $permission = new \Google_Service_Drive_Permission();
        $permission->setRole('writer');
        $permission->setType('user');
        $permission->setEmailAddress($email);
        $permissions = $service->permissions->create($file['basename'], $permission);

        return $permissions;
    }

    public function setPermissionEditToAllEmails($filename, $emails)
    {
        // Get the file to find the ID
        $dir = '/';
        $recursive = true; // Get subdirectories also?
        $contents = collect($this->google_drive_flysystem()->listContents($dir, $recursive));
        $file = $contents
            ->where('type', '=', 'file')
            ->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
            ->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
            ->first(); // there can be duplicate file names!

        // Change permissions
        // - https://developers.google.com/drive/v3/web/about-permissions
        // - https://developers.google.com/drive/v3/reference/permissions
        $service = $this->google_drive_flysystem()->getAdapter()->getService();
        foreach ($emails as $email) {
            if ($email && str_contains($email, '@')) {
                $permission = new \Google_Service_Drive_Permission();
                $permission->setRole('writer');
                $permission->setType('user');
                $permission->setEmailAddress($email);
                $service->permissions->create($file['basename'], $permission);
            }
        }

        return response()->json([
            'status' => 200,
            'message' => 'Permission was set to all verificators'
        ]);
    }
}
