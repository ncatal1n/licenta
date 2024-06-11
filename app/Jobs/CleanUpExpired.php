<?php

namespace App\Jobs;

use App\Models\Image;
use App\Services\ImageService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanUpExpired implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $imageService;
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $images = Image::all();
        foreach ($images as $image) {
            if(Carbon::parse($image->expiresAt)->lt(Carbon::now()))
            {
                $this->imageService->delete($image->uqid, null, true);
            }
        }
    }
}
