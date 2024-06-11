<?php

namespace App\Livewire;

use App\Services\ImageService;
use App\Services\UserService;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class PersonalImageGallery extends Component
{
    use  WithPagination;
    public $loaded = false;

    public function lazyLoad()
    {
        $this->loaded = true;
    }
    public function loadImages(ImageService $imageService)
    {
        
        $userService = new UserService();
        $user = $userService->get(request());
        $images = $imageService->getImagesOfOwner($user);

        /* 
            Prerender half of the images so the user has a better experience waiting for the rest to load
        */
        foreach(array_slice($images->items(),0, count($images->items()) / 2) as $image){
            $imageService->render($imageService->get($image->uqid), null, $user, request());
        }
        return $images;
    }

    public function render()
    {
        return view('livewire.personal-image-gallery',[
            "images" => $this->loaded ? $this->loadImages(new ImageService) : []
        ]);
    }
}
