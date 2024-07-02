<x-app-layout>
    @section("title")
    | Home
    @endsection
    <div class="hero h-[95vh] bg-base-100">
        <div class="hero-content text-center">
            <div class="max-w-md">
                <h1 class="text-4xl text-white font-bold">Store, share and edit your images on the fly with ease</h1>
                <br>
                <a href="{{route("ui.upload")}}">
                    <button class="btn btn-primary btn-wide">Upload</button>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
