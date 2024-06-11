<?php
namespace App\Services;

use App\Models\Image;
use App\Models\TemporaryImageAccess;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Gd\Encoders\JpegEncoder;
use Intervention\Image\Drivers\Gd\Encoders\PngEncoder;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Image as IImage;
use Intervention\Image\ImageManager;
use Intervention\Image\Typography\FontFactory;
use PhpParser\Node\Stmt\Break_;
use stdClass;

class ImageService
{
    const SUPPORTED_ENCODING = ["jpeg","webp","png"];

    public function store(TemporaryUploadedFile $toUpload, $user, array $settings = []) : string | null
    {
        try{
            $path = $toUpload->store("images");
            return $this->createRecord($path, $user, $settings);
        }
        catch(\Exception $e){
            return null;
        }
    }
    public function createRecord(string $path, $user, array $settings) : string 
    {
        $image = new Image();
        $image->uri = $path;
        $image->uqid = Str::uuid();
        $image->owner = $user->email;
        $image->visibility = $settings["visibility"] ?? "private";
        $image->expiresAt = now()->addDays($settings["expiration"] ?? 1);
        $image->save();
        return $image->uqid;
    }
    public function get(string $imageUqid)
    {
        try{
            $image = Cache::remember($imageUqid, 60*60, function() use ($imageUqid){
                return Image::where("uqid", $imageUqid)->firstOrFail();
            });
            return $image;
        }
        catch(\Exception $e){
            return null;
        }
    }

    public function encodeToPNG(IImage $iImage)
    {
        return $iImage->encode(new PngEncoder());
    }
    public function encodeToJPEG(IImage $iImage, int|null $quality = 100)
    {
        return $iImage->encode(new JpegEncoder(quality:$quality ?? 100));
    }
    public function encodeToWebp(IImage $iImage, int|null $quality = 100)
    {
        return $iImage->encode(new WebpEncoder(quality: $quality ?? 100));
    }

    public function addTextToImage(IImage $iImage, array $params) 
    {
        $iImage->text($params["content"], $params["coords"]["x"], $params["coords"]["y"], function (FontFactory $font) use ($params) {
            $font->filename('../resources/fonts/opensans.ttf');
            $font->size($params["size"]);
            $font->color($params["color"]);
            $font->align('left');
            $font->valign('middle');
            $font->lineHeight(1);
        });
        return $iImage;
    }

    public function resizeImage(IImage $iImage, array $params)
    {
        $iImage->resize($params["width"], $params["height"]);  
        return $iImage;
    }

    public function modifyBrightness(IImage $iImage, int $level)
    {
        $iImage->brightness($level);
        return $iImage;
    }

    public function modifyContrast(IImage $iImage, int $level)
    {
        $iImage->contrast($level);
        return $iImage;
    }

    public function convertToGrayscale(IImage $iImage)
    {
        $iImage->greyscale();
        return $iImage;
    }
    public function blurImage(IImage $iImage, int $amount)
    {
        $iImage->blur($amount);
        return $iImage;
    }
    public function returnImageResponse($content, $mime)
    {
        return response($content)->header("Content-Type", $mime);
    }

    public function originalImage($imageData, $mime) : Response
    {
        return $this->returnImageResponse($imageData, $mime);
    }
    
    public function usesDefaultParams(stdClass $params) : bool{
        foreach ($params as $param){
            if(!in_array($param,[0,null])){
                return false;
            }
        }
        return true;
    }

    public function parseParams(Request $request) : stdClass
    {
        $resizeParams = explode("x",$request["resize"]);
        $textParams = explode(":",$request["text"]);
        $qualityParams = $request["quality"];
        $brightnessParams = $request["brightness"];
        $contrastParams = $request["contrast"];
        $grayscaleParams = $request["grayscale"];
        $blurParams = $request["blur"];
        $encodeParams = $request["encode"];
        
        $resizeParams = count($resizeParams) == 2 ? [
            "width" => $resizeParams[0],
            "height" => $resizeParams[1]
        ] : null;
        $textParams = count($textParams) == 4 ? [
            "content" => $textParams[0],
            "coords" => [
                "x" => explode("x", $textParams[1])[0],
                "y" => explode("x", $textParams[1])[1],
            ],
            "size" => $textParams[2] ,
            "color" => '#'.$textParams[3]
        ] : null;
        $brightnessParams = $brightnessParams ?? null;
        $contrastParams = $contrastParams ?? null;
        $grayscaleParams = $grayscaleParams ?? null;
        $blurParams = $blurParams ?? null;
        $encodeParams = $encodeParams ?? null;
        $qualityParams = $qualityParams ?? null;
        
        $params = new stdClass();
        $params->resize = $resizeParams;
        $params->text = $textParams;
        $params->brightness = intval($brightnessParams);
        $params->contrast = intval($contrastParams);
        $params->grayscale = $grayscaleParams;
        $params->blur = intval($blurParams);
        $params->encode = $encodeParams;
        $params->quality = $qualityParams;

        return $params;
    }

