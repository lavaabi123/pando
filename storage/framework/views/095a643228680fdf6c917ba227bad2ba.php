<?php $__currentLoopData = \AppDashboard::getDashboardItems(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dashboardItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
        $isVisible = $dashboardItem['visible'] ?? fn() => true;
    ?>
    <?php if($isVisible()): ?>
        <?php echo is_callable($dashboardItem['item']) ? $dashboardItem['item']() : $dashboardItem['item']; ?>

    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<div class="col-md-6 mb-4">
	    <div class="card h-100 b-r-15 mb-4 border">
	        <div class="card-header">
	            <div class="card-title my-3">Recent publications</div>
	        </div>
	        <div class="card-body py-0 px-4">
	        	<div class="schedules-main overflow-auto row mt-4 mh-600 h-100">
				    <div class="schedule-list h-100">
				    						    	
							    							    <div class="card border px-0 item mb-4 b-r-15 overflow-hidden">

							        							            <div class="ribbon ribbon-triangle ribbon-top-end border-success rounded">
							                <div class="ribbon-icon mn-t-20 mn-l-2">
							                    <i class="fs-20 fad fa-check-double fs-2 text-white"></i>
							                </div>
							            </div>

							            <!--<div class="border-success border-top-dashed border-1"></div>-->
							        							        
							        <div class="card-header px-4 border-0 pt-4">
							            
							            <div class="card-title fw-normal m-0 fs-12">
							                
							                <div class="d-flex flex-stack">
							                    <div class="symbol symbol-45px me-3">
							                        <img src="https://itspando.com/writable/avatar/672ff0ef06c1e.jpg" class="align-self-center rounded-circle border" alt="">
							                    </div>
							                    <div class="d-flex align-items-center flex-row-fluid flex-wrap">
							                        <div class="flex-grow-1 me-2 text-over-all">
							                            <a href="https://www.facebook.com/153908811306053" target="_blank" class="text-gray-800 text-hover-primary fs-14 fw-bold"><i class="" style="color: ;"></i> USA Berkey Filters</a>
							                            <span class="text-muted fw-semibold d-block fs-12"><i class="fal fa-calendar-alt"></i> 11/29/2025 1:00 PM</span>
							                        </div>
							                    </div>
							                </div>

							            </div>

							        </div>

							        <div class="card-body p-20">
							            
							            <div class="d-flex">
							                <div class="symbol symbol-80 me-3 overflow-hidden w-80 h-80 border b-r-15">

							                    							                        							                        <div class="owl-carousel owl-theme owl-loaded owl-drag">
							                            							                                
							                                							                                    
							                                
							                            							                        <div class="owl-stage-outer"><div class="owl-stage" style="transform: translate3d(-156px, 0px, 0px); transition: all; width: 392px;"><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764180683_d13afc5ac93ab4ef901a.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764180683_d13afc5ac93ab4ef901a.png');"></div></div><div class="owl-item active" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764180683_d13afc5ac93ab4ef901a.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764180683_d13afc5ac93ab4ef901a.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764180683_d13afc5ac93ab4ef901a.png');"></div></div></div></div><div class="owl-nav disabled"><button type="button" role="presentation" class="owl-prev"><span aria-label="Previous">‹</span></button><button type="button" role="presentation" class="owl-next"><span aria-label="Next">›</span></button></div><div class="owl-dots disabled"><button role="button" class="owl-dot active"><span></span></button></div></div>
							                        
							                    
							                </div>
							                <div class="d-flex flex-row-fluid flex-wrap">
							                    <div class="flex-grow-1 me-2">
							                        <span class="text-gray-600 d-block overflow-auto">
							                            Is your Berkey System fitted to protect against fluoride? 🚰<br>
<br>
Remove up to 99.75% of fluoride, and the full targeting of arsenic with the help of our PF-2 Fluoride and Arsenic Reduction Elements! 🙌<br>
🔗: https://www.usaberkeyfilters.com/products/pf2-fluoride-filter/							                        </span>
							                    </div>
							                </div>
							            </div>

							        </div>

							        
							            
							            <div class="card-footer bg-light-success text-success py-3 px-4 d-flex justify-content-between">
							                <span class="me-2">Post Published</span> <a href="https://fb.com/1305674761593520" class="text-dark text-hover-primary" target="_blank"><i class="fad fa-eye"></i> View post</a>
							            </div>
							        
							        
							        
							    </div>

							
							    							    <div class="card border px-0 item mb-4 b-r-15 overflow-hidden">

							        							            <div class="ribbon ribbon-triangle ribbon-top-end border-success rounded">
							                <div class="ribbon-icon mn-t-20 mn-l-2">
							                    <i class="fs-20 fad fa-check-double fs-2 text-white"></i>
							                </div>
							            </div>

							            <!--<div class="border-success border-top-dashed border-1"></div>-->
							        							        
							        <div class="card-header px-4 border-0 pt-4">
							            
							            <div class="card-title fw-normal m-0 fs-12">
							                
							                <div class="d-flex flex-stack">
							                    <div class="symbol symbol-45px me-3">
							                        <img src="https://itspando.com/writable/avatar/683f4dd6615c9.jpg" class="align-self-center rounded-circle border" alt="">
							                    </div>
							                    <div class="d-flex align-items-center flex-row-fluid flex-wrap">
							                        <div class="flex-grow-1 me-2 text-over-all">
							                            <a href="https://twitter.com/usaberkey" target="_blank" class="text-gray-800 text-hover-primary fs-14 fw-bold"><i class="" style="color: ;"></i> USA Berkey Filters</a>
							                            <span class="text-muted fw-semibold d-block fs-12"><i class="fal fa-calendar-alt"></i> 11/29/2025 1:00 PM</span>
							                        </div>
							                    </div>
							                </div>

							            </div>

							        </div>

							        <div class="card-body p-20">
							            
							            <div class="d-flex">
							                <div class="symbol symbol-80 me-3 overflow-hidden w-80 h-80 border b-r-15">

							                    							                        							                        <div class="owl-carousel owl-theme owl-loaded owl-drag">
							                            							                                
							                                							                                    
							                                
							                            							                        <div class="owl-stage-outer"><div class="owl-stage" style="transform: translate3d(-156px, 0px, 0px); transition: all; width: 392px;"><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764180683_d13afc5ac93ab4ef901a.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764180683_d13afc5ac93ab4ef901a.png');"></div></div><div class="owl-item active" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764180683_d13afc5ac93ab4ef901a.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764180683_d13afc5ac93ab4ef901a.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764180683_d13afc5ac93ab4ef901a.png');"></div></div></div></div><div class="owl-nav disabled"><button type="button" role="presentation" class="owl-prev"><span aria-label="Previous">‹</span></button><button type="button" role="presentation" class="owl-next"><span aria-label="Next">›</span></button></div><div class="owl-dots disabled"><button role="button" class="owl-dot active"><span></span></button></div></div>
							                        
							                    
							                </div>
							                <div class="d-flex flex-row-fluid flex-wrap">
							                    <div class="flex-grow-1 me-2">
							                        <span class="text-gray-600 d-block overflow-auto">
							                            Is your Berkey System fitted to protect against fluoride? 🚰<br>
