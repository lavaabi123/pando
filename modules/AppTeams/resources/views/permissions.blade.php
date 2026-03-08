<div class="card b-r-6 border-gray-300 mb-3">
    <div class="card-header">
        <label class="fw-6 fs-14 text-gray-700">
            {{ __("Team Members") }}
        </label>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="mb-4">
                    <input type="hidden" value="1" id="appteams" name="permissions[appteams]" checked="checked">
                    <input class="form-control" name="permissions[team_members]" id="credits" type="number" value="{{ $permissions['team_members'] ?? 1 }}">
                </div>
            </div>
        </div>
    </div>
</div>
  