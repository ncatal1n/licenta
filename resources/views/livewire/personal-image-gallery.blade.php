<div wire:init="lazyLoad" class="relative">
    <div wire:loading class="absolute w-full h-full top-0 left-0 bg-black/50 backdrop-blur-md z-50">
        <div class="flex w-full h-full place-content-center place-items-center">
            <p class="loading loading-infinity loading-lg"></p>
        </div>
    </div>
    <div class="flex flex-wrap place w-full min-h-96 {{count($images) == 0 ? 'place-content-center place-items-center' : ''}}">
        @if(count($images) == 0)
        <h1 class="text-lg font-semibold">You don't have any uploaded images</h1>
        @else
        <h1 class="text-lg font-semibold w-full my-2">Your images</h1>
        @endif
        @foreach($images as $image)
        <div class="min-w-56 min-h-56 w-1/4 h-56  rounded-2xl flex place-content-center place-items-center p-2 relative overflow-hidden">
            <a wire:navigate class="absolute bg-black/50 top-0 left-0 flex place-content-center place-items-center w-full h-full z-20 transition-all opacity-0 cursor-pointer hover:opacity-100 " href="{{route("ui.image.view",[
                "uqid" => $image["uqid"]
            ])}}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                    <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
                    <path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 0 1 0-1.113ZM17.25 12a5.25 5.25 0 1 1-10.5 0 5.25 5.25 0 0 1 10.5 0Z" clip-rule="evenodd" />
                </svg>

            </a>

            <img class="w-full h-full object-cover rounded-2xl" src="{{route("image.render",[
                    "uqid" => $image["uqid"]
                ])}}" />
        </div>
        @endforeach
    </div>
    @if($loaded)
    <div class="flex mt-4">
        {{ $images->links() }}
    </div>
    @endif
</div>