<br>
Remove up to 99.75% of fluoride, and the full targeting of arsenic with the help of our PF-2 Fluoride and Arsenic Reduction Elements! 🙌<br>
🔗: https://www.usaberkeyfilters.com/products/pf2-fluoride-filter/							                        </span>
							                    </div>
							                </div>
							            </div>

							        </div>

							        
							            
							            <div class="card-footer bg-light-success text-success py-3 px-4 d-flex justify-content-between">
							                <span class="me-2">Post Published</span> <a href="https://twitter.com/usaberkey/status/1994874061495697817" class="text-dark text-hover-primary" target="_blank"><i class="fad fa-eye"></i> View post</a>
							            </div>
							        
							        
							        
							    </div>

							
							    							    <div class="card border px-0 item mb-4 b-r-15 overflow-hidden">

							        							            <div class="ribbon ribbon-triangle ribbon-top-end border-success rounded">
							                <div class="ribbon-icon mn-t-20 mn-l-2">
							                    <i class="fs-20 fad fa-check-double fs-2 text-white"></i>
							                </div>
							            </div>

							            <!--<div class="border-success border-top-dashed border-1"></div>-->
							        							        
							        <div class="card-header px-4 border-0 pt-4">
							            
							            <div class="card-title fw-normal m-0 fs-12">
							                
							                <div class="d-flex flex-stack">
							                    <div class="symbol symbol-45px me-3">
							                        <img src="https://itspando.com/writable/avatar/672ff15d53462.jpg" class="align-self-center rounded-circle border" alt="">
							                    </div>
							                    <div class="d-flex align-items-center flex-row-fluid flex-wrap">
							                        <div class="flex-grow-1 me-2 text-over-all">
							                            <a href="https://www.instagram.com/usaberkeyfilters" target="_blank" class="text-gray-800 text-hover-primary fs-14 fw-bold"><i class="" style="color: ;"></i> USA Berkey® Filters | Berkey Water | Clean Water</a>
							                            <span class="text-muted fw-semibold d-block fs-12"><i class="fal fa-calendar-alt"></i> 11/29/2025 1:00 PM</span>
							                        </div>
							                    </div>
							                </div>

							            </div>

							        </div>

							        <div class="card-body p-20">
							            
							            <div class="d-flex">
							                <div class="symbol symbol-80 me-3 overflow-hidden w-80 h-80 border b-r-15">

							                    							                        							                        <div class="owl-carousel owl-theme owl-loaded owl-drag">
							                            							                                
							                                							                                    
							                                
							                            							                        <div class="owl-stage-outer"><div class="owl-stage" style="transform: translate3d(-156px, 0px, 0px); transition: all; width: 392px;"><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764180683_d13afc5ac93ab4ef901a.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764180683_d13afc5ac93ab4ef901a.png');"></div></div><div class="owl-item active" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764180683_d13afc5ac93ab4ef901a.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764180683_d13afc5ac93ab4ef901a.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764180683_d13afc5ac93ab4ef901a.png');"></div></div></div></div><div class="owl-nav disabled"><button type="button" role="presentation" class="owl-prev"><span aria-label="Previous">‹</span></button><button type="button" role="presentation" class="owl-next"><span aria-label="Next">›</span></button></div><div class="owl-dots disabled"><button role="button" class="owl-dot active"><span></span></button></div></div>
							                        
							                    
							                </div>
							                <div class="d-flex flex-row-fluid flex-wrap">
							                    <div class="flex-grow-1 me-2">
							                        <span class="text-gray-600 d-block overflow-auto">
							                            Is your Berkey System fitted to protect against fluoride? 🚰<br>
<br>
Remove up to 99.75% of fluoride, and the full targeting of arsenic with the help of our PF-2 Fluoride and Arsenic Reduction Elements! 🙌<br>
🔗: Link in bio!							                        </span>
							                    </div>
							                </div>
							            </div>

							        </div>

							        
							            
							            <div class="card-footer bg-light-success text-success py-3 px-4 d-flex justify-content-between">
							                <span class="me-2">Post Published</span> <a href="https://www.instagram.com/p/DRp5kGMkzSP" class="text-dark text-hover-primary" target="_blank"><i class="fad fa-eye"></i> View post</a>
							            </div>
							        
							        
							        
							    </div>

							
							    							    <div class="card border px-0 item mb-4 b-r-15 overflow-hidden">

							        							            <div class="ribbon ribbon-triangle ribbon-top-end border-success rounded">
							                <div class="ribbon-icon mn-t-20 mn-l-2">
							                    <i class="fs-20 fad fa-check-double fs-2 text-white"></i>
							                </div>
							            </div>

							            <!--<div class="border-success border-top-dashed border-1"></div>-->
							        							        
							        <div class="card-header px-4 border-0 pt-4">
							            
							            <div class="card-title fw-normal m-0 fs-12">
							                
							                <div class="d-flex flex-stack">
							                    <div class="symbol symbol-45px me-3">
							                        <img src="https://itspando.com/writable/avatar/672ff0ef06c1e.jpg" class="align-self-center rounded-circle border" alt="">
							                    </div>
							                    <div class="d-flex align-items-center flex-row-fluid flex-wrap">
							                        <div class="flex-grow-1 me-2 text-over-all">
							                            <a href="https://www.facebook.com/153908811306053" target="_blank" class="text-gray-800 text-hover-primary fs-14 fw-bold"><i class="" style="color: ;"></i> USA Berkey Filters</a>
							                            <span class="text-muted fw-semibold d-block fs-12"><i class="fal fa-calendar-alt"></i> 11/28/2025 11:00 AM</span>
							                        </div>
							                    </div>
							                </div>

							            </div>

							        </div>

							        <div class="card-body p-20">
							            
							            <div class="d-flex">
							                <div class="symbol symbol-80 me-3 overflow-hidden w-80 h-80 border b-r-15">

							                    							                        							                        <div class="owl-carousel owl-theme owl-loaded owl-drag">
							                            							                                
							                                							                                    
							                                
							                            							                        <div class="owl-stage-outer"><div class="owl-stage" style="transform: translate3d(-156px, 0px, 0px); transition: all; width: 392px;"><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764181006_4ddcef3740b5debe6a51.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764181006_4ddcef3740b5debe6a51.png');"></div></div><div class="owl-item active" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764181006_4ddcef3740b5debe6a51.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764181006_4ddcef3740b5debe6a51.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764181006_4ddcef3740b5debe6a51.png');"></div></div></div></div><div class="owl-nav disabled"><button type="button" role="presentation" class="owl-prev"><span aria-label="Previous">‹</span></button><button type="button" role="presentation" class="owl-next"><span aria-label="Next">›</span></button></div><div class="owl-dots disabled"><button role="button" class="owl-dot active"><span></span></button></div></div>
							                        
							                    
							                </div>
							                <div class="d-flex flex-row-fluid flex-wrap">
							                    <div class="flex-grow-1 me-2">
							                        <span class="text-gray-600 d-block overflow-auto">
							                            Sooo who else already has their Christmas decorations up? 😬✋<br>
