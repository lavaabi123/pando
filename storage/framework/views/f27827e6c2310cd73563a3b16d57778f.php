<h2><?php echo e(__('Welcome to :app', ['app' => get_option("website_title", config('site.title'))])); ?></h2>

<p><?php echo e(__('Hello, :name!', ['name' => $fullname ?? 'User'])); ?></p>

<p><?php echo e(__('We’re excited to have you on board. You can now explore your dashboard and start using all features.')); ?></p>

<div style="margin: 28px 0;">
    <a href="<?php echo e($login_url ?? url('/')); ?>" class="btn"
       style="background:#00b894; color:#fff; padding:12px 32px; border-radius:5px; text-decoration:none;">
        <?php echo e(__('Go to Dashboard')); ?>

    </a>
</div>

<p><?php echo e(__('Need help? Just reply to this email or contact support.')); ?></p><?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/Auth/resources/views/mail/welcome.blade.php ENDPATH**/ ?>