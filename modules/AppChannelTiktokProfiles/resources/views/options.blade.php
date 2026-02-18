{{-- TikTok Options for Composer --}}
{{-- This file matches TikTok's native upload interface exactly --}}
{{-- Path: modules/AppChannelTiktokProfiles/resources/views/options.blade.php --}}

<div class="card mb-3 shadow-sm border-0">
    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3">
        <div>
            <i class="fab fa-tiktok me-2" style="color: #000;"></i>
            <strong style="font-size: 1.1rem;">Upload to TikTok</strong>
        </div>
    </div>
    <div class="card-body bg-light">
        
        {{-- Who can view this video --}}
        <div class="mb-4">
            <label class="form-label fw-bold mb-2" style="color: #161823;">
                Who can view this video
            </label>
            <select class="form-select form-select-lg" name="tiktok_privacy" id="tiktok_privacy" style="background-color: #F1F1F2; border: none;">
                <option value="PUBLIC_TO_EVERYONE" selected>Public</option>
                <option value="MUTUAL_FOLLOW_FRIENDS">Friends</option>
                <option value="SELF_ONLY">Private</option>
            </select>
        </div>

        {{-- Allow users to --}}
        <div class="mb-4">
            <label class="form-label fw-bold mb-3" style="color: #161823;">
                Allow users to
            </label>
            <div class="d-flex gap-3 flex-wrap">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="tiktok_allow_comment" checked style="width: 20px; height: 20px; border-radius: 4px;">
                    <label class="form-check-label ms-2" for="tiktok_allow_comment" style="font-size: 15px;">
                        Comment
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="tiktok_allow_duet" checked style="width: 20px; height: 20px; border-radius: 4px;">
                    <label class="form-check-label ms-2" for="tiktok_allow_duet" style="font-size: 15px;">
                        Duet
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="tiktok_allow_stitch" checked style="width: 20px; height: 20px; border-radius: 4px;">
                    <label class="form-check-label ms-2" for="tiktok_allow_stitch" style="font-size: 15px;">
                        Stitch
                    </label>
                </div>
            </div>
        </div>

        {{-- Disclose video content --}}
        <div class="mb-3">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <label class="form-label fw-bold mb-0" style="color: #161823;">
                    Disclose video content
                </label>
                <div class="form-check form-switch m-0">
                    <input class="form-check-input" type="checkbox" role="switch" id="tiktok_commercial_toggle" 
                           style="width: 50px; height: 26px; cursor: pointer;">
                </div>
            </div>
            
            <p class="text-muted small mb-3" style="font-size: 13px;">
                Turn on to disclose that this video promotes goods or services in exchange for something of value. 
                Your video could promote yourself, a third party, or both.
            </p>

            {{-- Commercial Content Options (Hidden by default) --}}
            <div id="tiktok_commercial_options" style="display: none;">
                
                {{-- Blue info box when toggle is ON --}}
                <div class="alert alert-info d-flex align-items-start mb-3" style="background-color: #E7F3FF; border: none; border-radius: 8px;">
                    <i class="fas fa-info-circle mt-1 me-2" style="color: #0095F6;"></i>
                    <div style="font-size: 13px; color: #161823;">
                        <strong>Your video will be labeled "Promotional content".</strong><br>
                        This cannot be changed once your video is posted.
                    </div>
                </div>

                {{-- Your brand checkbox --}}
                <div class="card mb-2 border-0" style="background-color: #F8F8F8; border-radius: 8px;">
                    <div class="card-body p-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="tiktok_your_brand" 
                                   style="width: 20px; height: 20px; border-radius: 4px; margin-top: 0.15rem;">
                            <label class="form-check-label ms-2" for="tiktok_your_brand">
                                <strong style="font-size: 14px;">Your brand</strong>
                                <p class="text-muted small mb-0 mt-1" style="font-size: 13px;">
                                    You are promoting yourself or your own business. This video will be classified as Brand Organic.
                                </p>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Branded content checkbox --}}
                <div class="card mb-3 border-0" style="background-color: #F8F8F8; border-radius: 8px;">
                    <div class="card-body p-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="tiktok_branded_content" 
                                   style="width: 20px; height: 20px; border-radius: 4px; margin-top: 0.15rem;">
                            <label class="form-check-label ms-2" for="tiktok_branded_content">
                                <strong style="font-size: 14px;">Branded content</strong>
                                <p class="text-muted small mb-0 mt-1" style="font-size: 13px;">
                                    You are promoting another brand or a third party. This video will be classified as Branded Content.
                                </p>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Declaration text (changes based on selections) --}}
            <p class="small mb-0" id="tiktok_declaration" style="font-size: 13px; color: #8A8B8F;">
                By posting, you agree to our 
                <a href="https://www.tiktok.com/legal/music-usage-confirmation" target="_blank" style="color: #00D4FF;">Music Usage Confirmation</a>.
            </p>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
