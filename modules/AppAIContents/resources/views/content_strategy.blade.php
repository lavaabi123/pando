@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-7 mx-auto py-4">
            <div class="card">
                <div class="card-header">
                    <h4>AI Content Strategy</h4>
                    <p class="text-muted mb-0">Generate intelligent content strategies with AI</p>
                </div>
                <div class="card-body">
                    
                    <!-- Nav Tabs - Compatible with Bootstrap 4 & 5 -->
                    <ul class="nav nav-tabs justify-content-between" id="strategyTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="calendar-tab" data-toggle="tab" data-bs-toggle="tab" href="#calendar" role="tab" aria-controls="calendar" aria-selected="true">
                                <i class="fas fa-calendar-alt"></i> Content Calendar
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="hashtags-tab" data-toggle="tab" data-bs-toggle="tab" href="#hashtags" role="tab" aria-controls="hashtags" aria-selected="false">
                                <i class="fas fa-hashtag"></i> Hashtag Research
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="topics-tab" data-toggle="tab" data-bs-toggle="tab" href="#topics" role="tab" aria-controls="topics" aria-selected="false">
                                <i class="fas fa-lightbulb"></i> Topic Research
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="competitor-tab" data-toggle="tab" data-bs-toggle="tab" href="#competitor" role="tab" aria-controls="competitor" aria-selected="false">
                                <i class="fas fa-chart-line"></i> Competitor Analysis
                            </a>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content mt-4 inputField" id="strategyTabContent">
                        
                        <!-- Content Calendar Tab -->
                        <div class="tab-pane fade show active" id="calendar" role="tabpanel" aria-labelledby="calendar-tab">
                            <h5>Generate Content Calendar</h5>
                            <form id="calendar-form" class="">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Campaign Name</label>
                                            <input type="text" name="name" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Posting Frequency</label>
                                            <select name="posting_frequency" class="form-control">
                                                <option value="daily">Daily</option>
                                                <option value="5x_week">5 times per week</option>
                                                <option value="3x_week">3 times per week</option>
                                                <option value="weekly">Weekly</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Business Goals</label>
                                    <textarea name="business_goals" class="form-control" rows="2" required placeholder="e.g., Increase brand awareness by 50%, drive website traffic"></textarea>
                                </div>

                                <div class="form-group">
                                    <label>Target Audience</label>
                                    <textarea name="target_audience" class="form-control" rows="2" required placeholder="e.g., Tech professionals aged 25-40"></textarea>
                                </div>

                                <div class="form-group">
                                    <label>Select Platforms</label>
                                    <div class="d-flex gap-8 align-items-center">
                                        <label class="d-flex gap-8 align-items-center"><input class="mb-0" type="checkbox" name="platforms[]" value="facebook" checked> Facebook</label>
                                        <label class="d-flex gap-8 align-items-center"><input class="mb-0" type="checkbox" name="platforms[]" value="instagram" checked> Instagram</label>
                                        <label class="d-flex gap-8 align-items-center"><input class="mb-0" type="checkbox" name="platforms[]" value="linkedin"> LinkedIn</label>
                                        <label class="d-flex gap-8 align-items-center"><input class="mb-0" type="checkbox" name="platforms[]" value="twitter"> Twitter</label>
                                        <label class="d-flex gap-8 align-items-center"><input class="mb-0" type="checkbox" name="platforms[]" value="tiktok"> TikTok</label>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Start Date</label>
                                            <input type="date" name="start_date" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>End Date</label>
                                            <input type="date" name="end_date" class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-dark w-100 mt-2">
                                    <i class="fas fa-magic"></i> Generate Calendar
                                </button>
                            </form>

                            <div id="calendar-results" class="mt-4" style="display:none;">
                                <!-- Results will appear here -->
                            </div>
                        </div>

                        <!-- Hashtag Research Tab -->
                        <div class="tab-pane fade" id="hashtags" role="tabpanel" aria-labelledby="hashtags-tab">
                            <h5>Hashtag Research</h5>
                            <form id="hashtags-form">
                                @csrf
                                <div class="form-group">
                                    <label>Your Content</label>
                                    <textarea name="content" class="form-control" rows="3" required placeholder="Enter your post content here..."></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Platform</label>
                                            <select name="platform" class="form-control" required>
                                                <option value="instagram">Instagram</option>
                                                <option value="twitter">Twitter</option>
                                                <option value="linkedin">LinkedIn</option>
                                                <option value="facebook">Facebook</option>
                                                <option value="tiktok">TikTok</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Industry</label>
                                            <input type="text" name="industry" class="form-control" required placeholder="e.g., Technology, Fashion, Food">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Number of Hashtags</label>
                                    <input type="number" name="count" class="form-control" value="15" min="5" max="30">
                                </div>

                                <button type="submit" class="btn btn-dark w-100 mt-2">
                                    <i class="fas fa-hashtag"></i> Research Hashtags
                                </button>
                            </form>

                            <div id="hashtags-results" class="mt-4" style="display:none;">
                                <!-- Results will appear here -->
                            </div>
                        </div>

                        <!-- Topic Research Tab -->
                        <div class="tab-pane fade" id="topics" role="tabpanel" aria-labelledby="topics-tab">
                            <h5>Topic Research</h5>
                            <form id="topics-form">
                                @csrf
                                <div class="form-group">
                                    <label>Topics to Research (comma separated)</label>
                                    <textarea name="topics" class="form-control" rows="2" required placeholder="e.g., AI Marketing, Social Media Trends, Content Strategy"></textarea>
                                </div>

                                <div class="form-group">
                                    <label>Industry</label>
                                    <input type="text" name="industry" class="form-control" required placeholder="e.g., Digital Marketing">
                                </div>

                                <div class="form-group">
                                    <label>Target Audience (optional)</label>
                                    <input type="text" name="target_audience" class="form-control" placeholder="e.g., Small business owners">
                                </div>

                                <button type="submit" class="btn btn-dark w-100 mt-2">
                                    <i class="fas fa-search"></i> Research Topics
                                </button>
                            </form>

                            <div id="topics-results" class="mt-4" style="display:none;">
                                <!-- Results will appear here -->
                            </div>
                        </div>

                        <!-- Competitor Analysis Tab -->
                        <div class="tab-pane fade" id="competitor" role="tabpanel" aria-labelledby="competitor-tab">
                            <h5>Competitor Analysis</h5>
                            <form id="competitor-form">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Competitor Name</label>
                                            <input type="text" name="name" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Competitor Website</label>
                                            <input type="url" name="url" class="form-control" placeholder="https://competitor.com">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Industry</label>
                                    <input type="text" name="industry" class="form-control" required placeholder="e.g., SaaS, E-commerce">
                                </div>

                                <div class="form-group">
                                    <label>Platforms They're On</label>
                                    <div class="d-flex gap-8 align-items-center">
                                        <label class="d-flex gap-8 align-items-center"><input class="mb-0" type="checkbox" name="platforms[]" value="facebook"> Facebook</label>
                                        <label class="d-flex gap-8 align-items-center"><input class="mb-0" type="checkbox" name="platforms[]" value="instagram"> Instagram</label>
                                        <label class="d-flex gap-8 align-items-center"><input class="mb-0" type="checkbox" name="platforms[]" value="linkedin"> LinkedIn</label>
                                        <label class="d-flex gap-8 align-items-center"><input class="mb-0" type="checkbox" name="platforms[]" value="twitter"> Twitter</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Sample Content (optional - paste 2-3 of their posts)</label>
                                    <textarea name="sample_content" class="form-control" rows="4" placeholder="Paste some of their recent posts here..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-dark w-100 mt-2">
                                    <i class="fas fa-chart-bar"></i> Analyze Competitor
                                </button>
                            </form>

                            <div id="competitor-results" class="mt-4" style="display:none;">
                                <!-- Results will appear here -->
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    
    // Manual tab switching for compatibility
    $('#strategyTabs a').on('click', function (e) {
        e.preventDefault();
        
        // Remove active from all tabs and panes
        $('#strategyTabs .nav-link').removeClass('active');
        $('.tab-pane').removeClass('show active');
        
        // Add active to clicked tab
        $(this).addClass('active');
        
        // Show corresponding pane
        const target = $(this).attr('href');
        $(target).addClass('show active');
    });

    // Content Calendar Form
    $('#calendar-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serializeArray();
        const data = {};
        formData.forEach(field => {
            if (field.name.includes('[]')) {
                const key = field.name.replace('[]', '');
                if (!data[key]) data[key] = [];
                data[key].push(field.value);
            } else {
                data[field.name] = field.value;
            }
        });

        $('#calendar-results').html('<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Generating content calendar...</div>').show();

        $.ajax({
            url: '{{ route("ai-strategy.calendar.generate") }}',
            method: 'POST',
            data: data,
            success: function(response) {
                if (response.status === 1) {
                    displayCalendarResults(response.data);
                } else {
                    $('#calendar-results').html('<div class="alert alert-danger">' + response.message + (response.error ? '<br><small>' + response.error + '</small>' : '') + '</div>');
                }
            },
            error: function(xhr) {
                $('#calendar-results').html('<div class="alert alert-danger">Error: ' + xhr.responseText + '</div>');
            }
        });
    });

    // Hashtags Form
    $('#hashtags-form').on('submit', function(e) {
        e.preventDefault();
        
        const data = $(this).serialize();
        $('#hashtags-results').html('<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Researching hashtags...</div>').show();

        $.ajax({
            url: '{{ route("ai-strategy.hashtags.research") }}',
            method: 'POST',
            data: data,
            success: function(response) {
				
	console.log(response);
                if (response.status === 1) {
                    displayHashtagResults(response.data);
                } else {
                    $('#hashtags-results').html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function(xhr) {
                $('#hashtags-results').html('<div class="alert alert-danger">Error: ' + xhr.responseText + '</div>');
            }
        });
    });

    // Topics Form
    $('#topics-form').on('submit', function(e) {
        e.preventDefault();
        
        const data = $(this).serialize();
        $('#topics-results').html('<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Researching topics...</div>').show();

        $.ajax({
            url: '{{ route("ai-strategy.topics.research") }}',
            method: 'POST',
            data: data,
            success: function(response) {
				
	console.log(response);
                if (response.status === 1) {
                    displayTopicResults(response.data);
                } else {
                    $('#topics-results').html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function(xhr) {
                $('#topics-results').html('<div class="alert alert-danger">Error: ' + xhr.responseText + '</div>');
            }
        });
    });

    // Competitor Form
    $('#competitor-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serializeArray();
        const data = {};
        formData.forEach(field => {
            if (field.name.includes('[]')) {
                const key = field.name.replace('[]', '');
                if (!data[key]) data[key] = [];
                data[key].push(field.value);
            } else {
                data[field.name] = field.value;
            }
        });

        $('#competitor-results').html('<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Analyzing competitor...</div>').show();

        $.ajax({
            url: '{{ route("ai-strategy.competitor.analyze") }}',
            method: 'POST',
            data: data,
            success: function(response) {
				
	console.log(response);
                if (response.status === 1) {
                    displayCompetitorResults(response.data);
                } else {
                    $('#competitor-results').html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function(xhr) {
                $('#competitor-results').html('<div class="alert alert-danger">Error: ' + xhr.responseText + '</div>');
            }
        });
    });

    // Display Functions
   // Add this to your view file - REPLACE the existing displayCalendarResults function

function displayCalendarResults(data) {
	console.log(data);
    let html = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Content calendar generated successfully!</div>';
    
    // Show strategy notes if available
    if (data.strategy_notes) {
        html += '<div class="alert alert-info"><strong><i class="fas fa-lightbulb"></i> Strategy Notes:</strong><br>' + data.strategy_notes + '</div>';
    }
    
    // Show the calendar items
    if (data.calendar_items && data.calendar_items.length > 0) {
        html += '<h5 class="mt-4 mb-3">Generated Content Calendar (' + data.calendar_items.length + ' posts)</h5>';
        html += '<div class="table-responsive">';
        html += '<table class="table table-hover table-bordered">';
        html += '<thead class="thead-light">';
        html += '<tr>';
        html += '<th width="10%">Date & Time</th>';
        html += '<th width="20%">Title</th>';
        html += '<th width="35%">Content</th>';
        html += '<th width="20%">Hashtags</th>';
        html += '<th width="15%">Type</th>';
        html += '</tr>';
        html += '</thead><tbody>';
        
        data.calendar_items.forEach(item => {
            // Format date
            let postDate = new Date(item.suggested_post_time);
            let dateStr = postDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            let timeStr = postDate.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            
            // Get hashtags
            let hashtags = '';
            if (item.suggested_hashtags && Array.isArray(item.suggested_hashtags)) {
                hashtags = item.suggested_hashtags.map(tag => 
                    '<span class="badge badge-info mr-1 mb-1">' + tag + '</span>'
                ).join(' ');
            }
            
            // Get type badge color
            let typeBadge = 'info';
            if (item.content_type === 'promotional') typeBadge = 'warning';
            if (item.content_type === 'engaging') typeBadge = 'success';
            if (item.content_type === 'educational') typeBadge = 'primary';
            
            html += '<tr>';
            html += '<td><small><strong>' + dateStr + '</strong><br>' + timeStr + '</small></td>';
            html += '<td><strong>' + item.title + '</strong></td>';
            html += '<td>' + item.content + '</td>';
            html += '<td>' + hashtags + '</td>';
            html += '<td><span class="badge badge-' + typeBadge + '">' + item.content_type + '</span></td>';
            html += '</tr>';
        });
        
        html += '</tbody></table></div>';
        
        // Add summary
        html += '<div class="row mt-3">';
        html += '<div class="col-md-12">';
        html += '<div class="card">';
        html += '<div class="card-body">';
        html += '<h6>Campaign Summary</h6>';
        html += '<ul>';
        html += '<li><strong>Campaign:</strong> ' + data.content_plan.name + '</li>';
        html += '<li><strong>Duration:</strong> ' + new Date(data.content_plan.start_date).toLocaleDateString() + ' to ' + new Date(data.content_plan.end_date).toLocaleDateString() + '</li>';
        html += '<li><strong>Platforms:</strong> ' + data.content_plan.platforms.join(', ') + '</li>';
        html += '<li><strong>Total Posts:</strong> ' + data.calendar_items.length + '</li>';
        html += '</ul>';
        html += '</div></div></div></div>';
    } else {
        html += '<div class="alert alert-warning">No calendar items were generated.</div>';
    }
    
    $('#calendar-results').html(html).show();
}

function displayHashtagResults(data) {
    let html = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Hashtags researched successfully!</div>';
    
    if (data && data.hashtags && data.hashtags.length > 0) {
        html += '<h5 class="mt-3 mb-3">Recommended Hashtags</h5>';
        html += '<div class="row">';
        
        data.hashtags.forEach(hashtag => {
            let popularityBadge = 'secondary';
            if (hashtag.popularity === 'very_high') popularityBadge = 'danger';
            else if (hashtag.popularity === 'high') popularityBadge = 'warning';
            else if (hashtag.popularity === 'medium') popularityBadge = 'info';
            
            html += '<div class="col-md-6 col-lg-4 mb-3">';
            html += '<div class="card h-100">';
            html += '<div class="card-body">';
            html += '<h5 class="card-title">' + hashtag.hashtag + '</h5>';
            html += '<span class="badge badge-' + popularityBadge + ' mb-2">' + hashtag.popularity.replace('_', ' ').toUpperCase() + '</span>';
            html += '<p class="card-text small">' + hashtag.reasoning + '</p>';
            html += '<div class="mt-2">';
            html += '<small><strong>Relevance:</strong> ' + hashtag.relevance_score + '/100</small><br>';
            html += '<small><strong>Reach:</strong> ' + hashtag.reach_potential + '</small>';
            html += '</div>';
            html += '</div></div></div>';
        });
        
        html += '</div>';
        
        if (data.strategy_notes) {
            html += '<div class="alert alert-info mt-3"><strong><i class="fas fa-lightbulb"></i> Strategy Notes:</strong><br>' + data.strategy_notes + '</div>';
        }
    } else {
        html += '<div class="alert alert-warning">No hashtags were generated.</div>';
    }
    
    $('#hashtags-results').html(html).show();
}

function displayTopicResults(data) {
    let html = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Topics researched successfully!</div>';
    
    if (data.research && data.research.topics && data.research.topics.length > 0) {
        html += '<h5 class="mt-3 mb-3">Topic Research Results</h5>';
        
        data.research.topics.forEach(topic => {
            let scoreColor = 'success';
            if (topic.relevance_score < 50) scoreColor = 'warning';
            if (topic.relevance_score < 30) scoreColor = 'danger';
            
            html += '<div class="card mb-3">';
            html += '<div class="card-header">';
            html += '<h5 class="mb-0">' + topic.topic + ' ';
            html += '<span class="badge badge-' + scoreColor + ' float-right">Relevance: ' + topic.relevance_score + '/100</span>';
            html += '</h5></div>';
            html += '<div class="card-body">';
            html += '<p>' + topic.summary + '</p>';
            
            if (topic.trending_keywords && topic.trending_keywords.length > 0) {
                html += '<p><strong><i class="fas fa-fire"></i> Trending Keywords:</strong><br>';
                topic.trending_keywords.forEach(keyword => {
                    html += '<span class="badge badge-warning mr-1 mb-1">' + keyword + '</span>';
                });
                html += '</p>';
            }
            
            if (topic.related_topics && topic.related_topics.length > 0) {
                html += '<p><strong><i class="fas fa-link"></i> Related Topics:</strong><br>';
                topic.related_topics.forEach(related => {
                    html += '<span class="badge badge-secondary mr-1 mb-1">' + related + '</span>';
                });
                html += '</p>';
            }
            
            if (topic.content_angles && topic.content_angles.length > 0) {
                html += '<p><strong><i class="fas fa-lightbulb"></i> Content Angles:</strong></p>';
                html += '<ul>';
                topic.content_angles.forEach(angle => {
                    html += '<li>' + angle + '</li>';
                });
                html += '</ul>';
            }
            
            html += '</div></div>';
        });
    } else {
        html += '<div class="alert alert-warning">No topics were researched.</div>';
    }
    
    $('#topics-results').html(html).show();
}

function displayCompetitorResults(data) {
    let html = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Competitor analyzed successfully!</div>';
    
    if (data.analysis) {
        html += '<div class="row">';
        
        // Strengths
        if (data.analysis.strengths) {
            html += '<div class="col-md-6 mb-3">';
            html += '<div class="card h-100 border-success">';
            html += '<div class="card-header bg-success text-white"><i class="fas fa-thumbs-up"></i> Strengths</div>';
            html += '<div class="card-body">' + data.analysis.strengths + '</div>';
            html += '</div></div>';
        }
        
        // Weaknesses
        if (data.analysis.weaknesses) {
            html += '<div class="col-md-6 mb-3">';
            html += '<div class="card h-100 border-warning">';
            html += '<div class="card-header bg-warning text-dark"><i class="fas fa-exclamation-triangle"></i> Weaknesses</div>';
            html += '<div class="card-body">' + data.analysis.weaknesses + '</div>';
            html += '</div></div>';
        }
        
        html += '</div>';
        
        // Content Themes
        if (data.analysis.content_themes && data.analysis.content_themes.length > 0) {
            html += '<div class="card mb-3">';
            html += '<div class="card-header"><i class="fas fa-hashtag"></i> Content Themes</div>';
            html += '<div class="card-body"><ul>';
            data.analysis.content_themes.forEach(theme => {
                html += '<li>' + theme + '</li>';
            });
            html += '</ul></div></div>';
        }
        
        // Content Types
        if (data.analysis.content_types && data.analysis.content_types.length > 0) {
            html += '<div class="card mb-3">';
            html += '<div class="card-header"><i class="fas fa-file-alt"></i> Content Types Used</div>';
            html += '<div class="card-body">';
            data.analysis.content_types.forEach(type => {
                html += '<span class="badge badge-info mr-2 mb-2">' + type + '</span>';
            });
            html += '</div></div>';
        }
        
        // Recommendations
        if (data.analysis.recommendations && data.analysis.recommendations.length > 0) {
            html += '<div class="card">';
            html += '<div class="card-header bg-primary text-white"><i class="fas fa-lightbulb"></i> Recommendations</div>';
            html += '<div class="card-body"><ol>';
            data.analysis.recommendations.forEach(rec => {
                html += '<li>' + rec + '</li>';
            });
            html += '</ol></div></div>';
        }
    } else {
        html += '<div class="alert alert-warning">No analysis data available.</div>';
    }
    
    $('#competitor-results').html(html).show();
}
    function getPopularityBadge(popularity) {
        const badges = {
            'very_high': 'danger',
            'high': 'warning',
            'medium': 'info',
            'low': 'secondary'
        };
        return badges[popularity] || 'secondary';
    }
});
</script>
@endpush

@endsection