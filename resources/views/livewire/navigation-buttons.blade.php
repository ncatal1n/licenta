<div wire:init="loadUser" class="flex gap-2">
    @if(!$state["logged"])
    <a href="{{route("login")}}" wire:navigate>
        <button class="btn btn-sm btn-neutral">Login</button>

    </a>
    @else
    <a href="{{route("dashboard")}}" wire:navigate>
        <button class="btn btn-sm btn-neutral">Dashboard</button>

    </a>
    <form action="{{route("logout")}}" method="POST">
        @csrf
        <button type="submit" class="btn btn-sm btn-neutral">Logout</button>
    </form>
    @endif
</div>