(function($) {
    // Show/hide commercial content options
    $('#tiktok_commercial_toggle').on('change', function() {
        if ($(this).is(':checked')) {
            $('#tiktok_commercial_options').slideDown(200);
            updateDeclarationText();
        } else {
            $('#tiktok_commercial_options').slideUp(200);
            // Uncheck both options when toggle is turned off
            $('#tiktok_your_brand').prop('checked', false);
            $('#tiktok_branded_content').prop('checked', false);
            updateDeclarationText();
        }
    });

    // Update declaration text when checkboxes change
    $('#tiktok_your_brand, #tiktok_branded_content').on('change', function() {
        updateDeclarationText();
    });

    // Function to update the declaration text based on selections
    function updateDeclarationText() {
        const commercialToggle = $('#tiktok_commercial_toggle').is(':checked');
        const yourBrand = $('#tiktok_your_brand').is(':checked');
        const brandedContent = $('#tiktok_branded_content').is(':checked');
        
        let declarationHTML = '';
        
        if (!commercialToggle) {
            // Default: Commercial toggle is OFF
            declarationHTML = 'By posting, you agree to our <a href="https://www.tiktok.com/legal/music-usage-confirmation" target="_blank" style="color: #00D4FF;">Music Usage Confirmation</a>.';
        } else if (yourBrand && !brandedContent) {
            // Only "Your Brand" is checked
            declarationHTML = 'By posting, you agree to TikTok\'s <a href="https://www.tiktok.com/legal/music-usage-confirmation" target="_blank" style="color: #00D4FF;">Music Usage Confirmation</a>.';
        } else if (!yourBrand && brandedContent) {
            // Only "Branded Content" is checked
            declarationHTML = 'By posting, you agree to TikTok\'s <a href="https://www.tiktok.com/community-guidelines/branded-content" target="_blank" style="color: #00D4FF;">Branded Content Policy</a> and <a href="https://www.tiktok.com/legal/music-usage-confirmation" target="_blank" style="color: #00D4FF;">Music Usage Confirmation</a>.';
        } else if (yourBrand && brandedContent) {
            // Both options are selected
            declarationHTML = 'By posting, you agree to TikTok\'s <a href="https://www.tiktok.com/community-guidelines/branded-content" target="_blank" style="color: #00D4FF;">Branded Content Policy</a> and <a href="https://www.tiktok.com/legal/music-usage-confirmation" target="_blank" style="color: #00D4FF;">Music Usage Confirmation</a>.';
        } else {
            // Commercial toggle is ON but neither checkbox is selected
            declarationHTML = 'By posting, you agree to our <a href="https://www.tiktok.com/legal/music-usage-confirmation" target="_blank" style="color: #00D4FF;">Music Usage Confirmation</a>.';
        }
        
        $('#tiktok_declaration').html(declarationHTML);
    }

    // Function to collect TikTok settings (called from main form submission)
    window.getTikTokSettings = function() {
        const commercialToggle = $('#tiktok_commercial_toggle').is(':checked');
        const yourBrand = $('#tiktok_your_brand').is(':checked');
        const brandedContent = $('#tiktok_branded_content').is(':checked');
        
        return {
            privacy_level: $('#tiktok_privacy').val() || 'PUBLIC_TO_EVERYONE',
            brand_content_toggle: commercialToggle && (yourBrand || brandedContent),
            brand_organic_toggle: commercialToggle && yourBrand,
            disable_comment: !$('#tiktok_allow_comment').is(':checked'),
            disable_duet: !$('#tiktok_allow_duet').is(':checked'),
            disable_stitch: !$('#tiktok_allow_stitch').is(':checked'),
            cover_timestamp: 1000
        };
    };
})(jQuery);
});
</script>

<style>
/* TikTok-style checkbox styling */
.form-check-input:checked {
    background-color: #00D4FF !important;
    border-color: #00D4FF !important;
}

.form-check-input:focus {
    border-color: #00D4FF !important;
    box-shadow: 0 0 0 0.25rem rgba(0, 212, 255, 0.25) !important;
}

/* TikTok-style toggle switch */
input[type="checkbox"][role="switch"]:checked {
    background-color: #00D4FF !important;
    border-color: #00D4FF !important;
}

input[type="checkbox"][role="switch"]:focus {
    border-color: #00D4FF !important;
    box-shadow: 0 0 0 0.25rem rgba(0, 212, 255, 0.25) !important;
}

/* TikTok-style select dropdown */
.form-select:focus {
    border-color: #00D4FF !important;
    box-shadow: 0 0 0 0.25rem rgba(0, 212, 255, 0.25) !important;
}

/* Smooth transitions */
.form-check-input,
input[type="checkbox"][role="switch"] {
    transition: all 0.2s ease-in-out;
}
</style>
