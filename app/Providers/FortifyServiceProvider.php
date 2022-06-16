<?php

namespace App\Providers;

use App\Actions\Fortify\AttemptToAuthenticate;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\RedirectIfTwoFactorAuthenticatable;
use App\Actions\Fortify\RedirectIfTwoFactorConfirmed;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\Country;
use App\Models\Setting;
use App\Models\SocialAuthSetting;
use App\Models\User;
use Carbon\Carbon;
use Froiden\Envato\Traits\AppBoot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Features;

class FortifyServiceProvider extends ServiceProvider
{

    use AppBoot;

    public function __construct()
    {
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Fortify::authenticateThrough(function (Request $request) {

            return array_filter([
                config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
                Features::enabled(Features::twoFactorAuthentication()) ? RedirectIfTwoFactorConfirmed::class : null,
                AttemptToAuthenticate::class,
                PrepareAuthenticatedSession::class,
            ]);
        });
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Fortify::authenticateThrough();
        Fortify::authenticateUsing(function (Request $request) {
            $rules = [
                'email' => 'required|email:rfc|regex:/(.+)@(.+)\.(.+)/i'
            ];

            $request->validate($rules);

            $user = User::where('email', $request->email)
                ->where('status', 'active')
                ->where('login', 'enable')
                ->first();

            if ($user && Hash::check($request->password, $user->password)) {
                return $user;
            }
        });

        Fortify::requestPasswordResetLinkView(function () {
            $global = $setting = Setting::first();
            App::setLocale($global->locale);
            Carbon::setLocale($global->locale);
            setlocale(LC_TIME, $global->locale . '_' . strtoupper($global->locale));

            return view('auth.passwords.email', ['setting' => $setting, 'global' => $global]);
        });

        Fortify::loginView(function () {
            $this->showInstall();
            $this->checkMigrateStatus();

            if (!$this->isLegal()) {
                return redirect('verify-purchase');
            }

            if (Schema::hasTable('organisation_settings')) {
                $global = $setting = Setting::first();

                App::setLocale($global->locale);
                Carbon::setLocale($global->locale);
                setlocale(LC_TIME, $global->locale . '_' . strtoupper($global->locale));

                $userTotal = User::count();

                if ($userTotal == 0) {
                    return view('auth.account_setup', ['global' => $global, 'setting' => $setting]);
                }

                $socialAuthSettings = SocialAuthSetting::first();

                return view('auth.login', ['global' => $global, 'socialAuthSettings' => $socialAuthSettings, 'setting' => $setting]);
            }

        });

        Fortify::resetPasswordView(function ($request) {
            $setting = $global = Setting::first();

            App::setLocale($global->locale);
            Carbon::setLocale($global->locale);
            setlocale(LC_TIME, $global->locale . '_' . strtoupper($global->locale));

            return view('auth.reset-password', ['request' => $request, 'setting' => $setting, 'global' => $global]);
        });

        Fortify::confirmPasswordView(function ($request) {
            $setting = $global = Setting::first();

            App::setLocale($global->locale);
            Carbon::setLocale($global->locale);
            setlocale(LC_TIME, $global->locale . '_' . strtoupper($global->locale));
            
            return view('auth.password-confirm', ['request' => $request, 'setting' => $setting, 'global' => $global]);
        });

        Fortify::twoFactorChallengeView(function () {
            $setting = $global = Setting::first();

            App::setLocale($global->locale);
            Carbon::setLocale($global->locale);
            setlocale(LC_TIME, $global->locale . '_' . strtoupper($global->locale));

            return view('auth.two-factor-challenge', ['setting' => $setting, 'global' => $global]);
        });

        Fortify::registerView(function () {
            if (Schema::hasTable('organisation_settings')) {
                $global = $setting = Setting::first();

                if (!$setting->allow_client_signup) {
                    return redirect(route('login'));
                }

                App::setLocale($global->locale);
                Carbon::setLocale($global->locale);
                setlocale(LC_TIME, $global->locale . '_' . strtoupper($global->locale));

                return view('auth.register', ['global' => $global, 'setting' => $setting]);
            }

        });

    }

    public function checkMigrateStatus()
    {
        return check_migrate_status();
    }

}
