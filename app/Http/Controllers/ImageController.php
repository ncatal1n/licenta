<?php

namespace App\Http\Controllers;

use App\Services\ImageService;
use App\Services\UserService;
use Illuminate\Http\Request;

class ImageController
{
    public function render(Request $request, ImageService $imageService, UserService $userService)
    {
        $image = $imageService->get($request["uqid"]);
        $user = $userService->get($request);

        return $imageService->render($image,$request["token"], $user, $request);
    }
    public function view(Request $request, ImageService $imageService, UserService $userService)
    {
        $image = $imageService->get($request["uqid"]);
        $user = $userService->get($request);
        
        if(!$imageService->isImageOwner($image, $user))
        {
            return abort(403);
        }
        return view("view",[
            "image_uqid" =>  $image->uqid
        ]);
    }
}
