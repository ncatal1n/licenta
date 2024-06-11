<?php

namespace App\Livewire;

use App\Services\ImageService;
use App\Services\UserService;
use Exception;
use Livewire\Attributes\On; 
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

class ImageUploader extends Component
{
    use WithFileUploads;

    protected $limits = [
        "size" => 10000000, //10 MB
        "expirations" => [1, 7]
    ];

    public $status;
    public $chunkSize = 2000000; //2MB
    public $fileChunk;
    public $fileName;
    public $fileSize;
    public $finalFile;

    public $state = [
        "currentSize" => 0,
        "totalSize" => 0,
        "expiration" => 1
    ];

    #[On('setStatus')] 
    public function setStatus($status)
    {
        $this->status = $status;
        $this->check();
    }

    public function check()
    {
        if($this->fileSize > $this->limits["size"])
        {
            $this->cleanUp();
            return false;
        }
        return true;
    }

    public function invalidFile()
    {
        return redirect(request()->header('Referer'))->error("Invalid file type");

    }

    public function cleanUp()
    {
        return redirect(request()->header('Referer'))->error("File too large");
    }

    public function updatedFileChunk()
    {
        if(!$this->check())
        {
            return;
        }
        $chunkFileName = $this->fileChunk->getFileName();
        $finalPath = Storage::path('/livewire-tmp/' . $this->fileName);
        $tmpPath   = Storage::path('/livewire-tmp/' . $chunkFileName);
        $file = fopen($tmpPath, 'rb');
        $buff = fread($file, $this->chunkSize);
        fclose($file);
        $final = fopen($finalPath, 'ab');
        fwrite($final, $buff);
        fclose($final);
        unlink($tmpPath);
        $curSize = Storage::size('/livewire-tmp/' . $this->fileName);
        $this->state["currentSize"] = $curSize;
        $this->state["totalSize"] = $this->fileSize;
        
        if ($curSize == $this->fileSize) {
            $this->finalFile =
                TemporaryUploadedFile::createFromLivewire('/' . $this->fileName);
            $this->status = "done";
        }
    }
    public function finishUpload(ImageService $imageService, UserService $userService)
    {
        try{

            $user = $userService->get(request());
            if(!in_array($this->state["expiration"], $this->limits["expirations"]))
            {
                Toaster::error("Invalid expiration");
                return;
            }
            $imageId = $imageService->store($this->finalFile, $user,[
                "expiration" => $this->state["expiration"]
            ]);
            
            if($imageId == null)
            {
                throw new Exception("Could not upload image ");
            }
            return redirect(route("ui.image.view",[
                "uqid" => $imageId
            ]));
        }
        catch(\Exception $e)
        {
            abort(500);
        }

    }

    public function render()
    {
        return view('livewire.image-uploader');
    }
}
