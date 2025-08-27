<?php $__env->startSection('form', json_encode([
    'action' => module_url("save"),
    'method' => 'POST',
    'class' => 'actionForm',
    'data-redirect' => module_url()
])); ?>

<?php $__env->startSection('sub_header'); ?>
    <?php if (isset($component)) { $__componentOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $attributes; } ?>
<?php $component = App\View\Components\SubHeader::resolve(['title' => ''.e($result ? __('Edit Blog') : __('New Blog')).'','description' => ''.e($result ? __('Revise and enhance blog content seamlessly with precision') : __('Craft and publish a new blog post effortlessly')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sub-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\SubHeader::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <a class="btn btn-light btn-sm" href="<?php echo e(module_url()); ?>">
            <span><i class="fa-light fa-chevron-left"></i></span>
            <span><?php echo e(__('Back')); ?></span>
        </a>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6bfd7fd5c294530066e0efb20ff4cd9a)): ?>
<?php $attributes = $__attributesOriginal6bfd7fd5c294530066e0efb20ff4cd9a; ?>
<?php unset($__attributesOriginal6bfd7fd5c294530066e0efb20ff4cd9a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6bfd7fd5c294530066e0efb20ff4cd9a)): ?>
<?php $component = $__componentOriginal6bfd7fd5c294530066e0efb20ff4cd9a; ?>
<?php unset($__componentOriginal6bfd7fd5c294530066e0efb20ff4cd9a); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="container pb-5">
    <input class="d-none" name="id" type="text" value="<?php echo e(data($result, "id_secure")); ?>">
    <div class="row">
        <div class="col-md-8">
            <div class="card b-r-6 border-gray-300 mb-3">
                <div class="card-body">
                    <div class="msg-errors"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-4">
                                <label for="title" class="form-label"><?php echo e(__('Title')); ?></label>
                                <input placeholder="<?php echo e(__('')); ?>" class="form-control" name="title" id="title" type="text" value="<?php echo e($result->title ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-4">
                                <label for="code" class="form-label"><?php echo e(__('Description')); ?></label>
                                <textarea class="form-control" name="desc"><?php echo e($result->desc ?? ''); ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-4">
                                <label for="content" class="form-label"><?php echo e(__('Content')); ?> (<span class="text-danger">*</span>)</label>
                                <textarea class="textarea_editor border-gray-300 border-1 min-h-1200" name="content" placeholder="<?php echo e(__("Enter content")); ?>"><?php echo data($result, "content"); ?></textarea>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
        <div class="col-md-4">

            <div class="card b-r-6 border-gray-300 mb-4">
                <div class="card-header">
                    <div class="fw-5 fs-14">
                        <?php echo e(__('Category')); ?>

                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-0">
                        <select class="form-select w-100" name="cate_id" id="cate_id" data-control="select2">
                            <option value="0"><?php echo e(__('Select categories')); ?></option>
                                <?php if($categories): ?>
                                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value->id); ?>" <?php echo e(data($result, "cate_id", "select", $value->id)); ?>><?php echo e($value->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>

                        </select>
                    </div>
                </div>
            </div>
            <div class="card b-r-6 border-gray-300 mb-4">
                <div class="card-header">
                    <div class="fw-5 fs-14">
                        <?php echo e(__("Thumbnail")); ?>

                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-0">
                        <?php echo $__env->make('appfiles::block_select_file_large', [
                            "id" => "thumbnail",
                            "value" => $result->thumbnail ?? ''
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                </div>
            </div>
            <div class="card b-r-6 border-gray-300 mb-4">
                <div class="card-header">
                    <div class="fw-5 fs-14">
                        <?php echo e(__('Tags')); ?>

                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-0 fs-13">
                        <?php
                        $tag_ids = [];
                        if(!empty($result) && isset($result->tag_ids) && !empty($result->tag_ids)){
                            $tag_ids = $result->tag_ids;
                        }
                        ?>

                        <select class="form-select h-auto " name="tags[]" id="tags" data-control="select2" multiple="true" data-placeholder="<?php echo e(__("Add Tags")); ?>">
                            <?php if($tags): ?>
                                    <?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option
                                            value="<?php echo e($value->id); ?>"
                                            data-color="<?php echo e($value->color); ?>"
                                            <?php echo e((!empty($tag_ids) && in_array($value->id, $tag_ids)) ? "selected" : ""); ?>

                                        >
                                            <?php echo e($value->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card b-r-6 border-gray-300 mb-4">
                <div class="card-header">
                    <div class="fw-5 fs-14">
                        <?php echo e(__('Status')); ?>

                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-0">
                        <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                            <div class="form-check me-3">
                                <input class="form-check-input" type="radio" name="status" value="1" id="status_1" <?php if(($result->status ?? 1) == 1): echo 'checked'; endif; ?>>
                                <label class="form-check-label mt-1" for="status_1">
                                    <?php echo e(__('Enable')); ?>

                                </label>
                            </div>
                            <div class="form-check me-3">
                                <input class="form-check-input" type="radio" name="status" value="0" id="status_0" <?php if(($result->status ?? 1) == 0): echo 'checked'; endif; ?>>
                                <label class="form-check-label mt-1" for="status_0">
                                    <?php echo e(__('Disable')); ?>

                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card b-r-6 border-gray-300 mb-4">
                <div class="card-header">
                    <div class="fw-5 fs-14">
                       <?php echo e(__('Featured')); ?>

                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-0">
                        <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                            <div class="form-check me-3">
                                <input class="form-check-input" type="radio" name="featured" value="1" id="featured_1" <?php if(($result->featured ?? 0) == 1): echo 'checked'; endif; ?>>
                                <label class="form-check-label mt-1" for="featured_1">
                                    <?php echo e(__('Yes')); ?>

                                </label>
                            </div>
                            <div class="form-check me-3">
                                <input class="form-check-input" type="radio" name="featured" value="0" id="featured_2" <?php if(($result->featured ?? 0) == 0): echo 'checked'; endif; ?>>
                                <label class="form-check-label mt-1" for="featured_2">
                                    <?php echo e(__('No')); ?>

                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <div class="row">
        <div class="col-md-8">
            <button type="submit" class="btn btn-dark w-100"><?php echo e(__("Save changes")); ?></button>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
     <script type="text/javascript">
         $(document).ready(function() {
            function formatTag(tag) {
                if (!tag.id) {
                    return tag.text;
                }
                var color = $(tag.element).data('color') || '#d1fae5';
                return $(
                    '<span class="fs-12" style="display:inline-block;padding:4px;border:none;background:' + color + ';color:#222;">' + tag.text + '</span>'
                );
            }
            $('#tags').select2({
                templateResult: formatTag,
                templateSelection: formatTag,
                escapeMarkup: function(m) { return m; },
                width: '100%',
                closeOnSelect: false,
            });
        });
     </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AdminBlogs/resources/views/update.blade.php ENDPATH**/ ?>