<br>
We can't wait to see all of your holiday-themed Berkeys! 🎄							                        </span>
							                    </div>
							                </div>
							            </div>

							        </div>

							        
							            
							            <div class="card-footer bg-light-success text-success py-3 px-4 d-flex justify-content-between">
							                <span class="me-2">Post Published</span> <a href="https://fb.com/1304786335015696" class="text-dark text-hover-primary" target="_blank"><i class="fad fa-eye"></i> View post</a>
							            </div>
							        
							        
							        
							    </div>

							
							    							    <div class="card border px-0 item mb-4 b-r-15 overflow-hidden">

							        							            <div class="ribbon ribbon-triangle ribbon-top-end border-success rounded">
							                <div class="ribbon-icon mn-t-20 mn-l-2">
							                    <i class="fs-20 fad fa-check-double fs-2 text-white"></i>
							                </div>
							            </div>

							            <!--<div class="border-success border-top-dashed border-1"></div>-->
							        							        
							        <div class="card-header px-4 border-0 pt-4">
							            
							            <div class="card-title fw-normal m-0 fs-12">
							                
							                <div class="d-flex flex-stack">
							                    <div class="symbol symbol-45px me-3">
							                        <img src="https://itspando.com/writable/avatar/672ff15d53462.jpg" class="align-self-center rounded-circle border" alt="">
							                    </div>
							                    <div class="d-flex align-items-center flex-row-fluid flex-wrap">
							                        <div class="flex-grow-1 me-2 text-over-all">
							                            <a href="https://www.instagram.com/usaberkeyfilters" target="_blank" class="text-gray-800 text-hover-primary fs-14 fw-bold"><i class="" style="color: ;"></i> USA Berkey® Filters | Berkey Water | Clean Water</a>
							                            <span class="text-muted fw-semibold d-block fs-12"><i class="fal fa-calendar-alt"></i> 11/28/2025 11:00 AM</span>
							                        </div>
							                    </div>
							                </div>

							            </div>

							        </div>

							        <div class="card-body p-20">
							            
							            <div class="d-flex">
							                <div class="symbol symbol-80 me-3 overflow-hidden w-80 h-80 border b-r-15">

							                    							                        							                        <div class="owl-carousel owl-theme owl-loaded owl-drag">
							                            							                                
							                                							                                    
							                                
							                            							                        <div class="owl-stage-outer"><div class="owl-stage" style="transform: translate3d(-156px, 0px, 0px); transition: all; width: 392px;"><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764181006_4ddcef3740b5debe6a51.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764181006_4ddcef3740b5debe6a51.png');"></div></div><div class="owl-item active" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764181006_4ddcef3740b5debe6a51.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764181006_4ddcef3740b5debe6a51.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764181006_4ddcef3740b5debe6a51.png');"></div></div></div></div><div class="owl-nav disabled"><button type="button" role="presentation" class="owl-prev"><span aria-label="Previous">‹</span></button><button type="button" role="presentation" class="owl-next"><span aria-label="Next">›</span></button></div><div class="owl-dots disabled"><button role="button" class="owl-dot active"><span></span></button></div></div>
							                        
							                    
							                </div>
							                <div class="d-flex flex-row-fluid flex-wrap">
							                    <div class="flex-grow-1 me-2">
							                        <span class="text-gray-600 d-block overflow-auto">
							                            Sooo who else already has their Christmas decorations up? 😬✋<br>
<br>
We can't wait to see all of your holiday-themed Berkeys! 🎄							                        </span>
							                    </div>
							                </div>
							            </div>

							        </div>

							        
							            
							            <div class="card-footer bg-light-success text-success py-3 px-4 d-flex justify-content-between">
							                <span class="me-2">Post Published</span> <a href="https://www.instagram.com/p/DRnHNgfkxiO" class="text-dark text-hover-primary" target="_blank"><i class="fad fa-eye"></i> View post</a>
							            </div>
							        
							        
							        
							    </div>

							
							    							    <div class="card border px-0 item mb-4 b-r-15 overflow-hidden">

							        							            <div class="ribbon ribbon-triangle ribbon-top-end border-success rounded">
							                <div class="ribbon-icon mn-t-20 mn-l-2">
							                    <i class="fs-20 fad fa-check-double fs-2 text-white"></i>
							                </div>
							            </div>

							            <!--<div class="border-success border-top-dashed border-1"></div>-->
							        							        
							        <div class="card-header px-4 border-0 pt-4">
							            
							            <div class="card-title fw-normal m-0 fs-12">
							                
							                <div class="d-flex flex-stack">
							                    <div class="symbol symbol-45px me-3">
							                        <img src="https://itspando.com/writable/avatar/682de1f9e5d2e.png" class="align-self-center rounded-circle border" alt="">
							                    </div>
							                    <div class="d-flex align-items-center flex-row-fluid flex-wrap">
							                        <div class="flex-grow-1 me-2 text-over-all">
							                            <a href="https://www.pinterest.com/usaberkeyfilters" target="_blank" class="text-gray-800 text-hover-primary fs-14 fw-bold"><i class="" style="color: ;"></i> http://usaberkeyfilters.com</a>
							                            <span class="text-muted fw-semibold d-block fs-12"><i class="fal fa-calendar-alt"></i> 11/28/2025 11:00 AM</span>
							                        </div>
							                    </div>
							                </div>

							            </div>

							        </div>

							        <div class="card-body p-20">
							            
							            <div class="d-flex">
							                <div class="symbol symbol-80 me-3 overflow-hidden w-80 h-80 border b-r-15">

							                    							                        							                        <div class="owl-carousel owl-theme owl-loaded owl-drag">
							                            							                                
							                                							                                    
							                                
							                            							                        <div class="owl-stage-outer"><div class="owl-stage" style="transform: translate3d(-156px, 0px, 0px); transition: all; width: 392px;"><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764181006_4ddcef3740b5debe6a51.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764181006_4ddcef3740b5debe6a51.png');"></div></div><div class="owl-item active" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764181006_4ddcef3740b5debe6a51.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764181006_4ddcef3740b5debe6a51.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764181006_4ddcef3740b5debe6a51.png');"></div></div></div></div><div class="owl-nav disabled"><button type="button" role="presentation" class="owl-prev"><span aria-label="Previous">‹</span></button><button type="button" role="presentation" class="owl-next"><span aria-label="Next">›</span></button></div><div class="owl-dots disabled"><button role="button" class="owl-dot active"><span></span></button></div></div>
							                        
							                    
							                </div>
							                <div class="d-flex flex-row-fluid flex-wrap">
							                    <div class="flex-grow-1 me-2">
							                        <span class="text-gray-600 d-block overflow-auto">
							                            Sooo who else already has their Christmas decorations up? 😬✋<br>
