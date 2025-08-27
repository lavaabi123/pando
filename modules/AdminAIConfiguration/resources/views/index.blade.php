@extends('layouts.app')

@section('sub_header')
    <x-sub-header
        title="{{ __('AI Configuration') }}"
        description="{{ __('Set up and customize your AI settings easily') }}"
    >
    </x-sub-header>
@endsection

@section('content')

<div class="container max-w-800 pb-5">
    <form class="actionForm" action="{{ url_admin("settings/save") }}">
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("General configuration") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select class="form-select" name="ai_status">
                                <option value="1" {{ get_option("ai_status", 1)==1?"selected":"" }} >{{ __("Enable") }}</option>
                                <option value="0" {{ get_option("ai_status", 1)==0?"selected":"" }} >{{ __("Disable") }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">{{ __('AI Platform') }}</label>
                            <select class="form-select" name="ai_platform">
                                <?php foreach (AI::getPlatforms() as $key => $value): ?>
                                    <option value="{{ $key }}" {{ get_option("ai_platform", "openai")==$key?"selected":"" }} >{{ $value }}</option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Default Language') }}</label>
                            <select class="form-select" name="ai_language">
                                <?php foreach (languages() as $key => $value): ?>
                                    <option value="{{ $key }}" {{ get_option("ai_language", "en-US")==$key?"selected":"" }} >{{ $value }}</option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Default Tone Of Voice') }}</label>
                            <select class="form-select" name="ai_tone_of_voice">
                                <?php foreach (tone_of_voices() as $key => $value): ?>
                                    <option value="{{ $key }}" {{ get_option("ai_tone_of_voice", "Friendly")==$key?"selected":"" }} >{{ $value }}</option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Default Creativity') }}</label>
                            <select class="form-select" name="ai_creativity">
                                <?php foreach (ai_creativity() as $key => $value): ?>
                                    <option value="{{ $key }}" {{ get_option("ai_creativity", 0)==$key?"selected":"" }}>{{ $value }}</option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Maximum Input Length') }}</label>
                            <input type="number" class="form-control" name="ai_max_input_lenght" value="{{ get_option("ai_max_input_lenght", 100) }}" >
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Maximum Ouput Length') }}</label>
                            <input type="number" class="form-control" name="ai_max_output_lenght" value="{{ get_option("ai_max_output_lenght", 1000) }}" >
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("OpenAI") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('API Key') }}</label>
                            <input placeholder="{{ __('Enter API Key') }}" class="form-control" name="ai_openai_api_key" id="ai_openai_api_key" type="text" value="{{ get_option("ai_openai_api_key", "") }}">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">{{ __('Default Model') }}</label>
                        <select class="form-select" name="ai_openai_model">
                            <?php foreach (AI::getAvailableModels("openai") as $key => $value): ?>
                                <option value="{{ $key }}" {{ get_option("ai_openai_model", "gpt-4-turbo")==$key?"selected":"" }} >{{ $value }}</option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("Gemini AI") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('API Key') }}</label>
                            <input placeholder="{{ __('Enter API Key') }}" class="form-control" name="ai_gemeni_api_key" id="ai_gemeni_api_key" type="text" value="{{ get_option("ai_gemeni_api_key", "") }}">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">{{ __('Default Model') }}</label>
                            <select class="form-select" name="ai_gemini_model">
                                <?php foreach (AI::getAvailableModels("gemini") as $key => $value): ?>
                                    <option value="{{ $key }}" {{ get_option("ai_gemini_model", "gemini-2.5-flash")==$key?"selected":"" }} >{{ $value }}</option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("Deepseek AI") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('API Key') }}</label>
                            <input placeholder="{{ __('Enter API Key') }}" class="form-control" name="ai_deepseek_api_key" id="ai_deepseek_api_key" type="text" value="{{ get_option("ai_deepseek_api_key", "") }}">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">{{ __('Default Model') }}</label>
                            <select class="form-select" name="ai_deepseek_model">
                                <?php foreach (AI::getAvailableModels("deepseek") as $key => $value): ?>
                                    <option value="{{ $key }}" {{ get_option("ai_deepseek_model", "deepseek-v3")==$key?"selected":"" }} >{{ $value }}</option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("Claude AI") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('API Key') }}</label>
                            <input placeholder="{{ __('Enter API Key') }}" class="form-control" name="ai_claude_api_key" id="ai_claude_api_key" type="text" value="{{ get_option("ai_claude_api_key", "") }}">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">{{ __('Default Model') }}</label>
                            <select class="form-select" name="ai_claude_model">
                                <?php foreach (AI::getAvailableModels("claude") as $key => $value): ?>
                                    <option value="{{ $key }}" {{ get_option("ai_claude_model", "claude-3-sonnet-20240229")==$key?"selected":"" }} >{{ $value }}</option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-dark b-r-10 w-100">
                {{ __('Save changes') }}
            </button>
        </div>

    </form>

</div>

@endsection
