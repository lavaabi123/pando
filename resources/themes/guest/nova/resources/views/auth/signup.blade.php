<section class="relative w-screen min-h-screen flex flex-column flex-sm-row items-stretch overflow-hidden bg-white overflow-x-hidden">

    @include("partials/login-screen", ["name" => __("Create an account & get started.")])

    <div class="flex flex-col justify-center flex-1 px-8 py-16 bg-white z-10">
        <form class="actionForm max-w-md mx-auto w-full space-y-5" action="{{ module_url('do_signup') }}" method="POST" data-loading="1">
            <div class="show-on-mobile">
                <a class="mb-4 inline-block" href="{{ url('') }}">
                    <img class="h-10" src="{{ url( get_option("website_logo_brand_dark", asset('public/img/logo-brand-dark.png')) ) }}" alt="">
                </a>
                <h2 class="mb-10 text-4xl md:text-4xl font-bold font-heading tracking-px-n leading-tight">
                    {{ __("Create an account & get started.") }}
                </h2>
            </div>
            <h4 class="fw-bold fs-5 mt-4 mb-3 d-none d-sm-block">Welcome! Sign up</h4>
            <!-- Full Name -->
            <div class="mb-3">
                <!--<label for="fullname" class="block text-gray-700 font-semibold mb-2">{{ __("Full Name") }}</label>-->
                <input type="text" id="fullname" name="fullname" class="form-control" placeholder="{{ __('Enter your full name') }}" required>
            </div>

            <!-- Email -->
            <div class="mb-3">
                <!--<label for="email" class="block text-gray-700 font-semibold mb-2">{{ __("Email Address") }}</label>-->
                <input type="email" id="email" name="email" class="form-control" placeholder="{{ __('Enter your email address') }}" required>
                <p class="email-validation-message mt-1 text-sm"></p>
            </div>

            <!-- Username -->
            <div class="mb-3">
                <!--<label for="username" class="block text-gray-700 font-semibold mb-2">{{ __("Username") }}</label>-->
                <input type="text" id="username" name="username" class="form-control" placeholder="{{ __('Choose a username') }}" required>
                <p class="username-validation-message mt-1 text-sm"></p>
            </div>

            <!-- Password -->
            <div class="mb-3">
                <!--<label for="password" class="block text-gray-700 font-semibold mb-2">{{ __("Password") }}</label>-->
                <input type="password" id="password" name="password" class="form-control" placeholder="{{ __('Enter your password') }}" required>
                <p class="password-validation-message mt-1 text-sm"></p>
            </div>

            <!-- Confirm Password -->
            <div class="mb-3">
                <!--<label for="password_confirmation" class="block text-gray-700 font-semibold mb-2">{{ __("Confirm Password") }}</label>-->
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="{{ __('Re-enter your password') }}" required>
                <p class="password-confirmation-message mt-1 text-sm"></p>
            </div>
			
			  <!-- Timezone -->
		    <div class="mb-3">
		        <!--<label for="timezone" class="block text-gray-700 font-semibold mb-2">{{ __("Timezone") }}</label>-->
		        <select id="timezone" name="timezone" class="form-control" required>
		            <option value="">{{ __("Select your timezone") }}</option>
		            @foreach(timezone_identifiers_list() as $tz)
		                <option value="{{ $tz }}" {{ old('timezone') == $tz ? 'selected' : '' }}>
		                    {{ $tz }}
		                </option>
		            @endforeach
		        </select>
		    </div>

            <div class="mb-3">
                {!! Captcha::render(); !!}
            </div>

            <div class="flex flex-wrap justify-between mb-4">
                <div class="w-full">
                    <div class="flex items-center">
                        <input class="w-4 h-4" id="accep_terms" name="accep_terms" type="checkbox" value="1" required>
                        <label class="ml-2 text-gray-700 font-medium" for="accep_terms">
                            <span>{{ __("I agree to the") }}</span>
                            <a class="text-indigo-600 hover:text-indigo-700" href="{{ url('terms-of-service') }}">{{ __("Terms & Conditions") }}</a>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Error Message -->
            <div class="msg-error mb-2"></div>

            <!-- Submit -->
            <!--<button type="submit" id="signup-submit-btn"
                    class="mb-8 py-4 px-9 w-full text-white font-semibold border border-indigo-700 rounded-xl shadow-4xl focus:ring focus:ring-indigo-300 bg-indigo-600 hover:bg-indigo-700 transition ease-in-out duration-200">
                {{ __("Sign Up") }}
            </button>-->
			
			
			<button type="submit" id="signup-submit-btn" class="w-full btn btn-primary submit-btn">
				<span class="btn-text">{{ __("Sign Up") }}</span>
				<span class="btn-loader" style="display: none;">
					<i class="fas fa-spinner fa-spin"></i> {{ __("Signing Up...") }}
				</span>
			</button>

            <!-- Switch to Sign In -->
            <p class="text-center text-base-content/80 pt-4">
                {{ __("Already have an account?") }}
                <a href="{{ url('auth/login') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">{{ __("Sign in") }}</a>
            </p>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const passwordConfirmationInput = document.getElementById('password_confirmation');
    
    const emailMessage = document.querySelector('.email-validation-message');
    const usernameMessage = document.querySelector('.username-validation-message');
    const passwordMessage = document.querySelector('.password-validation-message');
    const passwordConfirmationMessage = document.querySelector('.password-confirmation-message');
    
    const submitBtn = document.getElementById('signup-submit-btn');
    
    let emailAvailable = false;
    let usernameAvailable = false;
    let passwordValid = false;
    let passwordsMatch = false;
    let emailTimeout, usernameTimeout;

    // Check Email Availability
    emailInput.addEventListener('input', function() {
        clearTimeout(emailTimeout);
        const email = this.value.trim();
        
        if (!email || !isValidEmail(email)) {
            emailMessage.textContent = '';
            emailMessage.className = 'email-validation-message mt-1 text-sm';
            emailAvailable = false;
            updateSubmitButton();
            return;
        }

        emailTimeout = setTimeout(() => {
            checkEmailAvailability(email);
        }, 500);
    });

    // Check Username Availability
    usernameInput.addEventListener('input', function() {
        clearTimeout(usernameTimeout);
        const username = this.value.trim();
        
        if (!username || username.length < 3) {
            usernameMessage.textContent = '';
            usernameMessage.className = 'username-validation-message mt-1 text-sm';
            usernameAvailable = false;
            updateSubmitButton();
            return;
        }

        usernameTimeout = setTimeout(() => {
            checkUsernameAvailability(username);
        }, 500);
    });

    // Check Password Strength
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        
        if (!password) {
            passwordMessage.textContent = '';
            passwordMessage.className = 'password-validation-message mt-1 text-sm';
            passwordValid = false;
            updateSubmitButton();
            return;
        }

        validatePassword(password);
        
        // Also check if passwords match when password changes
        if (passwordConfirmationInput.value) {
            checkPasswordMatch();
        }
    });

    // Check Password Confirmation Match
    passwordConfirmationInput.addEventListener('input', function() {
        checkPasswordMatch();
    });

    function checkEmailAvailability(email) {
        fetch('{{ url("auth/check-email") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            emailAvailable = data.available;
            
            if (data.available) {
                emailMessage.textContent = '✓ ' + data.message;
                emailMessage.className = 'email-validation-message mt-1 text-sm text-success';
                emailInput.classList.remove('border-red-500');
                emailInput.classList.add('border-green-500');
            } else {
                emailMessage.textContent = '✗ ' + data.message;
                emailMessage.className = 'email-validation-message mt-1 text-sm text-danger';
                emailInput.classList.remove('border-green-500');
                emailInput.classList.add('border-red-500');
            }
            
            updateSubmitButton();
        })
        .catch(error => {
            console.error('Error checking email:', error);
        });
    }

    function checkUsernameAvailability(username) {
        fetch('{{ url("auth/check-username") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ username: username })
        })
        .then(response => response.json())
        .then(data => {
            usernameAvailable = data.available;
            
            if (data.available) {
                usernameMessage.textContent = '✓ ' + data.message;
                usernameMessage.className = 'username-validation-message mt-1 text-sm text-success';
                usernameInput.classList.remove('border-red-500');
                usernameInput.classList.add('border-green-500');
            } else {
                usernameMessage.textContent = '✗ ' + data.message;
                usernameMessage.className = 'username-validation-message mt-1 text-sm text-danger';
                usernameInput.classList.remove('border-green-500');
                usernameInput.classList.add('border-red-500');
            }
            
            updateSubmitButton();
        })
        .catch(error => {
            console.error('Error checking username:', error);
        });
    }

    function validatePassword(password) {
        const minLength = 8;
        const hasUpperCase = /[A-Z]/.test(password);
        const hasLowerCase = /[a-z]/.test(password);
        const hasNumbers = /\d/.test(password);
        const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
        
        if (password.length < minLength) {
            passwordValid = false;
            passwordMessage.textContent = '✗ Password must be at least ' + minLength + ' characters long';
            passwordMessage.className = 'password-validation-message mt-1 text-sm text-danger';
            passwordInput.classList.remove('border-green-500');
            passwordInput.classList.add('border-red-500');
        } else if (!hasUpperCase || !hasLowerCase || !hasNumbers) {
            passwordValid = false;
            passwordMessage.textContent = '✗ Password must contain uppercase, lowercase, and numbers';
            passwordMessage.className = 'password-validation-message mt-1 text-sm text-warning';
            passwordInput.classList.remove('border-green-500', 'border-red-500');
            passwordInput.classList.add('border-orange-500');
        } else {
            passwordValid = true;
            let strength = 'Strong';
            if (hasSpecialChar) {
                strength = 'Very Strong';
            }
            passwordMessage.textContent = '✓ Password is ' + strength;
            passwordMessage.className = 'password-validation-message mt-1 text-sm text-success';
            passwordInput.classList.remove('border-red-500', 'border-orange-500');
            passwordInput.classList.add('border-green-500');
        }
        
        updateSubmitButton();
    }

    function checkPasswordMatch() {
        const password = passwordInput.value;
        const passwordConfirmation = passwordConfirmationInput.value;
        
        if (!passwordConfirmation) {
            passwordConfirmationMessage.textContent = '';
            passwordConfirmationMessage.className = 'password-confirmation-message mt-1 text-sm';
            passwordsMatch = false;
            passwordConfirmationInput.classList.remove('border-green-500', 'border-red-500');
            updateSubmitButton();
            return;
        }
        
        if (password === passwordConfirmation) {
            passwordsMatch = true;
            passwordConfirmationMessage.textContent = '✓ {{ __("Passwords match") }}';
            passwordConfirmationMessage.className = 'password-confirmation-message mt-1 text-sm text-success';
            passwordConfirmationInput.classList.remove('border-red-500');
            passwordConfirmationInput.classList.add('border-green-500');
        } else {
            passwordsMatch = false;
            passwordConfirmationMessage.textContent = '✗ {{ __("Passwords do not match") }}';
            passwordConfirmationMessage.className = 'password-confirmation-message mt-1 text-sm text-danger';
            passwordConfirmationInput.classList.remove('border-green-500');
            passwordConfirmationInput.classList.add('border-red-500');
        }
        
        updateSubmitButton();
    }

    function updateSubmitButton() {
        const emailValue = emailInput.value.trim();
        const usernameValue = usernameInput.value.trim();
        const passwordValue = passwordInput.value;
        const passwordConfirmationValue = passwordConfirmationInput.value;
        
        // Disable submit if any validation fails
        const shouldDisable = 
            (emailValue && !emailAvailable) || 
            (usernameValue && !usernameAvailable) ||
            (passwordValue && !passwordValid) ||
            (passwordConfirmationValue && !passwordsMatch);
        
        if (shouldDisable) {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
});
</script>