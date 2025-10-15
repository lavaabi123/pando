<?php $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
        $data = is_string($value->data) ? json_decode($value->data, true) : (is_array($value->data) ? $value->data : []);
        $caption = $data['caption'] ?? '';
        $link = $data['link'] ?? '';
        $medias = $data['medias'] ?? [];
        $img = is_array($medias) && isset($medias[0]) && $medias[0] ? $medias[0] : 'https://placehold.co/80x80';
        
        $network = $value->social_network ?? 'N/A';
        $type = $value->type ?? 'N/A';
        $status = ($value->result == 1) ? 'Ready' : 'Draft';
    ?>

    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card hp-100 draft-card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="card-content">
                    <div class="d-flex">
                        <div class="size-80 me-3 overflow-hidden b-r-10 d-flex justify-content-center align-items-center fs-30 text-primary bg-primary-100 border border-primary-200 img-wrap">
                        	<?php switch($type):
							    case ('media'): ?>
							        <img src="<?php echo e(Media::url($img)); ?>" class="img-fluid rounded-3 shadow-sm"/>
							        <?php break; ?>

							    <?php case ('link'): ?>
							        <a href="<?php echo e($link); ?>" target="_blank"><i class="fa-light fa-link"></i></a>
							        <?php break; ?>

							    <?php default: ?>
							        <i class="fa-light fa-align-center"></i>
							<?php endswitch; ?>
                        </div>
                        <div class="flex-grow-1">
                            <p class="card-text text-gray-600 mb-3 fs-14"><?php echo e($caption ?: 'No caption.'); ?></p>
                            <div class="d-flex gap-2 flex-wrap">
                            </div>
                        </div>
                    </div>
                </div>


            </div>
            <div class="card-footer fs-12 d-flex justify-content-center gap-8">
	            <a href="<?php echo e(url_app("publishing/composer")); ?>" class="d-flex flex-fill gap-8 align-items-center justify-content-center text-gray-900 text-hover-primary fw-5 border-end actionItem" data-append-content="composer-scheduling" data-id="<?php echo e($value->id_secure); ?>" da data-call-success="AppPubishing.openCompose();">
	                <i class="fa-light fa-pen-to-square"></i>
	                <span><?php echo e(__("Edit")); ?></span>
	            </a>
	            <a href="<?php echo e(url_app("publishing/destroy")); ?>" class="d-flex flex-fill gap-8 align-items-center justify-content-center text-gray-900 text-hover-primary fw-5 actionItem" data-id="<?php echo e($value->id_secure); ?>" data-confirm="<?php echo e(__("Are you sure you want to delete this?")); ?>" data-call-success="Main.ajaxScroll(true)">
	                <i class="fa-light fa-trash-can"></i>
	                <span><?php echo e(__("Delete")); ?></span>
	            </a>
	        </div>

        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php if($schedules->total() == 0 && $schedules->currentPage() == 1): ?>
<div class="d-flex flex-column align-items-center justify-content-center py-5 my-5">
    <span class="fs-70 mb-3 text-primary">
        <i class="fa-light fa-file-pen"></i>
    </span>
    <div class="fw-semibold fs-5 mb-2 text-gray-800">
        <?php echo e(__('No drafts yet')); ?>

    </div>
    <div class="text-body-secondary mb-4">
        <?php echo e(__('Start by creating a new draft to save your ideas and prepare content before publishing.')); ?>

    </div>
</div>
<?php endif; ?><?php /**PATH C:\xampp82\htdocs\pando-laravel\modules/AppPublishingDraft\resources/views/list.blade.php ENDPATH**/ ?>