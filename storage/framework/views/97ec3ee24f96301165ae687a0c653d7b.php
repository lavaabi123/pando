<div class="header">
    <div class="container1 px-3 hp-100">
        <div class="hp-100 d-flex justify-content-between">
		
<?php
use Illuminate\Support\Facades\DB;

$userId = session('user_id');

// Get role once (role=2 => Super Admin)
$role = DB::table('users')->where('id', $userId)->value('role');

if ((int)$role === 2) {
    // SUPER ADMIN: see every brand
    $brands = DB::table('brands as b')
        ->leftJoin('brands_favorites as bf', function($join) use ($userId) {
            $join->on('bf.brand_id', '=', 'b.id')
                 ->where('bf.user_id', '=', $userId);
        })
        ->leftJoin('brands_recents as br', function($join) use ($userId) {
            $join->on('br.brand_id', '=', 'b.id')
                 ->where('br.user_id', '=', $userId);
        })
        ->select(
            'b.*',
            DB::raw('IF(bf.id IS NOT NULL, 1, 0) as is_favorite'),
            DB::raw('IF(br.id IS NOT NULL, 1, 0) as is_recent'),
            'br.changed as last_used_at'
        )
        ->orderBy('b.name') // Default alphabetic order
        ->get();
} else {
    // Determine if this user is a team member and get effective team_id
    $memberRow = DB::table('team_members')
        ->select('team_id')
        ->where('uid', $userId)
        ->first();
    
    $isMember = (bool) $memberRow;
    $teamId   = $isMember ? $memberRow->team_id : $userId;
    
    if (!$isMember) {
        // TEAM ADMIN: see all brands in this team
        $brands = DB::table('brands as b')
            ->leftJoin('brands_favorites as bf', function($join) use ($userId) {
                $join->on('bf.brand_id', '=', 'b.id')
                     ->where('bf.user_id', '=', $userId);
            })
            ->leftJoin('brands_recents as br', function($join) use ($userId) {
                $join->on('br.brand_id', '=', 'b.id')
                     ->where('br.user_id', '=', $userId);
            })
            ->where('b.team_id', $teamId)
            ->select(
                'b.*',
                DB::raw('IF(bf.id IS NOT NULL, 1, 0) as is_favorite'),
                DB::raw('IF(br.id IS NOT NULL, 1, 0) as is_recent'),
                'br.changed as last_used_at'
            )
            ->orderBy('b.name') // Default alphabetic order
            ->get();
    } else {
        // TEAM MEMBER: see brands created by me OR assigned to me (within team)
        $brands = DB::table('brands as b')
            ->leftJoin('user_brands as ub', function ($join) use ($userId, $teamId) {
                $join->on('ub.brand_id', '=', 'b.id')
                     ->where('ub.user_id', '=', $userId)
                     ->where('ub.team_id', '=', $teamId);
            })
            ->leftJoin('brands_favorites as bf', function($join) use ($userId) {
                $join->on('bf.brand_id', '=', 'b.id')
                     ->where('bf.user_id', '=', $userId);
            })
            ->leftJoin('brands_recents as br', function($join) use ($userId) {
                $join->on('br.brand_id', '=', 'b.id')
                     ->where('br.user_id', '=', $userId);
            })
            ->where('b.team_id', $teamId)
            ->where(function ($q) use ($userId) {
                $q->where('b.user_id', $userId)      // created by me
                  ->orWhereNotNull('ub.user_id');     // assigned to me
            })
            ->select(
                'b.*',
                DB::raw('IF(bf.id IS NOT NULL, 1, 0) as is_favorite'),
                DB::raw('IF(br.id IS NOT NULL, 1, 0) as is_recent'),
                'br.changed as last_used_at'
            )
            ->distinct()
            ->orderBy('b.name') // Default alphabetic order
            ->get();
    }
}
?>



            <div class="d-flex justify-content-between align-items-center">			
                <div class="d-block d-sm-block d-md-none">
                    <button class="btn btn-icon btn-light sidebar-toggle">
                        <i class="fa-light fa-chevron-right"></i>
                    </button>
                </div>
                <?php $__currentLoopData = \HeaderManager::getHeaderItems('start'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $headerItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $isVisible = $headerItem['visible'] ?? fn() => true;
                    ?>
                    <?php if($isVisible()): ?>
                        <?php echo is_callable($headerItem['item']) ? $headerItem['item']() : $headerItem['item']; ?>

                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php echo $__env->yieldContent('header_start'); ?>
            </div>

            <div class="d-flex flex-grow-1 justify-content-between wp-100">
				<ul class="top-menu d-flex align-items-center mb-0 ms-4">
					<li><a class="icons active" href="#"><?php echo file_get_contents(public_path('img/post.svg')); ?></a></li>
					<li><a class="icons" href="#"><?php echo file_get_contents(public_path('img/notification.svg')); ?></a></li>
					<li><a class="icons" href="#"><?php echo file_get_contents(public_path('img/add.svg')); ?></a></li>
					<li><a class="icons" href="#"><?php echo file_get_contents(public_path('img/note.svg')); ?></a></li>
					<li><a class="icons" href="#"><?php echo file_get_contents(public_path('img/inbox.svg')); ?></a></li>
				</ul>
				
                <?php $__currentLoopData = \HeaderManager::getHeaderItems('center'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $headerItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $isVisible = $headerItem['visible'] ?? fn() => true;
                    ?>
                    <?php if($isVisible()): ?>
                        <?php echo is_callable($headerItem['item']) ? $headerItem['item']() : $headerItem['item']; ?>

                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php echo $__env->yieldContent('header_center'); ?>
            </div>
			<div class="d-flex align-items-center">
    <p class="fs-14 fw-bold mb-0">Brand:</p>
    
    <!-- Custom Brand Dropdown -->
    <div class="custom-brand-dropdown" id="customBrandDropdown">
        <button type="button" class="brand-dropdown-toggle" id="brandDropdownToggle">
            <?php if(session('brand_id') && !empty($brands)): ?>
                <?php
                    $selectedBrand = collect($brands)->firstWhere('id', session('brand_id'));
                ?>
                <?php if($selectedBrand): ?>
                    <div class="selected-brand-display">
                        <?php if($selectedBrand->image): ?>
                            <img src="<?php echo e($selectedBrand->image); ?>" class="brand-avatar-small" alt="<?php echo e($selectedBrand->name); ?>">
                        <?php else: ?>
                            <div class="brand-avatar-placeholder-small"><?php echo e(strtoupper(substr($selectedBrand->name, 0, 1))); ?></div>
                        <?php endif; ?>
                        <span><?php echo e($selectedBrand->name); ?></span>
                    </div>
                <?php else: ?>
                    <span class="placeholder-text">Select Brand</span>
                <?php endif; ?>
            <?php else: ?>
                <span class="placeholder-text">Select Brand</span>
            <?php endif; ?>
            <i class="fas fa-chevron-down ms-auto"></i>
        </button>
        
        <div class="brand-dropdown-menu" id="brandDropdownMenu">
            <!-- Search Box -->
            <div class="brand-search-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" class="brand-search-input" id="brandSearchInput" placeholder="Search">
            </div>
            
            <!-- Filter Tabs -->
            <div class="brand-filters">
				<button type="button" class="filter-btn active" data-filter="alphabetic">Alphabetic</button>
				<button type="button" class="filter-btn" data-filter="recent">Recent</button>
				<button type="button" class="filter-btn" data-filter="favorite">Favorite</button>
			</div>
            
            <!-- Brand List -->
            <div class="brand-list" id="brandList">
                <?php if(!empty($brands)): ?>
                    <?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="brand-item" 
						 data-brand-id="<?php echo e($brand->id); ?>"
						 data-brand-name="<?php echo e(strtolower($brand->name)); ?>"
						 data-is-favorite="<?php echo e(!empty($brand->is_favorite) ? '1' : '0'); ?>"
						 data-is-recent="<?php echo e(!empty($brand->is_recent) ? '1' : '0'); ?>"
						 data-last-used-at="<?php echo e($brand->last_used_at ?? '0'); ?>">
						
						<!-- Avatar -->
						<div class="brand-avatar-wrapper">
							<?php if($brand->image): ?>
								<img src="<?php echo e(Media::url($brand->image)); ?>" class="brand-avatar" alt="<?php echo e($brand->name); ?>">
							<?php else: ?>
								<div class="brand-avatar-placeholder"><?php echo e(strtoupper(substr($brand->name, 0, 1))); ?></div>
							<?php endif; ?>
							
							<?php if(!empty($brand->unread_count) && $brand->unread_count > 0): ?>
								<span class="brand-badge"><?php echo e($brand->unread_count); ?></span>
							<?php endif; ?>
						</div>
						
						<!-- Brand Name (clickable area for selection) -->
						<span class="brand-name-text"><?php echo e($brand->name); ?></span>
						
						<!-- Action Buttons -->
						<div class="brand-actions">
							<button type="button" 
									class="brand-action-btn favorite-btn <?php echo e(!empty($brand->is_favorite) ? 'active' : ''); ?>" 
									data-brand-id="<?php echo e($brand->id); ?>"
									title="Toggle Favorite">
								<i class="<?php echo e(!empty($brand->is_favorite) ? 'fas' : 'far'); ?> fa-star"></i>
							</button>
							<button type="button" 
									class="brand-action-btn edit-btn" 
									data-brand-id="<?php echo e($brand->id); ?>"
									title="Edit Brand">
								<i class="far fa-edit"></i>
							</button>
							<button type="button" 
									class="brand-action-btn delete-btn" 
									data-brand-id="<?php echo e($brand->id); ?>"
									title="Delete Brand">
								<i class="far fa-trash-alt"></i>
							</button>
						</div>
					</div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </div>
            
            <!-- Add Brand Button -->
            <button type="button" class="add-brand-btn" id="addBrandBtn">
                <i class="fas fa-plus"></i> Add Brand
            </button>
        </div>
    </div>
</div>

<!-- Hidden input to store selected brand (for form submission if needed) -->
<input type="hidden" name="brand_id" id="selectedBrandId" value="<?php echo e(session('brand_id')); ?>">
            <div class="d-flex align-items-center gap-16">
                <?php echo $__env->yieldContent('header_end'); ?>

                <?php $__currentLoopData = \HeaderManager::getHeaderItems('end'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $headerItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $isVisible = $headerItem['visible'] ?? fn() => true;
                    ?>
                    <?php if($isVisible()): ?>
                        <?php echo is_callable($headerItem['item']) ? $headerItem['item']() : $headerItem['item']; ?>

                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                
            </div>
        </div>
    </div>
</div>

<style>
/* Custom Brand Dropdown */
.custom-brand-dropdown {
    position: relative;
    width: 100%;
    max-width: 300px;
    margin-left: 10px;
}

.brand-dropdown-toggle {
    width: 100%;
    padding: 10px 15px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    transition: all 0.2s;
}

.brand-dropdown-toggle:hover {
    border-color: #999;
}

.brand-dropdown-toggle:focus {
    outline: none;
    border-color: #4a90e2;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
}

.selected-brand-display {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
}

.brand-avatar-small {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    object-fit: cover;
}

.brand-avatar-placeholder-small {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background-color: #333;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 600;
}

.placeholder-text {
    color: #999;
    flex: 1;
}

.brand-dropdown-menu {
    position: absolute;
    top: calc(100% + 5px);
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    max-height: 500px;
    overflow: hidden;
    display: none;
    z-index: 1000;
}

.brand-dropdown-menu.active {
    display: block;
}

/* Search Box */
.brand-search-wrapper {
    padding: 15px;
    border-bottom: 1px solid #eee;
    display: flex;
    align-items: center;
    gap: 10px;
}

.brand-search-wrapper i {
    color: #999;
    font-size: 16px;
}

.brand-search-input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 14px;
    padding: 5px;
}

.brand-search-input::placeholder {
    color: #999;
}

/* Filter Tabs */
.brand-filters {
    display: flex;
    gap: 8px;
    padding: 15px;
    border-bottom: 1px solid #eee;
}

.filter-btn {
    padding: 8px 20px;
    border-radius: 20px;
    border: none;
    background-color: #e0e0e0;
    color: #333;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 14px;
    font-weight: 500;
}

.filter-btn:hover {
    background-color: #d0d0d0;
}

.filter-btn.active {
    background-color: #4a1a1a;
    color: white;
}

/* Brand List */
.brand-list {
    max-height: 350px;
    overflow-y: auto;
}

.brand-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.brand-item:hover {
    background-color: #f5f5f5;
}

.brand-avatar-wrapper {
    position: relative;
    flex-shrink: 0;
}

.brand-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: contain;
}

.brand-avatar-placeholder {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background-color: #333;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: 600;
}

.brand-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    background-color: #dc3545;
    color: white;
    border-radius: 50%;
    min-width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: bold;
    padding: 0 5px;
    border: 2px solid white;
}

