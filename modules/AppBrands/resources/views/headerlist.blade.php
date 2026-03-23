@if(!empty($captions))
						@foreach($captions as $brand)
							<div class="brand-item" 
							 data-brand-id="{{ $brand->id }}"
							 data-brand-name="{{ strtolower($brand->name) }}"
							 data-is-favorite="{{ !empty($brand->is_favorite) ? '1' : '0' }}"
							 data-is-recent="{{ !empty($brand->is_recent) ? '1' : '0' }}"
							 data-last-used-at="{{ $brand->last_used_at ?? '0' }}">
							
							<!-- Avatar -->
							<div class="brand-avatar-wrapper">
								@if($brand->image)
									<img src="{{ Media::url($brand->image) }}" class="brand-avatar" alt="{{ $brand->name }}">
								@else
									<div class="brand-avatar-placeholder">{{ strtoupper(substr($brand->name, 0, 1)) }}</div>
								@endif
								
								<div class="brand-badge inbox-count-badge-{{ $brand->id }}" 
									  style="display: {{ (!empty($brand->unread_count) && $brand->unread_count > 0) ? 'inline-block' : 'none' }};">
									{{ (!empty($brand->unread_count) && $brand->unread_count > 99) ? '99+' : ($brand->unread_count ?? 0) }}
								</div>
							</div>
							
							<!-- Brand Name (clickable area for selection) -->
							<span class="brand-name-text">{{ $brand->name }}</span>
							
							<!-- Action Buttons -->
							<div class="brand-actions">
								<button type="button" 
										class="brand-action-btn favorite-btn {{ !empty($brand->is_favorite) ? 'active' : '' }}" 
										data-brand-id="{{ $brand->id }}"
										title="Toggle Favorite">
									<i class="{{ !empty($brand->is_favorite) ? 'fas' : 'far' }} fa-star"></i>
								</button>								
								<a title="Edit Brand" class="btn brand-action-btn actionItem" href="{{ route("app.brands.update") }}" data-popup="groupModal" data-id="{{ $brand->id_secure }}" data-call-success="Main.ajaxScroll(true,'brands');">								
									<i class="far fa-edit"></i>
								</a>
								<a title="Delete Brand" class="btn brand-action-btn actionItem" href="{{ route("app.brands.destroy") }}" data-confirm="All accounts under this brand will be removed and any scheduled posts will not be published. Are you sure?" data-id="{{ $brand->id_secure }}" data-call-success="Main.ajaxScroll(true);" data-redirect="<?php _ec( current_url() )?>">
									<i class="far fa-trash-alt"></i>
								</a>
							</div>
						</div>
						@endforeach
					@endif