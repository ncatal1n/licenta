<div>
    @if($state["loaded"] == true)
    <div class="flex place-content-center w-screen h-fit min-h-[95vh] place-items-center">
        <div class="w-1/3 h-fit bg-neutral rounded-2xl overflow-hidden relative">
            <div wire:loading class="absolute w-full h-full top-0 left-0 bg-black/50 backdrop-blur-md z-50">
                <div class="flex w-full h-full place-content-center place-items-center">
                    <p class="loading loading-infinity loading-lg"></p>
                </div>
            </div>
            <div class="w-full h-96 rounded-2xl overflow-hidden">
                <img class="w-full h-full object-cover rounded-2xl" src="{{$state["image"]["uqid"]}}" />
            </div>
            <div class="flex flex-col gap-2 mt-2 p-4">
                @if($state["image"]["share_url"] != null)
                <label class="input input-bordered flex items-center gap-2">
                    Share
                    <input value="{{$state["image"]["share_url"]}}" type="text" class="grow input-ghost border-0" placeholder="Shareable link..." readonly />
                </label>
                @else
                <button class="btn btn-primary" wire:click="genereateToken">Generate public link</button>

                @endif
                <button class="btn btn-error" wire:click="delete">Delete image</button>

                <label class="form-control w-full relative">

                    <div class="label">
                        <span class="label-text">Image visibility</span>
                    </div>
                    <select wire:model.change="state.image.visibility" class="select select-bordered w-full">
                        @foreach($state["image"]["availableVisibility"] as $visibility)
                        <option {{$visibility == $state["image"]["visibility"] ? "selected" : ""}}>{{$visibility}}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </div>
    </div>
    @endif
</div>