<br>
We can't wait to see all of your holiday-themed Berkeys! 🎄							                        </span>
							                    </div>
							                </div>
							            </div>

							        </div>

							        
							            
							            <div class="card-footer bg-light-success text-success py-3 px-4 d-flex justify-content-between">
							                <span class="me-2">Post Published</span> <a href="https://www.pinterest.com/pin/577657089746346444/" class="text-dark text-hover-primary" target="_blank"><i class="fad fa-eye"></i> View post</a>
							            </div>
							        
							        
							        
							    </div>

							
							    							    <div class="card border px-0 item mb-4 b-r-15 overflow-hidden">

							        							            <div class="ribbon ribbon-triangle ribbon-top-end border-success rounded">
							                <div class="ribbon-icon mn-t-20 mn-l-2">
							                    <i class="fs-20 fad fa-check-double fs-2 text-white"></i>
							                </div>
							            </div>

							            <!--<div class="border-success border-top-dashed border-1"></div>-->
							        							        
							        <div class="card-header px-4 border-0 pt-4">
							            
							            <div class="card-title fw-normal m-0 fs-12">
							                
							                <div class="d-flex flex-stack">
							                    <div class="symbol symbol-45px me-3">
							                        <img src="https://itspando.com/writable/avatar/683f4dd6615c9.jpg" class="align-self-center rounded-circle border" alt="">
							                    </div>
							                    <div class="d-flex align-items-center flex-row-fluid flex-wrap">
							                        <div class="flex-grow-1 me-2 text-over-all">
							                            <a href="https://twitter.com/usaberkey" target="_blank" class="text-gray-800 text-hover-primary fs-14 fw-bold"><i class="" style="color: ;"></i> USA Berkey Filters</a>
							                            <span class="text-muted fw-semibold d-block fs-12"><i class="fal fa-calendar-alt"></i> 11/28/2025 11:00 AM</span>
							                        </div>
							                    </div>
							                </div>

							            </div>

							        </div>

							        <div class="card-body p-20">
							            
							            <div class="d-flex">
							                <div class="symbol symbol-80 me-3 overflow-hidden w-80 h-80 border b-r-15">

							                    							                        							                        <div class="owl-carousel owl-theme owl-loaded owl-drag">
							                            							                                
							                                							                                    
							                                
							                            							                        <div class="owl-stage-outer"><div class="owl-stage" style="transform: translate3d(-156px, 0px, 0px); transition: all; width: 392px;"><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764181006_4ddcef3740b5debe6a51.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764181006_4ddcef3740b5debe6a51.png');"></div></div><div class="owl-item active" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764181006_4ddcef3740b5debe6a51.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764181006_4ddcef3740b5debe6a51.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764181006_4ddcef3740b5debe6a51.png');"></div></div></div></div><div class="owl-nav disabled"><button type="button" role="presentation" class="owl-prev"><span aria-label="Previous">‹</span></button><button type="button" role="presentation" class="owl-next"><span aria-label="Next">›</span></button></div><div class="owl-dots disabled"><button role="button" class="owl-dot active"><span></span></button></div></div>
							                        
							                    
							                </div>
							                <div class="d-flex flex-row-fluid flex-wrap">
							                    <div class="flex-grow-1 me-2">
							                        <span class="text-gray-600 d-block overflow-auto">
							                            Sooo who else already has their Christmas decorations up? 😬✋<br>
<br>
We can't wait to see all of your holiday-themed Berkeys! 🎄							                        </span>
							                    </div>
							                </div>
							            </div>

							        </div>

							        
							            
							            <div class="card-footer bg-light-success text-success py-3 px-4 d-flex justify-content-between">
							                <span class="me-2">Post Published</span> <a href="https://twitter.com/usaberkey/status/1994481923972853783" class="text-dark text-hover-primary" target="_blank"><i class="fad fa-eye"></i> View post</a>
							            </div>
							        
							        
							        
							    </div>

							
							    							    <div class="card border px-0 item mb-4 b-r-15 overflow-hidden">

							        							            <div class="ribbon ribbon-triangle ribbon-top-end border-success rounded">
							                <div class="ribbon-icon mn-t-20 mn-l-2">
							                    <i class="fs-20 fad fa-check-double fs-2 text-white"></i>
							                </div>
							            </div>

							            <!--<div class="border-success border-top-dashed border-1"></div>-->
							        							        
							        <div class="card-header px-4 border-0 pt-4">
							            
							            <div class="card-title fw-normal m-0 fs-12">
							                
							                <div class="d-flex flex-stack">
							                    <div class="symbol symbol-45px me-3">
							                        <img src="https://itspando.com/writable/avatar/672ff0ef06c1e.jpg" class="align-self-center rounded-circle border" alt="">
							                    </div>
							                    <div class="d-flex align-items-center flex-row-fluid flex-wrap">
							                        <div class="flex-grow-1 me-2 text-over-all">
							                            <a href="https://www.facebook.com/153908811306053" target="_blank" class="text-gray-800 text-hover-primary fs-14 fw-bold"><i class="" style="color: ;"></i> USA Berkey Filters</a>
							                            <span class="text-muted fw-semibold d-block fs-12"><i class="fal fa-calendar-alt"></i> 11/27/2025 8:00 AM</span>
							                        </div>
							                    </div>
							                </div>

							            </div>

							        </div>

							        <div class="card-body p-20">
							            
							            <div class="d-flex">
							                <div class="symbol symbol-80 me-3 overflow-hidden w-80 h-80 border b-r-15">

							                    							                        							                        <div class="owl-carousel owl-theme owl-loaded owl-drag">
							                            							                                
							                                							                                    
							                                
							                            							                        <div class="owl-stage-outer"><div class="owl-stage" style="transform: translate3d(-156px, 0px, 0px); transition: all; width: 392px;"><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764179260_7a10dfa69e449a880fc5.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764179260_7a10dfa69e449a880fc5.png');"></div></div><div class="owl-item active" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764179260_7a10dfa69e449a880fc5.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764179260_7a10dfa69e449a880fc5.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764179260_7a10dfa69e449a880fc5.png');"></div></div></div></div><div class="owl-nav disabled"><button type="button" role="presentation" class="owl-prev"><span aria-label="Previous">‹</span></button><button type="button" role="presentation" class="owl-next"><span aria-label="Next">›</span></button></div><div class="owl-dots disabled"><button role="button" class="owl-dot active"><span></span></button></div></div>
							                        
							                    
							                </div>
							                <div class="d-flex flex-row-fluid flex-wrap">
							                    <div class="flex-grow-1 me-2">
							                        <span class="text-gray-600 d-block overflow-auto">
							                            Happy Thanksgiving from all of us here at USA Berkey Filters! 🦃<br>
