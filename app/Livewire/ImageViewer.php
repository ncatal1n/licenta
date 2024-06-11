<?php

namespace App\Livewire;

use App\Services\ImageService;
use App\Services\UserService;
use Illuminate\Support\Facades\Redirect;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class ImageViewer extends Component
{
    protected $imageService;
    public $uqid;
    public $state = [
        "loaded" => false
    ];


    public function delete(ImageService $imageService)
    {
        $userService = new UserService();

        $user = $userService->get(request());


        if($imageService->delete($this->uqid, $user)) {
            return Redirect::route('dashboard')->success('The image was successfully deleted');

        }
    }

    public function genereateToken(ImageService $imageService)
    {
        $userService = new UserService();
        $user = $userService->get(request());

        $this->state["image"]["share_url"] = route("image.render",[
            "uqid" => $this->uqid,
            "token" => $imageService->getTemporaryToken($imageService->get($this->uqid), $user)
        ]);
        Toaster::success("New public url has been generated");
    }

    public function loadImage(ImageService $imageService)
    {
        $image = $imageService->get($this->uqid);
        $this->state["image"] = [
                "uqid" => route("image.render",[
                    "uqid" => $image->uqid
                ]),
                "visibility" => $image->visibility,
                "availableVisibility" => ["private","public"],
                "share_url" => $image->visibility != "private" ? route("image.render",[
                    "uqid" => $image->uqid
                ]) : null
            ];
        $this->state["loaded"] = true;
        

    }

    public function updatedState($value, $key)
    {
        if($key == "image.visibility")
        {
            $userService = new UserService();
            $imageService = new ImageService();

            $user = $userService->get(request());

            
            if($imageService->changeVisibility($this->uqid, $value, $user))
            {
                Toaster::success("Image visibility changed to " . $value);
                $this->loadImage($imageService);
            }
            else{
                Toaster::success("Could not change visibility");
            }
   
        }
    }

    public function mount(ImageService $imageService)
    {
        $this->loadImage($imageService);
    }

    public function render()
    {
        return view('livewire.image-viewer');
    }
}
