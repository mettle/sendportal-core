<div class="row mb-4">
    <div class="col-sm-6">
        {!! $records->links() !!}
    </div>
    <div class="col-sm-6 text-right pt-1">
        {{ __('Showing :count of :total items', ['count' => $records->count(), 'total' => $records->total()]) }}
    </div>
</div>
