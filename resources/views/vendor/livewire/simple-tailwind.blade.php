<div class="join">
    @if ($paginator->hasPages())
    @if (!$paginator->onFirstPage())
    <button wire:click="previousPage" class="join-item btn">«</button>
    @endif
    <button class="join-item btn">Page {{$paginator->currentPage()}}</button>
    @if (!$paginator->onLastPage())
    <button wire:click="nextPage" class="join-item btn">»</button>
    @endif
    @endif
</div>
