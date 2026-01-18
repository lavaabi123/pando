<div class="bg-light p-10 border mb-2 rounded note-items">
    <div class="d-flex align-items-center justify-content-between p-2">
		<p dir="auto" class="notetext">{{ $note->notes }}</p>
        <div class="ml-auto d-flex align-item-center">
            <div class="me-2">
                <i class="fa fa-edit font-14 text-muted pointer" onclick="click_edit(this, {{ $note->id }})" role="button"></i>
            </div>
            <div class="mx-1">
                <i class="fa fa-close font-20 text-muted pointer" onclick="delete_note({{ $note->id }}, '{{ $note->date->format('Y-m-d') }}')" role="button"></i>
            </div>
        </div>
    </div>
</div>