<br>
<br>
We’re incredibly grateful for our loyal customers who have continually shown their support and make everything we do possible. Wishing you a wonderful day with your loved ones! 🩵<br>
<br>
<br>
#Thanksgiving #HappyThanksgiving #Thankful #Grateful #USABerkeyFilters							                        </span>
							                    </div>
							                </div>
							            </div>

							        </div>

							        
							            
							            <div class="card-footer bg-light-success text-success py-3 px-4 d-flex justify-content-between">
							                <span class="me-2">Post Published</span> <a href="https://fb.com/1303808185113511" class="text-dark text-hover-primary" target="_blank"><i class="fad fa-eye"></i> View post</a>
							            </div>
							        
							        
							        
							    </div>

							
							    							    <div class="card border px-0 item mb-4 b-r-15 overflow-hidden">

							        							            <div class="ribbon ribbon-triangle ribbon-top-end border-success rounded">
							                <div class="ribbon-icon mn-t-20 mn-l-2">
							                    <i class="fs-20 fad fa-check-double fs-2 text-white"></i>
							                </div>
							            </div>

							            <!--<div class="border-success border-top-dashed border-1"></div>-->
							        							        
							        <div class="card-header px-4 border-0 pt-4">
							            
							            <div class="card-title fw-normal m-0 fs-12">
							                
							                <div class="d-flex flex-stack">
							                    <div class="symbol symbol-45px me-3">
							                        <img src="https://itspando.com/writable/avatar/672ff15d53462.jpg" class="align-self-center rounded-circle border" alt="">
							                    </div>
							                    <div class="d-flex align-items-center flex-row-fluid flex-wrap">
							                        <div class="flex-grow-1 me-2 text-over-all">
							                            <a href="https://www.instagram.com/usaberkeyfilters" target="_blank" class="text-gray-800 text-hover-primary fs-14 fw-bold"><i class="" style="color: ;"></i> USA Berkey® Filters | Berkey Water | Clean Water</a>
							                            <span class="text-muted fw-semibold d-block fs-12"><i class="fal fa-calendar-alt"></i> 11/27/2025 8:00 AM</span>
							                        </div>
							                    </div>
							                </div>

							            </div>

							        </div>

							        <div class="card-body p-20">
							            
							            <div class="d-flex">
							                <div class="symbol symbol-80 me-3 overflow-hidden w-80 h-80 border b-r-15">

							                    							                        							                        <div class="owl-carousel owl-theme owl-loaded owl-drag">
							                            							                                
							                                							                                    
							                                
							                            							                        <div class="owl-stage-outer"><div class="owl-stage" style="transform: translate3d(-156px, 0px, 0px); transition: all; width: 392px;"><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764179260_7a10dfa69e449a880fc5.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764179260_7a10dfa69e449a880fc5.png');"></div></div><div class="owl-item active" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764179260_7a10dfa69e449a880fc5.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764179260_7a10dfa69e449a880fc5.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764179260_7a10dfa69e449a880fc5.png');"></div></div></div></div><div class="owl-nav disabled"><button type="button" role="presentation" class="owl-prev"><span aria-label="Previous">‹</span></button><button type="button" role="presentation" class="owl-next"><span aria-label="Next">›</span></button></div><div class="owl-dots disabled"><button role="button" class="owl-dot active"><span></span></button></div></div>
							                        
							                    
							                </div>
							                <div class="d-flex flex-row-fluid flex-wrap">
							                    <div class="flex-grow-1 me-2">
							                        <span class="text-gray-600 d-block overflow-auto">
							                            Happy Thanksgiving from all of us here at USA Berkey Filters! 🦃<br>
<br>
<br>
We’re incredibly grateful for our loyal customers who have continually shown their support and make everything we do possible. Wishing you a wonderful day with your loved ones! 🩵<br>
<br>
<br>
#Thanksgiving #HappyThanksgiving #Thankful #Grateful #USABerkeyFilters							                        </span>
							                    </div>
							                </div>
							            </div>

							        </div>

							        
							            
							            <div class="card-footer bg-light-success text-success py-3 px-4 d-flex justify-content-between">
							                <span class="me-2">Post Published</span> <a href="https://www.instagram.com/p/DRkQZVtk8dI" class="text-dark text-hover-primary" target="_blank"><i class="fad fa-eye"></i> View post</a>
							            </div>
							        
							        
							        
							    </div>

							
							    							    <div class="card border px-0 item mb-4 b-r-15 overflow-hidden">

							        							            <div class="ribbon ribbon-triangle ribbon-top-end border-success rounded">
							                <div class="ribbon-icon mn-t-20 mn-l-2">
							                    <i class="fs-20 fad fa-check-double fs-2 text-white"></i>
							                </div>
							            </div>

							            <!--<div class="border-success border-top-dashed border-1"></div>-->
							        							        
							        <div class="card-header px-4 border-0 pt-4">
							            
							            <div class="card-title fw-normal m-0 fs-12">
							                
							                <div class="d-flex flex-stack">
							                    <div class="symbol symbol-45px me-3">
							                        <img src="https://itspando.com/writable/avatar/682de1f9e5d2e.png" class="align-self-center rounded-circle border" alt="">
							                    </div>
							                    <div class="d-flex align-items-center flex-row-fluid flex-wrap">
							                        <div class="flex-grow-1 me-2 text-over-all">
							                            <a href="https://www.pinterest.com/usaberkeyfilters" target="_blank" class="text-gray-800 text-hover-primary fs-14 fw-bold"><i class="" style="color: ;"></i> http://usaberkeyfilters.com</a>
							                            <span class="text-muted fw-semibold d-block fs-12"><i class="fal fa-calendar-alt"></i> 11/27/2025 8:00 AM</span>
							                        </div>
							                    </div>
							                </div>

							            </div>

							        </div>

							        <div class="card-body p-20">
							            
							            <div class="d-flex">
							                <div class="symbol symbol-80 me-3 overflow-hidden w-80 h-80 border b-r-15">

							                    							                        							                        <div class="owl-carousel owl-theme owl-loaded owl-drag">
							                            							                                
							                                							                                    
							                                
							                            							                        <div class="owl-stage-outer"><div class="owl-stage" style="transform: translate3d(-156px, 0px, 0px); transition: all; width: 392px;"><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764179260_7a10dfa69e449a880fc5.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764179260_7a10dfa69e449a880fc5.png');"></div></div><div class="owl-item active" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764179260_7a10dfa69e449a880fc5.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764179260_7a10dfa69e449a880fc5.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764179260_7a10dfa69e449a880fc5.png');"></div></div></div></div><div class="owl-nav disabled"><button type="button" role="presentation" class="owl-prev"><span aria-label="Previous">‹</span></button><button type="button" role="presentation" class="owl-next"><span aria-label="Next">›</span></button></div><div class="owl-dots disabled"><button role="button" class="owl-dot active"><span></span></button></div></div>
							                        
							                    
							                </div>
							                <div class="d-flex flex-row-fluid flex-wrap">
							                    <div class="flex-grow-1 me-2">
							                        <span class="text-gray-600 d-block overflow-auto">
							                            Happy Thanksgiving from all of us here at USA Berkey Filters! 🦃<br>
