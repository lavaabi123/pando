<?php
    $data = $result->data ?? [];
    $prompts = $result->prompts ?? [];
    $time_posts = $data['time_posts'] ?? [''];
?>



<?php $__env->startSection('sub_header'); ?>
    <?php if (isset($component)) { $__componentOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $attributes; } ?>
<?php $component = App\View\Components\SubHeader::resolve(['title' => ''.e(__('AI Publishing Campaign Editor')).'','description' => ''.e(__('Effortlessly create or edit automated AI publishing campaigns.')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sub-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\SubHeader::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <a class="btn btn-light btn-sm" href="<?php echo e(module_url("")); ?>">
            <span><i class="fa-light fa-angle-left"></i></span>
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
<form class="actionForm" action="<?php echo e(module_url("save")); ?>" method="POST" data-redirect="<?php echo e(module_url()); ?>">
    <div class="container max-w-800 pb-5">

         <div class="mb-5">
            <?php if($result): ?>
                <input class="form-control d-none" name="id" id="id" type="text" value="<?php echo e(old('name', $result->id_secure ?? '')); ?>">
            <?php endif; ?>

            <div class="border border-gray-200 b-r-6 p-3 mb-3">
                <label for="name" class="form-label"><?php echo e(__('Campaign name')); ?></label>
                <input 
                    placeholder="<?php echo e(__('Campaign name')); ?>" 
                    class="form-control" 
                    name="name" 
                    id="name" 
                    type="text" 
                    value="<?php echo e(old('name', $result->name ?? '')); ?>">
            </div>

            <div class="border border-gray-200 b-r-6 p-3 mb-3">
               <?php echo $__env->make('appchannels::block_channels', ['accounts' => $result->accounts ?? []], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            <?php $__currentLoopData = app('channels'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                <?php
                    $view = $value['key'].'::options';
                ?>

                <?php if(view()->exists($view)): ?>
                    <div class="d-none option-network" data-option-network="<?php echo e($value['social_network']); ?>">
                    <?php echo $__env->make($view, array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                <?php endif; ?>
                

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <div class="border border-gray-200 b-r-6 mb-3">
                <?php echo $__env->make('appaiprompts::block_prompts', ["prompts" => $prompts], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            <div class="border border-gray-200 b-r-6 mb-3">
                <div class="d-flex border-bottom p-3 align-items-center justify-content-between">
                    <div class="fw-5 fs-14"><?php echo e(__("Options")); ?></div>
                </div>

                <div class="row p-4">
                    <?php echo $__env->make("appaicontents::options", [
                        "include_media" => true,
                        "hashtags" => true,
                        "options" => $data
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>

            <div class="border border-gray-200 b-r-6 mb-3">
                <div class="d-flex border-bottom p-3 align-items-center justify-content-between">
                    <div class="fw-5 fs-14"><?php echo e(__("Schedule Regularly")); ?></div>
                </div>

                <div class="">
                    <div class="px-4 pt-4 listPostByTimes">
                        <?php if($data['time_posts']??false): ?>
                            <?php $__currentLoopData = ($data['time_posts'] ?? ['']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $time): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="input-group mb-3">
                                    <div class="form-control">
                                        <input class="onlytime" type="text" name="time_posts[]" value="<?php echo e($time); ?>">
                                        <span class="btn btn-icon">
                                            <i class="fa-light fa-calendar-days text-gray-600"></i>
                                        </span>
                                    </div>
                                    <span class="btn btn-input remove">
                                        <i class="fa-light fa-trash-can text-gray-900"></i>
                                    </span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <div class="input-group mb-3">
                                <div class="form-control">
                                    <input class="onlytime" type="text" name="time_posts[]" value="">
                                    <span class="btn btn-icon">
                                        <i class="fa-light fa-calendar-days text-gray-600"></i>
                                    </span>
                                </div>
                                <span class="btn btn-input remove">
                                    <i class="fa-light fa-trash-can text-gray-900"></i>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="px-4 pb-4">
                        <button type="button" class="btn btn-outline btn-dark w-100 addSpecificTimes">
                            <i class="fa-light fa-plus"></i> <?php echo e(__("Add time")); ?>

                        </button>
                    </div>
                    
                    <div class="tempPostByTimes d-none">
                        <div class="input-group mb-3">
                            <div class="form-control">
                                <input class="" type="text" value="">
                                <button class="btn btn-icon">
                                    <i class="fa-light fa-calendar-days text-gray-600"></i>
                                </button>
                            </div>
                            <span class="btn btn-input remove">
                                <i class="fa-light fa-trash-can text-gray-900"></i>
                            </span>
                        </div>
                    </div>

                    <div class="border-bottom border-top px-4 py-2 fs-12 bg-gray-100 text-gray-600">
                        <?php echo e(__("Schedule every")); ?>

                    </div>

                    <?php
                        $weekdays = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
                    ?>

                    <div class="p-3 overflow-x-auto">
                        <div class="d-flex gap-8 min-w-400">
                            <?php $__currentLoopData = $weekdays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label for="<?php echo e($value); ?>" class="flex-fill w-100 ratio-1x1 b-r-6 border border-gray-200 py-4 text-center bg-active-primary fw-3">
                                <?php echo e(__($value)); ?>  
                                <input class="form-check-input d-none" type="checkbox"  name="weekdays[<?php echo e($value); ?>]" value="1" id="<?php echo e($value); ?>" data-add-class="bg-primary text-white" <?php echo e((isset($data['weekdays'][$value]) && $data['weekdays'][$value]) ? 'checked' : ''); ?>>
                            </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <div class="border-bottom border-top px-4 py-2 fs-12 bg-gray-100 text-gray-600">
                        <?php echo e(__("End date")); ?>

                    </div>

                    <div class="p-3 overflow-x-auto">
                        <div class="form-control">
                            <input class="datetime" name="end_date" type="text" value="<?php echo e(old('end_date', (!empty($data['end_date']) ? datetime_show($data['end_date']) : ''))); ?>">
                            <span class="btn btn-icon">
                                <i class="fa-light fa-calendar-days text-gray-600"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-dark w-100"><?php echo e(__("Save changes")); ?></button>

        </div>
    </div>
</form>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('script'); ?>
    <?php $__env->startSection('script'); ?>
        <script type="text/javascript" src="<?php echo e(Module::asset(module("module_name").':resources/assets/js/ai_publishing.js')); ?>"></script>
    <?php $__env->stopSection(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AppAIPublishing/resources/views/update.blade.php ENDPATH**/ ?>