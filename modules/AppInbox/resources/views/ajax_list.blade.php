@if(!empty($inbox_list))
    <div class="inbox-messages-list">
        @foreach($inbox_list as $item)
            <div class="inbox-item p-3 border-bottom {{ $item['is_completed'] ? 'completed' : '' }}" 
                 data-id="{{ $item['id'] }}" 
                 data-type="{{ empty($item['conversation_id']) ? 'comment' : 'message' }}"
                 onclick="loadDetail(this)">
                <div class="d-flex align-items-center">
                    <img src="{{ $item['from_image'] }}" class="rounded-circle me-3" width="40" height="40" alt="">
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-1">{{ $item['from_name'] }}</h6>
                            <small class="text-muted">{{ date('M d, Y', strtotime($item['created_time'])) }}</small>
                        </div>
                        <p class="mb-1 text-truncate">{{ $item['message'] }}</p>
                        <small class="text-muted">
                            <i class="fab fa-{{ strtolower($item['media_type']) }} me-1"></i>
                            {{ $item['inbox_type'] }}
                        </small>
                    </div>
                </div>
                
                @if(!empty($item['tag_names']))
                    <div class="mt-2">
                        @foreach(explode(',', $item['tag_names']) as $tag)
                            <span class="badge bg-secondary me-1">
                                <i class="fa-tag fal me-1"></i>{{ $tag }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="pagination-container mt-3">
        {!! $pagerContainer !!}
    </div>
@else
    <div class="text-center py-5">
        <i class="fa-light fa-inbox fa-3x text-muted"></i>
        <p class="mt-3 text-muted">No messages found</p>
    </div>
@endif

<script>
function loadDetail(element) {
    const id = $(element).data('id');
    const type = $(element).data('type');
    
    $('.inbox-item').removeClass('active');
    $(element).addClass('active');
    
    // Load detail view via AJAX
    // Implementation depends on your specific requirements
}
</script>