<br>
<br>
We’re incredibly grateful for our loyal customers who have continually shown their support and make everything we do possible. Wishing you a wonderful day with your loved ones! 🩵<br>
<br>
<br>
#Thanksgiving #HappyThanksgiving #Thankful #Grateful #USABerkeyFilters							                        </span>
							                    </div>
							                </div>
							            </div>

							        </div>

							        
							            
							            <div class="card-footer bg-light-success text-success py-3 px-4 d-flex justify-content-between">
							                <span class="me-2">Post Published</span> <a href="https://www.pinterest.com/pin/577657089746326718/" class="text-dark text-hover-primary" target="_blank"><i class="fad fa-eye"></i> View post</a>
							            </div>
							        
							        
							        
							    </div>

							
							    							    <div class="card border px-0 item mb-4 b-r-15 overflow-hidden">

							        							            <div class="ribbon ribbon-triangle ribbon-top-end border-success rounded">
							                <div class="ribbon-icon mn-t-20 mn-l-2">
							                    <i class="fs-20 fad fa-check-double fs-2 text-white"></i>
							                </div>
							            </div>

							            <!--<div class="border-success border-top-dashed border-1"></div>-->
							        							        
							        <div class="card-header px-4 border-0 pt-4">
							            
							            <div class="card-title fw-normal m-0 fs-12">
							                
							                <div class="d-flex flex-stack">
							                    <div class="symbol symbol-45px me-3">
							                        <img src="https://itspando.com/writable/avatar/683f4dd6615c9.jpg" class="align-self-center rounded-circle border" alt="">
							                    </div>
							                    <div class="d-flex align-items-center flex-row-fluid flex-wrap">
							                        <div class="flex-grow-1 me-2 text-over-all">
							                            <a href="https://twitter.com/usaberkey" target="_blank" class="text-gray-800 text-hover-primary fs-14 fw-bold"><i class="" style="color: ;"></i> USA Berkey Filters</a>
							                            <span class="text-muted fw-semibold d-block fs-12"><i class="fal fa-calendar-alt"></i> 11/27/2025 8:00 AM</span>
							                        </div>
							                    </div>
							                </div>

							            </div>

							        </div>

							        <div class="card-body p-20">
							            
							            <div class="d-flex">
							                <div class="symbol symbol-80 me-3 overflow-hidden w-80 h-80 border b-r-15">

							                    							                        							                        <div class="owl-carousel owl-theme owl-loaded owl-drag">
							                            							                                
							                                							                                    
							                                
							                            							                        <div class="owl-stage-outer"><div class="owl-stage" style="transform: translate3d(-156px, 0px, 0px); transition: all; width: 392px;"><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764179260_7a10dfa69e449a880fc5.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764179260_7a10dfa69e449a880fc5.png');"></div></div><div class="owl-item active" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764179260_7a10dfa69e449a880fc5.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764179260_7a10dfa69e449a880fc5.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764179260_7a10dfa69e449a880fc5.png');"></div></div></div></div><div class="owl-nav disabled"><button type="button" role="presentation" class="owl-prev"><span aria-label="Previous">‹</span></button><button type="button" role="presentation" class="owl-next"><span aria-label="Next">›</span></button></div><div class="owl-dots disabled"><button role="button" class="owl-dot active"><span></span></button></div></div>
							                        
							                    
							                </div>
							                <div class="d-flex flex-row-fluid flex-wrap">
							                    <div class="flex-grow-1 me-2">
							                        <span class="text-gray-600 d-block overflow-auto">
							                            Happy Thanksgiving from all of us here at USA Berkey Filters! 🦃<br>
