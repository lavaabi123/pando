<?php $__env->startSection('sub_header'); ?>
    <?php if (isset($component)) { $__componentOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6bfd7fd5c294530066e0efb20ff4cd9a = $attributes; } ?>
<?php $component = App\View\Components\SubHeader::resolve(['title' => ''.e(__('AI Configuration')).'','description' => ''.e(__('Set up and customize your AI settings easily')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sub-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\SubHeader::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
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

<div class="container max-w-800 pb-5">
    <form class="actionForm" action="<?php echo e(url_admin("settings/save")); ?>">
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6"><?php echo e(__("General configuration")); ?></div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label"><?php echo e(__('Status')); ?></label>
                            <select class="form-select" name="ai_status">
                                <option value="1" <?php echo e(get_option("ai_status", 1)==1?"selected":""); ?> ><?php echo e(__("Enable")); ?></option>
                                <option value="0" <?php echo e(get_option("ai_status", 1)==0?"selected":""); ?> ><?php echo e(__("Disable")); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label"><?php echo e(__('AI Platform')); ?></label>
                            <select class="form-select" name="ai_platform">
                                <?php foreach (AI::getPlatforms() as $key => $value): ?>
                                    <option value="<?php echo e($key); ?>" <?php echo e(get_option("ai_platform", "openai")==$key?"selected":""); ?> ><?php echo e($value); ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label"><?php echo e(__('Default Language')); ?></label>
                            <select class="form-select" name="ai_language">
                                <?php foreach (languages() as $key => $value): ?>
                                    <option value="<?php echo e($key); ?>" <?php echo e(get_option("ai_language", "en-US")==$key?"selected":""); ?> ><?php echo e($value); ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label"><?php echo e(__('Default Tone Of Voice')); ?></label>
                            <select class="form-select" name="ai_tone_of_voice">
                                <?php foreach (tone_of_voices() as $key => $value): ?>
                                    <option value="<?php echo e($key); ?>" <?php echo e(get_option("ai_tone_of_voice", "Friendly")==$key?"selected":""); ?> ><?php echo e($value); ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label"><?php echo e(__('Default Creativity')); ?></label>
                            <select class="form-select" name="ai_creativity">
                                <?php foreach (ai_creativity() as $key => $value): ?>
                                    <option value="<?php echo e($key); ?>" <?php echo e(get_option("ai_creativity", 0)==$key?"selected":""); ?>><?php echo e($value); ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label"><?php echo e(__('Maximum Input Length')); ?></label>
                            <input type="number" class="form-control" name="ai_max_input_lenght" value="<?php echo e(get_option("ai_max_input_lenght", 100)); ?>" >
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label"><?php echo e(__('Maximum Ouput Length')); ?></label>
                            <input type="number" class="form-control" name="ai_max_output_lenght" value="<?php echo e(get_option("ai_max_output_lenght", 1000)); ?>" >
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6"><?php echo e(__("OpenAI")); ?></div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label"><?php echo e(__('API Key')); ?></label>
                            <input placeholder="<?php echo e(__('Enter API Key')); ?>" class="form-control" name="ai_openai_api_key" id="ai_openai_api_key" type="text" value="<?php echo e(get_option("ai_openai_api_key", "")); ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><?php echo e(__('Default Model')); ?></label>
                        <select class="form-select" name="ai_openai_model">
                            <?php foreach (AI::getAvailableModels("openai") as $key => $value): ?>
                                <option value="<?php echo e($key); ?>" <?php echo e(get_option("ai_openai_model", "gpt-4-turbo")==$key?"selected":""); ?> ><?php echo e($value); ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6"><?php echo e(__("Gemini AI")); ?></div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label"><?php echo e(__('API Key')); ?></label>
                            <input placeholder="<?php echo e(__('Enter API Key')); ?>" class="form-control" name="ai_gemeni_api_key" id="ai_gemeni_api_key" type="text" value="<?php echo e(get_option("ai_gemeni_api_key", "")); ?>">
                        </div>

                        <div class="mb-4">
                            <label class="form-label"><?php echo e(__('Default Model')); ?></label>
                            <select class="form-select" name="ai_gemini_model">
                                <?php foreach (AI::getAvailableModels("gemini") as $key => $value): ?>
                                    <option value="<?php echo e($key); ?>" <?php echo e(get_option("ai_gemini_model", "gemini-2.5-flash")==$key?"selected":""); ?> ><?php echo e($value); ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6"><?php echo e(__("Deepseek AI")); ?></div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label"><?php echo e(__('API Key')); ?></label>
                            <input placeholder="<?php echo e(__('Enter API Key')); ?>" class="form-control" name="ai_deepseek_api_key" id="ai_deepseek_api_key" type="text" value="<?php echo e(get_option("ai_deepseek_api_key", "")); ?>">
                        </div>

                        <div class="mb-4">
                            <label class="form-label"><?php echo e(__('Default Model')); ?></label>
                            <select class="form-select" name="ai_deepseek_model">
                                <?php foreach (AI::getAvailableModels("deepseek") as $key => $value): ?>
                                    <option value="<?php echo e($key); ?>" <?php echo e(get_option("ai_deepseek_model", "deepseek-v3")==$key?"selected":""); ?> ><?php echo e($value); ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6"><?php echo e(__("Claude AI")); ?></div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label"><?php echo e(__('API Key')); ?></label>
                            <input placeholder="<?php echo e(__('Enter API Key')); ?>" class="form-control" name="ai_claude_api_key" id="ai_claude_api_key" type="text" value="<?php echo e(get_option("ai_claude_api_key", "")); ?>">
                        </div>

                        <div class="mb-4">
                            <label class="form-label"><?php echo e(__('Default Model')); ?></label>
                            <select class="form-select" name="ai_claude_model">
                                <?php foreach (AI::getAvailableModels("claude") as $key => $value): ?>
                                    <option value="<?php echo e($key); ?>" <?php echo e(get_option("ai_claude_model", "claude-3-sonnet-20240229")==$key?"selected":""); ?> ><?php echo e($value); ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-dark b-r-10 w-100">
                <?php echo e(__('Save changes')); ?>

            </button>
        </div>

    </form>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AdminAIConfiguration/resources/views/index.blade.php ENDPATH**/ ?>