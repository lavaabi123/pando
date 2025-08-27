<h2 style="color:#675dff;"><?php echo e(__('Activate Your Account')); ?></h2>

<p><?php echo e(__('Hello, :name!', ['name' => $fullname ?? 'User'])); ?></p>

<p>
    <?php echo e(__('Thank you for registering at :app.', ['app' => config('app.name')])); ?><br>
    <?php echo e(__('To activate your account, please click the button below:')); ?>

</p>

<div style="margin: 28px 0;">
    <a href="<?php echo e($verify_url ?? '#'); ?>" class="btn"
       style="background:#675dff; color:#fff; padding:12px 32px; border-radius:5px; text-decoration:none;">
        <?php echo e(__('Activate Account')); ?>

    </a>
</div>

<p>
    <?php echo e(__('If the button does not work, copy and paste the following link into your browser:')); ?><br>
    <a href="<?php echo e($verify_url ?? '#'); ?>"><?php echo e($verify_url ?? '#'); ?></a>
</p>

<p style="color:#888;"><?php echo e(__('If you did not create an account, please ignore this email.')); ?></p>
<?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/Auth/resources/views/mail/activation.blade.php ENDPATH**/ ?>