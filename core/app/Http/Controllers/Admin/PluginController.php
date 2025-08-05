<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Lib\CurlRequest;
use App\Models\Plugin;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Image;
use Laramin\Utility\VugiChugi;

class PluginController extends Controller
{

    private $location;
    private $projectAlias;
    private $configContent;
    private $fileName;

    public function index()
    {
        $pageTitle = "Plugins";
        $plugins = Plugin::get();
        return view('admin.plugins.index',compact('pageTitle','plugins'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'name'=>'required',
            'description'=>'required',
            'company'=>'required',
            'purchase_code'=>'required',
            'envato_username'=>'required',
            'logo'=>['required',new FileTypeValidate(['jpg','jpeg','png'])],
            'plugin'=>['required',new FileTypeValidate(['zip'])]
        ]);

        $pluginExists = Plugin::where('purchase_code',$request->purchase_code)->exists();
        if ($pluginExists) {
            $notify[] = ['success','Plugin already exists'];
            return back()->withNotify($notify);
        }

        $zipFile = $request->plugin;
        $configContent = $this->unZipFile($request,$zipFile);


        $param['code']    = @$request->purchase_code;
        $param['url']     = env("APP_URL");
        $param['user']    = $request->envato_username;
        $param['product'] = @$configContent->alias;
        $param['email']   = auth()->guard('admin')->user()->email;
        $reqRoute         = VugiChugi::lcLabSbm();
        $response         = CurlRequest::curlPostContent($reqRoute, $param);
        $response         = json_decode($response);

        if ($response->error == 'error') {
            fileManager()->removeDirectory($this->location);
            $notify[] = ['error', $response->message];
            return back()->withNotify($notify);
        }

        $this->makeZip();

        $plugin = new Plugin();
        $plugin->name = $request->name;
        $plugin->file_name = $this->fileName;
        $plugin->version = $configContent->version;
        $plugin->purchase_code = $request->purchase_code;
        $plugin->plugin_for = $configContent->plugin_for;
        $plugin->meta_data = [
            'site_url'=>route('home'),
            'description'=>$request->description,
            'author'=>$request->company,
        ];
        $plugin->save();

        $notify[] = ['success','Plugin added successfully'];
        return back()->withNotify($notify);

    }

    public function update(Request $request,$id)
    {
        $request->validate([
            'name'=>'required',
            'description'=>'required',
            'company'=>'required',
            'logo'=>['required',new FileTypeValidate(['jpg','jpeg','png'])],
            'plugin'=>['required',new FileTypeValidate(['zip'])]
        ]);

        $plugin = Plugin::findOrFail($id);

        if ($request->hasFile('plugin')) {
            $configContent = $this->unZipFile($request,$request->plugin);
            $this->makeZip($plugin->file_name);

            $plugin->file_name = $this->fileName;
            $plugin->version = $configContent->version;
            $plugin->plugin_for = $configContent->plugin_for;
        }

        $plugin->name = $request->name;
        $plugin->meta_data = [
            'site_url'=>route('home'),
            'description'=>$request->description,
            'author'=>$request->company,
        ];
        $plugin->save();

        $notify[] = ['success','Plugin updated successfully'];
        return back()->withNotify($notify);

    }

    private function shortCodes()
    {
        return [
            'site_url'=>route('home'),
            'plugin_version'=>$this->configContent->version,
            'plugin_name'=>request()->name,
            'plugin_description'=>request()->description,
            'plugin_author'=>request()->company,
        ];
    }

    private function unZipFile($request,$zipFile){
        $zip = new \ZipArchive;
        if ($zip->open($zipFile) !== TRUE) {
            throw ValidationException::withMessages(['errors'=>'Zip couldn\'t be extracted.']);
        }

        $this->projectAlias = titleToKey($request->name);

        $this->location = "core/temp/$this->projectAlias";

        $zip->extractTo($this->location);
        $zip->close();

        $config = "$this->location/config.json";

        if (!file_exists($config)) {
            throw ValidationException::withMessages(['error'=>'Something went wrong with this file.']);
            fileManager()->removeDirectory($this->location);
        }

        $this->configContent = json_decode(file_get_contents($config));

        if (!$this->configContent->plugin_for) {
            fileManager()->removeDirectory($this->location);
            throw ValidationException::withMessages(['error'=>'Wrong file uploaded. Please upload the valid file.']);
        }

        return $this->configContent;
    }

    private function makeZip($old = null){
        $shortCodes = $this->shortCodes();

        $folderName = 'xcash-'.$this->configContent->plugin_for;

        $source = "$this->location/$folderName";
        $pluginFile = "$source/xcash.php";
        $pluginFileContent = file_get_contents($pluginFile);
        foreach ($shortCodes as $key => $value) {
            $pluginFileContent = str_replace('{{'.$key.'}}',$value,$pluginFileContent);
        }

        if(!file_put_contents($pluginFile,$pluginFileContent)){
            fileManager()->removeDirectory($this->location);
            $notify[] = ['error','Something went wrong.'];
            return back()->withNotify($notify);
        }

        if (request()->hasFile('logo')) {
            Image::make(request()->logo)->save($this->location.'/'.$folderName . '/logo.png');
        }

        $this->fileName = $this->projectAlias.'_'.$this->configContent->plugin_for.'.zip';

        $destination = 'assets/plugins/'.$this->fileName;
        $zip = new \ZipArchive();
        if ($zip->open($destination, \ZipArchive::CREATE) !== true) {
            return false;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        if ($old) {
            fileManager()->removeFile('assets/plugins/'.$old);
        }

        $rootPath = realpath($source);
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = "$this->projectAlias/".ltrim(substr($filePath, strlen($rootPath)), DIRECTORY_SEPARATOR);
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();

        $sourcePluginIcon = "$this->location/{$this->configContent->plugin_for}.png";
        $destinationPluginIcon = "assets/plugins/{$this->configContent->plugin_for}.png";
        @rename($sourcePluginIcon,$destinationPluginIcon);

        fileManager()->removeDirectory($this->location);
    }

    public function status($id)
    {
        return Plugin::changeStatus($id);
    }
}
