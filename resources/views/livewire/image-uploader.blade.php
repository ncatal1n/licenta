<div class="flex flex-col place-content-center place-items-center bg-base-100 w-screen h-[95vh]">
    <div class="flex flex-col gap-4 w-96 h-fit py-12 px-6 bg-neutral rounded-2xl relative overflow-hidden">
        <div wire:loading class="absolute w-full h-full top-0 left-0 bg-black/50 backdrop-blur-md z-50">
            <div class="flex w-full h-full place-content-center place-items-center">
                <p class="loading loading-infinity loading-lg"></p>
            </div>
        </div>
        @if($status == "uploading")
        <progress class="progress w-full" value="{{$state["currentSize"]}}" max="{{$state["totalSize"]}}"></progress>
        @else
        @if (!$finalFile)
        <form wire:submit.prevent="submit">
            <input class="hidden w-0" type="file" id="file" accept="image/*" onchange="detectFile()" />
            <button type="button" onClick="uploadChunks()" class="btn btn-primary  w-full">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 inline-block align-middle">
                    <path fill-rule="evenodd" d="M1 5.25A2.25 2.25 0 0 1 3.25 3h13.5A2.25 2.25 0 0 1 19 5.25v9.5A2.25 2.25 0 0 1 16.75 17H3.25A2.25 2.25 0 0 1 1 14.75v-9.5Zm1.5 5.81v3.69c0 .414.336.75.75.75h13.5a.75.75 0 0 0 .75-.75v-2.69l-2.22-2.219a.75.75 0 0 0-1.06 0l-1.91 1.909.47.47a.75.75 0 1 1-1.06 1.06L6.53 8.091a.75.75 0 0 0-1.06 0l-2.97 2.97ZM12 7a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z" clip-rule="evenodd" />
                </svg>
                <span class="align-middle" id="btntext">Select image</span><br>
                <span id="filename" class="text-[0.5rem]"></span>

            </button>
        </form>
        @else
        <img class="w-full h-56 rounded-lg object-cover" src="{{ $finalFile->temporaryUrl() }}">
        <button wire:click="finishUpload" class="btn btn-success w-full">Confirm upload</button>
        <select wire:model.change="state.expiration" class="select select-bordered w-full">
            <option disabled selected>Image expiration?</option>
            <option value="1">1 day</option>
            <option value="7">1 week</option>
            <option disabled>no expiration (only for premium users)</option>
        </select>
        @endif
        @endif
    </div>
</div>
<script>
    const delay = ms => new Promise(res => setTimeout(res, ms));
    let stop = false;
    const chunkSize = @js($chunkSize);

    function generateRandomString(length) {
        const characters =
            'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        const charactersLength = characters.length;
        let result = '';
        const randomValues = new Uint32Array(length);
        window.crypto.getRandomValues(randomValues);
        randomValues.forEach((value) => {
            result += characters.charAt(value % charactersLength);
        });
        return result;
    }

    function detectFile(el) {
        const file = document.querySelector('#file').files[0];
        document.getElementById('filename').innerHTML = file.name;
        document.getElementById('btntext').innerHTML = "Upload image";
    }

    function uploadChunks() {
        const file = document.querySelector('#file').files[0];
        if (file == undefined || file == null) {
            document.querySelector('#file').click();
        }
        const filename = `${generateRandomString(20)}.${file.name.split('.').pop()}`;
        @this.set('fileName', filename, true);
        @this.set('fileSize', file.size, true);
        livewireUploadChunk(file, 0);
    }

    async function livewireUploadChunk(file, start) {
        let sendchunktimeout = file.size >= (10 * Math.pow(10, 6)) ? 100 : 50;
        if (start != 0) {
            await delay(sendchunktimeout);
        } else {
            console.log("Sending first chunk");
            @this.dispatch("setStatus", {
                status: "uploading"
            })
            await delay(sendchunktimeout);
        }

        const chunkEnd = Math.min(start + chunkSize, file.size);
        const chunk = file.slice(start, chunkEnd);
        @this.upload('fileChunk', chunk, (uName) => {}, (err) => {
            @this.invalidFile();
            stop = true;
            return;
        }, (event) => {
            if (event.detail.progress == 100) {
                if (stop) {
                    return;
                }
                start = chunkEnd;
                if (start < file.size) {
                    console.info(`Sending chunk: ${start}`)
                    livewireUploadChunk(file, start);
                }
            }
        });
    }

</script>
