<div class="modal fade" id="inviteModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
		<form class="modal-content actionForm" action="<?php echo e(module_url("send-invite")); ?>" data-call-success="Main.closeModal('inviteModal'); Main.ajaxScroll(true);">
			<input type="text" class="d-none" name="type" value="0">
			<div class="modal-header">
				<h1 class="modal-title fs-16"><?php echo e(__("Add User")); ?></h1>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body p-4">
                <?php
			        $permissions = $team->permissions ?? '';
			        $permissions = groupArray($permissions);
			    ?>

         		<div class="msg-errors"></div>
 				<div class="row">
 					<div class="col-md-12">
						<!-- Full Name -->
						<div class="mb-2">
							<label for="fullname" class="form-label"><?php echo e(__("Full Name")); ?></label>
							<input type="text" value="" name="fullname" class="form-control" placeholder="<?php echo e(__('Enter full name')); ?>" required>
						</div>
						<!-- Username -->
						<div class="mb-2">
							<label for="username" class="form-label"><?php echo e(__("Username")); ?></label>
							<input type="text" id="username" name="username" class="form-control" placeholder="<?php echo e(__('Choose a username')); ?>" required>
						</div>	
						<!-- Email -->						
 						<div class="mb-2">
		                  	<label for="email" class="form-label"><?php echo e(__('User Email')); ?></label>
	                     	<input placeholder="<?php echo e(__('Enter user email address')); ?>" class="form-control" name="email" id="email" type="email" value="" required>
						</div>
						<!-- Password -->
						<div class="mb-2">
							<label for="password" class="form-label"><?php echo e(__("Password")); ?></label>
							<input type="password" id="password" name="password" class="form-control" placeholder="<?php echo e(__('Enter your password')); ?>" required>
						</div>
						<!-- Confirm Password -->
						<div class="mb-4">
							<label for="password_confirmation" class="form-label"><?php echo e(__("Confirm Password")); ?></label>
							<input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="<?php echo e(__('Re-enter your password')); ?>" required>
						</div>
		                <div class="mb-4 per-all">
							<label for="name" class="form-label"><?php echo e(__('Select permissions')); ?></label>
							<div class="mb-3">
					            <div class="input-group">
					                <div class="form-control">
				                     	<i class="fa-light fa-magnifying-glass"></i>
				                     	<input placeholder="<?php echo e(__("Search")); ?>" type="text" data-search="search-per" class="search-input" value="">
					                </div>
					                <span class="btn btn-icon btn-input min-w-55">
					                    <input class="form-check-input checkbox-all" data-checkbox-parent=".per-all" type="checkbox" value="">
					                </span>
					            </div>
							</div>
	 						<div class="mb-4 pf-0 b-r-4">
	 							<?php if($permissions): ?>
	 							<?php
	 								$selected_permissions = [];
	 							?>

			                  	<ul class="list-group border overflow-y-scroll max-h-350">
			                  		<?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

			                  			<?php if(Module::find($value['key']) && $value['key'] != 'appteams'): ?>
			                  				
				                  			<li class="search-per search-list border-start-2 border-primary">
				                  				<input type="hidden" name="team_permissions[]" value="<?php echo e($value['key']); ?>">
						                        <div class="list-group-item border-start-0 border-end-0 border-top-0 d-flex justify-content-between align-items-center gap-8">
											  		<label  class="mt-1 fs-14 d-flex align-items-center gap-8 text-truncate" for="id_<?php echo e($value['key']); ?>">
											  			<div class="size-26 min-w-26 border b-r-6 d-flex justify-content-between align-items-center text-center bg-gray-100 text-success fs-14 border-success-200">
											  				<i class="fa-light fa-key wp-100"></i>
											  			</div>
											  			<div class="text-truncate">
											  				<div class="fs-12 lh-sm mb-1 fw-5"><?php echo e($value['label']); ?></div>
											  			</div>
											  		</label>
											  		<span>
											  			<input class="form-check-input checkbox-item" type="checkbox" name="permissions[]" value="<?php echo e($value['key']); ?>" id="id_<?php echo e($value['key']); ?>" <?php echo e(__( in_array($value['key'], $selected_permissions)?"checked":"" )); ?> >
											  		</span>
											  	</div>
				                  			</li>

				                  			<?php if(isset($value['children'])): ?>

					                  			<?php $__currentLoopData = $value['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $children): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

					                  				<?php if((int)$children['value'] == 1): ?>
						                  				<li class=" search-list">
					                  						<input type="hidden" name="team_permissions[]" value="<?php echo e($children['key']); ?>">
									                        <div class="list-group-item border-end-0 border-top-0 d-flex justify-content-between align-items-center gap-8  border-start-2 ps-5">
														  		<label  class="mt-1 fs-14 d-flex align-items-center gap-8 text-truncate" for="id_<?php echo e($children['key']); ?>">
														  			<div class="size-26 min-w-26 border b-r-6 d-flex justify-content-between align-items-center text-center bg-primary-100 text-primary fs-14 border-primary-200">
														  				<i class="fa-light fa-key wp-100"></i>
														  			</div>
														  			<div class="text-truncate">
														  				<div class="fs-12 lh-sm mb-1 fw-5"><?php echo e($children['label']); ?></div>
														  			</div>
														  		</label>
														  		<span>
														  			<input class="form-check-input checkbox-item" type="checkbox" name="permissions[]" value="<?php echo e($children['key']); ?>" id="id_<?php echo e($children['key']); ?>" <?php echo e(__( in_array($children['key'], $selected_permissions)?"checked":"" )); ?> >
														  		</span>
														  	</div>
							                  			</li>
							                  		<?php endif; ?>

					                  			<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

				                  			<?php endif; ?>

				                  		<?php endif; ?>
			                  		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</ul>
								<?php else: ?>
								<div class="empty"></div>
								<?php endif; ?>
			                </div>
		                </div>

 					</div>
					
                        <div class="mb-4 brand-all">
                            <label for="name" class="form-label"><?php echo e(__('Assign Brands')); ?></label>
                            <div class="mb-3">
                                <div class="input-group">
                                    <div class="form-control">
                                        <i class="fa-light fa-magnifying-glass"></i>
                                        <input placeholder="<?php echo e(__("Search")); ?>" type="text" data-search="search-brand" class="search-input" value="">
                                    </div>
                                    <span class="btn btn-icon btn-input min-w-55">
                                        <input class="form-check-input checkbox-all" data-checkbox-parent=".brand-all" type="checkbox" value="">
                                    </span>
                                </div>
                            </div>
                            <div class="mb-4 pf-0 b-r-4">
                                <?php if($brands): ?>
								<?php
                                    $selected_brands =  [];
                                ?>
                                <ul class="list-group border overflow-y-scroll max-h-350">
                                    <?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                            <li class="search-brand border-start-2 border-primary">
                                                <input type="hidden" name="brand_permissions[]" value="<?php echo e($value->id); ?>">
                                                <div class="list-group-item border-start-0 border-end-0 border-top-0 d-flex justify-content-between align-items-center gap-8">
                                                    <label  class="mt-1 fs-14 d-flex align-items-center gap-8 text-truncate" for="id_<?php echo e($value->id); ?>">
                                                        <div class="size-26 min-w-26 border b-r-6 d-flex justify-content-between align-items-center text-center bg-gray-100 text-success fs-14 border-success-200">
                                                            <i class="fa-light fa-key wp-100"></i>
                                                        </div>
                                                        <div class="text-truncate">
                                                            <div class="fs-12 lh-sm mb-1 fw-5"><?php echo e($value->name); ?></div>
                                                        </div>
                                                    </label>
                                                    <span>
                                                        <input class="form-check-input checkbox-item" type="checkbox" name="brands[]" value="<?php echo e($value->id); ?>" id="id_<?php echo e($value->id); ?>" <?php echo e(__( in_array($value->id, $selected_brands)?"checked":"" )); ?> >
                                                    </span>
                                                </div>
                                            </li>

                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                <?php else: ?>
                                <div class="empty"></div>
                                <?php endif; ?>
                            </div>
                        </div>

 				</div>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('Close')); ?></button>
				<button type="submit" class="btn btn-dark"><?php echo e(__('Submit')); ?></button>
			</div>
		</form>
	</div>
</div><?php /**PATH C:\xampp82\htdocs\pando-laravel\modules/AppTeams\resources/views/invite.blade.php ENDPATH**/ ?>