<br>
We’re incredibly grateful for our loyal customers who have continually shown their support and make everything we do possible. Wishing you a wonderful day with your loved ones! 🩵							                        </span>
							                    </div>
							                </div>
							            </div>

							        </div>

							        
							            
							            <div class="card-footer bg-light-success text-success py-3 px-4 d-flex justify-content-between">
							                <span class="me-2">Post Published</span> <a href="https://twitter.com/usaberkey/status/1994079908075552833" class="text-dark text-hover-primary" target="_blank"><i class="fad fa-eye"></i> View post</a>
							            </div>
							        
							        
							        
							    </div>

							
							    							    <div class="card border px-0 item mb-4 b-r-15 overflow-hidden">

							        							            <div class="ribbon ribbon-triangle ribbon-top-end border-success rounded">
							                <div class="ribbon-icon mn-t-20 mn-l-2">
							                    <i class="fs-20 fad fa-check-double fs-2 text-white"></i>
							                </div>
							            </div>

							            <!--<div class="border-success border-top-dashed border-1"></div>-->
							        							        
							        <div class="card-header px-4 border-0 pt-4">
							            
							            <div class="card-title fw-normal m-0 fs-12">
							                
							                <div class="d-flex flex-stack">
							                    <div class="symbol symbol-45px me-3">
							                        <img src="https://itspando.com/writable/avatar/672ff0ef06c1e.jpg" class="align-self-center rounded-circle border" alt="">
							                    </div>
							                    <div class="d-flex align-items-center flex-row-fluid flex-wrap">
							                        <div class="flex-grow-1 me-2 text-over-all">
							                            <a href="https://www.facebook.com/153908811306053" target="_blank" class="text-gray-800 text-hover-primary fs-14 fw-bold"><i class="" style="color: ;"></i> USA Berkey Filters</a>
							                            <span class="text-muted fw-semibold d-block fs-12"><i class="fal fa-calendar-alt"></i> 11/25/2025 4:00 PM</span>
							                        </div>
							                    </div>
							                </div>

							            </div>

							        </div>

							        <div class="card-body p-20">
							            
							            <div class="d-flex">
							                <div class="symbol symbol-80 me-3 overflow-hidden w-80 h-80 border b-r-15">

							                    							                        							                        <div class="owl-carousel owl-theme owl-loaded owl-drag">
							                            							                                
							                                							                                    
							                                
							                            							                        <div class="owl-stage-outer"><div class="owl-stage" style="transform: translate3d(-156px, 0px, 0px); transition: all; width: 392px;"><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764105784_4ac1b0b7490331ebd283.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764105784_4ac1b0b7490331ebd283.png');"></div></div><div class="owl-item active" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764105784_4ac1b0b7490331ebd283.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764105784_4ac1b0b7490331ebd283.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764105784_4ac1b0b7490331ebd283.png');"></div></div></div></div><div class="owl-nav disabled"><button type="button" role="presentation" class="owl-prev"><span aria-label="Previous">‹</span></button><button type="button" role="presentation" class="owl-next"><span aria-label="Next">›</span></button></div><div class="owl-dots disabled"><button role="button" class="owl-dot active"><span></span></button></div></div>
							                        
							                    
							                </div>
							                <div class="d-flex flex-row-fluid flex-wrap">
							                    <div class="flex-grow-1 me-2">
							                        <span class="text-gray-600 d-block overflow-auto">
							                            Concerned about toxins in your water? 💧 With a Berkey, you can put those worries behind you!<br>
<br>
Visit our website to find the perfect system to fit your needs! 🚰<br>
🔗 https://www.usaberkeyfilters.com/							                        </span>
							                    </div>
							                </div>
							            </div>

							        </div>

							        
							            
							            <div class="card-footer bg-light-success text-success py-3 px-4 d-flex justify-content-between">
							                <span class="me-2">Post Published</span> <a href="https://fb.com/1302351561925840" class="text-dark text-hover-primary" target="_blank"><i class="fad fa-eye"></i> View post</a>
							            </div>
							        
							        
							        
							    </div>

							
							    							    <div class="card border px-0 item mb-4 b-r-15 overflow-hidden">

							        							            <div class="ribbon ribbon-triangle ribbon-top-end border-success rounded">
							                <div class="ribbon-icon mn-t-20 mn-l-2">
							                    <i class="fs-20 fad fa-check-double fs-2 text-white"></i>
							                </div>
							            </div>

							            <!--<div class="border-success border-top-dashed border-1"></div>-->
							        							        
							        <div class="card-header px-4 border-0 pt-4">
							            
							            <div class="card-title fw-normal m-0 fs-12">
							                
							                <div class="d-flex flex-stack">
							                    <div class="symbol symbol-45px me-3">
							                        <img src="https://itspando.com/writable/avatar/683f4dd6615c9.jpg" class="align-self-center rounded-circle border" alt="">
							                    </div>
							                    <div class="d-flex align-items-center flex-row-fluid flex-wrap">
							                        <div class="flex-grow-1 me-2 text-over-all">
							                            <a href="https://twitter.com/usaberkey" target="_blank" class="text-gray-800 text-hover-primary fs-14 fw-bold"><i class="" style="color: ;"></i> USA Berkey Filters</a>
							                            <span class="text-muted fw-semibold d-block fs-12"><i class="fal fa-calendar-alt"></i> 11/25/2025 4:00 PM</span>
							                        </div>
							                    </div>
							                </div>

							            </div>

							        </div>

							        <div class="card-body p-20">
							            
							            <div class="d-flex">
							                <div class="symbol symbol-80 me-3 overflow-hidden w-80 h-80 border b-r-15">

							                    							                        							                        <div class="owl-carousel owl-theme owl-loaded owl-drag">
							                            							                                
							                                							                                    
							                                
							                            							                        <div class="owl-stage-outer"><div class="owl-stage" style="transform: translate3d(-156px, 0px, 0px); transition: all; width: 392px;"><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764105784_4ac1b0b7490331ebd283.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764105784_4ac1b0b7490331ebd283.png');"></div></div><div class="owl-item active" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764105784_4ac1b0b7490331ebd283.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764105784_4ac1b0b7490331ebd283.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764105784_4ac1b0b7490331ebd283.png');"></div></div></div></div><div class="owl-nav disabled"><button type="button" role="presentation" class="owl-prev"><span aria-label="Previous">‹</span></button><button type="button" role="presentation" class="owl-next"><span aria-label="Next">›</span></button></div><div class="owl-dots disabled"><button role="button" class="owl-dot active"><span></span></button></div></div>
							                        
							                    
							                </div>
							                <div class="d-flex flex-row-fluid flex-wrap">
							                    <div class="flex-grow-1 me-2">
							                        <span class="text-gray-600 d-block overflow-auto">
							                            Concerned about toxins in your water? 💧 With a Berkey, you can put those worries behind you!<br>