    public function manipulate($uri, stdClass $params )
    {
        $imageData = Storage::get($uri);
        $mime = Storage::mimeType($uri);

        /*
            Return original image data
        */
        if(count((array)$params) == 0 || $this->usesDefaultParams($params))
        {
            return $this->originalImage($imageData, $mime);
        }

        $manager = new ImageManager(new GdDriver());
        $read = $manager->read($imageData); 

        if(property_exists($params, "resize") && $params->resize != null)
        {
            $read = $this->resizeImage($read, $params->resize);
        }
        if(property_exists($params, "text") && $params->text != null)
        {
            $read = $this->addTextToImage($read, $params->text);
        }

        if(property_exists($params, "brightness") && $params->brightness != null)
        {
            $read = $this->modifyBrightness($read, intval($params->brightness));
        }
        if(property_exists($params, "contrast") && $params->contrast != null)
        {
            $read = $this->modifyContrast($read, intval($params->contrast));
        }
        if(property_exists($params, "grayscale") && $params->grayscale != null)
        {
            if($params->grayscale == "true")
            {
                $read = $this->convertToGrayscale($read);
            }
        }
        if(property_exists($params, "blur") && $params->blur != null)
        {
            $read = $this->blurImage($read, intval($params->blur));
        }
        if(property_exists($params, "encode") && $params->encode != null)
        {
            if(!in_array($params->encode, self::SUPPORTED_ENCODING)){
                return $this->returnImageResponse($this->encodeToWebp($read, $params->quality ?? 100), 'image/webp');
            }

            switch($params->encode){
                case "jpeg":
                    return $this->returnImageResponse($this->encodeToJPEG($read, $params->quality),'image/jpeg');
                break;
                case "png":
                    return $this->returnImageResponse($this->encodeToPNG($read), 'image/png');
                break;
                case "webp":
                    return $this->returnImageResponse($this->encodeToWebp($read, $params->quality ?? 100), 'image/webp');
            }
        }

        /*
            Return the image with webp encoding and the requested quality
        */
        return $this->returnImageResponse($this->encodeToWebp($read, $params->quality ?? 100), 'image/webp');

    }


    public function render($image,$token, $user, Request $request) : Response|JsonResponse
    {
        try{
            if($image->visibility == "private"){
                if($token == null){
                    if($user == null || $image->owner != $user->email)
                    {
                        return response()->json([
                            "message" => "Could not validate access"
                        ], 403);
                    }
                }
                else{
                   if(!$this->hasValidTokenForImage($image,$token))
                   {
                    return response()->json([
                        "message" => "Invalid access token"
                    ], 403);
                   }
                }
        
            }
            $params = $this->parseParams($request);
            $manipulated = Cache::remember(md5(json_encode($image).md5(json_encode($params)).$token), 60, function() use ($image,$params) {
                return $this->manipulate($image->uri, $params);
            });
            return $manipulated;
        }
        catch(\Exception $e){
            return response([
                "message" => "Something went wrong",
            ])->header("Content-type","application/json");
        }
    }

    public function getImagesOfOwner($user) : Paginator
    {
        $images = Image::where("owner", $user->email)->simplePaginate(8, [
            "uqid"
        ]);

        return $images;
    }

    public function getTemporaryToken($image, $user) : string | null
    {
        try{
            if($image->owner != $user->email)
            {
                throw new \Exception("Not image owner");
            }
            $token = Cache::remember("image-temp-token".$image->uqid.$user->email, 5 * 60, function () use ($image, $user){
                $temporary = new TemporaryImageAccess();
                $temporary->image_uqid = $image->uqid;
                $temporary->token = Str::uuid()->toString();
                $temporary->owner = $user->email;
                $temporary->expiresAt = now()->addDays(1);
                $temporary->save();
                return $temporary->token;
            });
            return $token;
        }
        catch(\Exception $e)
        {
            return null;
        }
    }
    public function changeVisibility(string $imageUqid, string $visibility, $user) : bool
    {
        try{
            $allowed = ["public","private"];
            $image = $this->get($imageUqid);

            if(!in_array($visibility, $allowed) || !$this->isImageOwner($image, $user))
            {
                throw new \Exception("Invalid permission or visibility");
            }
            $image->visibility = $visibility;
            $image->save();
            Cache::delete($imageUqid);
            return true;
        }
        catch(\Exception $e)
        {
            return false;
        }
    }


    public function delete(string $imageUqid, $user, $bypass = false) : bool
    {
        try{
            $image = $this->get($imageUqid);
            if( !$bypass && !$this->isImageOwner($image, $user))
            {
                throw new \Exception("Invalid permission");
            }

            Storage::delete($image->uri);
            $image->delete();
            return true;
        }
        catch(\Exception $e)
        {
            return false;
        }
    }
    
    public function hasValidTokenForImage($image, $token) : bool
    {
        try{
            $valid = Cache::remember("has-valid-token".md5($image->uqid, $token),60,  function () use ($image, $token){
                $temporary = TemporaryImageAccess::where("token",$token)->firstOrFail();
                return $image->uqid == $temporary->image_uqid && Carbon::parse($temporary->expiresAt)->gt(Carbon::now());
            });
            return $valid;

        }
        catch(\Exception $e)
        {
            return false;
        }
    }
    public function isImageOwner($image, $user) : bool
    {
        return $image->owner == $user->email;
    }

}