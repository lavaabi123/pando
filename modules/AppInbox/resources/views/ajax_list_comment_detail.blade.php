@if(!empty($lists))
    <div class="comment-detail">
        @if(!empty($post_detail))
            <div class="post-header p-3 border-bottom bg-light">
                <div class="d-flex align-items-center">
                    @if(isset($post_detail['from']['name']))
                        <h6 class="mb-1">{{ $post_detail['from']['name'] }}</h6>
                    @endif
                </div>
                @if(isset($post_detail['message']) || isset($post_detail['caption']))
                    <p class="mb-2">{{ $post_detail['message'] ?? $post_detail['caption'] ?? '' }}</p>
                @endif
                @if(isset($post_detail['full_picture']) || isset($post_detail['media_url']))
                    <img src="{{ $post_detail['full_picture'] ?? $post_detail['media_url'] ?? '' }}" 
                         class="img-fluid rounded" alt="Post image">
                @endif
            </div>
        @endif

        <div class="comments-section p-3" style="max-height: 500px; overflow-y: auto;">
            @foreach($lists as $comment)
                <div class="comment-item mb-3">
                    <div class="d-flex">
                        <img src="{{ $comment['from_image'] }}" class="rounded-circle me-3" width="35" height="35" alt="">
                        <div class="flex-grow-1">
                            <div class="comment-header d-flex justify-content-between">
                                <strong>{{ $comment['from_name'] }}</strong>
                                <small class="text-muted">
                                    {{ date('M d, Y H:i', strtotime($comment['created_time'])) }}
                                </small>
                            </div>
                            <p class="mb-1">{{ $comment['message'] }}</p>
                            
                            @if(!empty($comment['tag_names']))
                                <div class="mt-1">
                                    @foreach(explode(',', $comment['tag_names']) as $tag)
                                        <span class="badge bg-secondary me-1">
                                            <i class="fa-tag fal me-1"></i>{{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Child comments/replies -->
                            @if(!empty($comment['child']))
                                <div class="child-comments mt-2 ms-4">
                                    @foreach($comment['child'] as $child)
                                        <div class="child-comment-item mb-2 p-2 bg-light rounded">
                                            <div class="d-flex">
                                                <img src="{{ $child['from_image'] }}" class="rounded-circle me-2" width="25" height="25" alt="">
                                                <div class="flex-grow-1">
                                                    <strong class="small">{{ $child['from_name'] }}</strong>
                                                    <p class="mb-0 small">{{ $child['message'] }}</p>
                                                    <small class="text-muted">
                                                        {{ date('M d, Y H:i', strtotime($child['created_time'])) }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <button class="btn btn-sm btn-link p-0" onclick="showReplyForm({{ $comment['id'] }})">
                                <i class="fa-light fa-reply"></i> Reply
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="reply-section p-3 border-top">
            <form id="comment-reply-form" onsubmit="sendCommentReply(event, '{{ $id }}', '')">
                <div class="input-group">
                    <textarea class="form-control" name="comment" rows="2" placeholder="Type your reply..." required></textarea>
                    <button class="btn btn-primary" type="submit">
                        <i class="fa-light fa-paper-plane"></i> Reply
                    </button>
                </div>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="complete_id" value="1" id="markCommentComplete">
                    <label class="form-check-label" for="markCommentComplete">
                        Mark as complete after sending
                    </label>
                </div>
            </form>
        </div>
    </div>
@else
    <div class="text-center py-5">
        <p class="text-muted">No comments found</p>
    </div>
@endif

<script>
function sendCommentReply(event, detailId, conversationId, inboxType) {
    event.preventDefault();
    const form = $(event.target);
    const formData = form.serialize() + '&detail_id=' + detailId + '&conversation_id=' + conversationId + '&inbox_type=' + inboxType;
    
    $.ajax({
        url: '{{ route("inbox.save_comment") }}',
        type: 'POST',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.status === 'success') {
                alert('Reply sent successfully');
                form[0].reset();
                loadInboxList();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr) {
            console.error('Error sending reply:', xhr);
            alert('Error sending reply. Please try again.');
        }
    });
}

function showReplyForm(commentId) {
    // Implementation for showing reply form for specific comment
    console.log('Reply to comment:', commentId);
}
</script>