<br>
Visit our website to find the perfect system to fit your needs! 🚰<br>
🔗 https://www.usaberkeyfilters.com/							                        </span>
							                    </div>
							                </div>
							            </div>

							        </div>

							        
							            
							            <div class="card-footer bg-light-success text-success py-3 px-4 d-flex justify-content-between">
							                <span class="me-2">Post Published</span> <a href="https://twitter.com/usaberkey/status/1993469799020523813" class="text-dark text-hover-primary" target="_blank"><i class="fad fa-eye"></i> View post</a>
							            </div>
							        
							        
							        
							    </div>

							
							    							    <div class="card border px-0 item mb-4 b-r-15 overflow-hidden">

							        							            <div class="ribbon ribbon-triangle ribbon-top-end border-success rounded">
							                <div class="ribbon-icon mn-t-20 mn-l-2">
							                    <i class="fs-20 fad fa-check-double fs-2 text-white"></i>
							                </div>
							            </div>

							            <!--<div class="border-success border-top-dashed border-1"></div>-->
							        							        
							        <div class="card-header px-4 border-0 pt-4">
							            
							            <div class="card-title fw-normal m-0 fs-12">
							                
							                <div class="d-flex flex-stack">
							                    <div class="symbol symbol-45px me-3">
							                        <img src="https://itspando.com/writable/avatar/672ff15d53462.jpg" class="align-self-center rounded-circle border" alt="">
							                    </div>
							                    <div class="d-flex align-items-center flex-row-fluid flex-wrap">
							                        <div class="flex-grow-1 me-2 text-over-all">
							                            <a href="https://www.instagram.com/usaberkeyfilters" target="_blank" class="text-gray-800 text-hover-primary fs-14 fw-bold"><i class="" style="color: ;"></i> USA Berkey® Filters | Berkey Water | Clean Water</a>
							                            <span class="text-muted fw-semibold d-block fs-12"><i class="fal fa-calendar-alt"></i> 11/25/2025 4:00 PM</span>
							                        </div>
							                    </div>
							                </div>

							            </div>

							        </div>

							        <div class="card-body p-20">
							            
							            <div class="d-flex">
							                <div class="symbol symbol-80 me-3 overflow-hidden w-80 h-80 border b-r-15">

							                    							                        							                        <div class="owl-carousel owl-theme owl-loaded owl-drag">
							                            							                                
							                                							                                    
							                                
							                            							                        <div class="owl-stage-outer"><div class="owl-stage" style="transform: translate3d(-156px, 0px, 0px); transition: all; width: 392px;"><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764105784_4ac1b0b7490331ebd283.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764105784_4ac1b0b7490331ebd283.png');"></div></div><div class="owl-item active" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764105784_4ac1b0b7490331ebd283.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764105784_4ac1b0b7490331ebd283.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1764105784_4ac1b0b7490331ebd283.png');"></div></div></div></div><div class="owl-nav disabled"><button type="button" role="presentation" class="owl-prev"><span aria-label="Previous">‹</span></button><button type="button" role="presentation" class="owl-next"><span aria-label="Next">›</span></button></div><div class="owl-dots disabled"><button role="button" class="owl-dot active"><span></span></button></div></div>
							                        
							                    
							                </div>
							                <div class="d-flex flex-row-fluid flex-wrap">
							                    <div class="flex-grow-1 me-2">
							                        <span class="text-gray-600 d-block overflow-auto">
							                            Concerned about toxins in your water? 💧 With a Berkey, you can put those worries behind you!<br>
<br>
Visit our website to find the perfect system to fit your needs! 🚰<br>
🔗 Link in bio!							                        </span>
							                    </div>
							                </div>
							            </div>

							        </div>

							        
							            
							            <div class="card-footer bg-light-success text-success py-3 px-4 d-flex justify-content-between">
							                <span class="me-2">Post Published</span> <a href="https://www.instagram.com/p/DRf6-mhjRC3" class="text-dark text-hover-primary" target="_blank"><i class="fad fa-eye"></i> View post</a>
							            </div>
							        
							        
							        
							    </div>

							
							    							    <div class="card border px-0 item mb-4 b-r-15 overflow-hidden">

							        							            <div class="ribbon ribbon-triangle ribbon-top-end border-success rounded">
							                <div class="ribbon-icon mn-t-20 mn-l-2">
							                    <i class="fs-20 fad fa-check-double fs-2 text-white"></i>
							                </div>
							            </div>

							            <!--<div class="border-success border-top-dashed border-1"></div>-->
							        							        
							        <div class="card-header px-4 border-0 pt-4">
							            
							            <div class="card-title fw-normal m-0 fs-12">
							                
							                <div class="d-flex flex-stack">
							                    <div class="symbol symbol-45px me-3">
							                        <img src="https://itspando.com/writable/avatar/672ff0ef06c1e.jpg" class="align-self-center rounded-circle border" alt="">
							                    </div>
							                    <div class="d-flex align-items-center flex-row-fluid flex-wrap">
							                        <div class="flex-grow-1 me-2 text-over-all">
							                            <a href="https://www.facebook.com/153908811306053" target="_blank" class="text-gray-800 text-hover-primary fs-14 fw-bold"><i class="" style="color: ;"></i> USA Berkey Filters</a>
							                            <span class="text-muted fw-semibold d-block fs-12"><i class="fal fa-calendar-alt"></i> 11/22/2025 10:00 AM</span>
							                        </div>
							                    </div>
							                </div>

							            </div>

							        </div>

							        <div class="card-body p-20">
							            
							            <div class="d-flex">
							                <div class="symbol symbol-80 me-3 overflow-hidden w-80 h-80 border b-r-15">

							                    							                        							                        <div class="owl-carousel owl-theme owl-loaded owl-drag">
							                            							                                
							                                							                                    
							                                
							                            							                        <div class="owl-stage-outer"><div class="owl-stage" style="transform: translate3d(-156px, 0px, 0px); transition: all; width: 392px;"><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1763786554_4d2eccc0acc7364616a3.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1763786554_4d2eccc0acc7364616a3.png');"></div></div><div class="owl-item active" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1763786554_4d2eccc0acc7364616a3.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1763786554_4d2eccc0acc7364616a3.png');"></div></div><div class="owl-item cloned" style="width: 78.4px;"><div class="item w-80 h-80" style="background-image: url('https://itspando.com/writable/uploads/1763786554_4d2eccc0acc7364616a3.png');"></div></div></div></div><div class="owl-nav disabled"><button type="button" role="presentation" class="owl-prev"><span aria-label="Previous">‹</span></button><button type="button" role="presentation" class="owl-next"><span aria-label="Next">›</span></button></div><div class="owl-dots disabled"><button role="button" class="owl-dot active"><span></span></button></div></div>
							                        
							                    
							                </div>
							                <div class="d-flex flex-row-fluid flex-wrap">
							                    <div class="flex-grow-1 me-2">
							                        <span class="text-gray-600 d-block overflow-auto">
							                            #CustomerReview - Thank you so much for your review, S.L., we're so glad you're loving your Berkey! 🩵🚰<br>
<br>
If you're ready to take the next step on your clean water journey, our team is always here to help! 🙌<br>
🔗 usaberkeyfilters.com							                        </span>
							                    </div>
							                </div>
							            </div>

							        </div>

							        
							            
							            <div class="card-footer bg-light-success text-success py-3 px-4 d-flex justify-content-between">
							                <span class="me-2">Post Published</span> <a href="https://fb.com/1299681702192826" class="text-dark text-hover-primary" target="_blank"><i class="fad fa-eye"></i> View post</a>
							            </div>
							        
							        
							        
							    </div>

														<script type="text/javascript">
							    $(function(){
							        Layout.carousel();
							    });
							</script>
						
				    </div>
	        	</div>
	        </div>
	    </div>
	</div><?php /**PATH C:\xampp82\htdocs\pando-laravel\modules/AppDashboard\resources/views/statistics.blade.php ENDPATH**/ ?>