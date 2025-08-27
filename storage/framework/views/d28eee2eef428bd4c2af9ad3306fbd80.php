<h2 style="margin-top:0;"><?php echo e(__('You are invited to join a team!')); ?></h2>

<p>
    <?php echo e(__('Hello, :name!', ['name' => $fullname ?? 'User'])); ?>

</p>

<p>
    <?php echo __('You have been invited to join the team <b>:team</b>.', ['team' => $team_name ?? '-']); ?>

</p>

<div style="margin:24px 0;">
    <a href="<?php echo e($invite_url ?? config('app.url')); ?>" style="background:#675dff;color:#fff;padding:12px 32px;border-radius:5px;text-decoration:none;font-size:16px;">
        <?php echo e(__('Accept Invitation')); ?>

    </a>
</div>

<p style="color:#888;">
    <?php echo e(__('If you did not expect this email, you can safely ignore it.')); ?>

</p><?php /**PATH /home/royalinkdevelopm/public_html/pando.royalinkdevelopment.com/modules/AppTeams/resources/views/mail/invite.blade.php ENDPATH**/ ?>