.brand-name-text {
    flex: 1;
    font-size: 15px;
    color: #1a1a1a;
    font-weight: 500;
}

/* Action Buttons */
.brand-actions {
    display: flex;
    gap: 6px;
}

.brand-item:hover .brand-actions {
    opacity: 1;
}

.brand-action-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 1.5px solid #d0d0d0;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.brand-action-btn:hover {
    border-color: #999;
    background-color: #f5f5f5;
}

.brand-action-btn i {
    font-size: 14px;
    color: #666;
}

.brand-action-btn.favorite-btn.active {
    border-color: #ffc107;
    background-color: #fff9e6;
}

.brand-action-btn.favorite-btn.active i {
    color: #ffc107;
}

/* Add Brand Button */
.add-brand-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: calc(100% - 32px);
    margin: 16px;
    padding: 12px;
    background-color: #4a1a1a;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 15px;
    font-weight: 500;
    transition: all 0.2s;
}

.add-brand-btn:hover {
    background-color: #5a2a2a;
}

/* Scrollbar Styling */
.brand-list::-webkit-scrollbar {
    width: 8px;
}

.brand-list::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.brand-list::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.brand-list::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style><?php /**PATH C:\xampp82\htdocs\pando-laravel\resources\themes\app\pico\resources\views/partials/header.blade.php ENDPATH**/ ?>