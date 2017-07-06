<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Helpers\Auth\Auth;
use Illuminate\Http\Request;
use App\Exceptions\GeneralException;
use App\Http\Controllers\Controller;
use App\Helpers\Frontend\Auth\Socialite;
use App\Events\Frontend\Auth\UserLoggedIn;
use App\Events\Frontend\Auth\UserLoggedOut;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Repositories\Frontend\Access\User\UserSessionRepository;

/**
 * Class LoginController.
 */
class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @return string
     */
    public function redirectPath()
    {
        return route(homeRoute());
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('frontend.auth.login')
            ->withSocialiteLinks((new Socialite())->getSocialLinks());
    }

    /**
     * @param Request $request
     * @param $user
     *
     * @throws GeneralException
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function authenticated(Request $request, $user)
    {
        $photo = $request->file('photo_path');

        if ($photo && $user) {

            $img_name = $request->file('photo_path')->getClientOriginalName();
            $request->file('photo_path')->move(public_path('tmp'), $img_name);
            $physicPhoto = public_path('uploads/' . $img_name);
            $bytePhoto = $this->dHash($physicPhoto);
            if ( ! $this->isValidPhoto($physicPhoto, $user)) {
                access()->logout();
                return redirect($this->redirectPath())->withFlashWarning('Aceasta poza nu coincide cu acest utilizator');
            };
        } else {
            access()->logout();
            return redirect($this->redirectPath())->withFlashWarning('Aceasta poza nu coincide cu acest utilizator');
        }

        /*
         * Check to see if the users account is confirmed and active
         */
        if ( ! $user->isConfirmed()) {
            access()->logout();

            // If the user is pending (account approval is on)
            if ($user->isPending()) {
                throw new GeneralException(trans('exceptions.frontend.auth.confirmation.pending'));
            }

            // Otherwise see if they want to resent the confirmation e-mail
            throw new GeneralException(trans('exceptions.frontend.auth.confirmation.resend', ['user_id' => $user->id]));
        } elseif ( ! $user->isActive()) {
            access()->logout();
            throw new GeneralException(trans('exceptions.frontend.auth.deactivated'));
        }

        event(new UserLoggedIn($user));

        // If only allowed one session at a time
        if (config('access.users.single_login')) {
            app()->make(UserSessionRepository::class)->clearSessionExceptCurrent($user);
        }

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Log the user out of the application.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        /*
         * Boilerplate needed logic
         */

        /*
         * Remove the socialite session variable if exists
         */
        if (app('session')->has(config('access.socialite_session_name'))) {
            app('session')->forget(config('access.socialite_session_name'));
        }

        /*
         * Remove any session data from backend
         */
        app()->make(Auth::class)->flushTempSession();

        /*
         * Fire event, Log out user, Redirect
         */
        event(new UserLoggedOut($this->guard()->user()));

        /*
         * Laravel specific logic
         */
        $this->guard()->logout();
        $request->session()->flush();
        $request->session()->regenerate();

        return redirect('/');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logoutAs()
    {
        //If for some reason route is getting hit without someone already logged in
        if ( ! access()->user()) {
            return redirect()->route('frontend.auth.login');
        }

        //If admin id is set, relogin
        if (session()->has('admin_user_id') && session()->has('temp_user_id')) {
            //Save admin id
            $admin_id = session()->get('admin_user_id');

            app()->make(Auth::class)->flushTempSession();

            //Re-login admin
            access()->loginUsingId((int) $admin_id);

            //Redirect to backend user page
            return redirect()->route('admin.access.user.index');
        } else {
            app()->make(Auth::class)->flushTempSession();

            //Otherwise logout and redirect to login
            access()->logout();

            return redirect()->route('frontend.auth.login');
        }
    }

    public function dHash($file)
    {
        $hash = '';
        $size = 8;
        list($w, $h, $t) = getimagesize($file);
        $im = imagecreatetruecolor($size + 1, $size);
        imagefilter($im, IMG_FILTER_GRAYSCALE);
        switch ($t) {
            case 1:
                $oi = imagecreatefromgif($file);
                break;
            case 2:
                $oi = imagecreatefromjpeg($file);
                break;
            case 3:
                $oi = imagecreatefrompng($file);
                break;
        }
        imagecopyresampled($im, $oi, 0, 0, 0, 0, $size + 1, $size, $w, $h);
        imagedestroy($oi);
        for ($y = 0; $y < $size; $y++) {
            $val = '';
            for ($x = 0; $x < $size; $x++) {
                $curr = imagecolorat($im, $x, $y);
                $next = imagecolorat($im, $x + 1, $y);
                $val .= ($curr > $next) ? 1 : 0;
            }
            $hash .= str_pad(dechex(bindec($val)), 2, 0, STR_PAD_LEFT);
        }
        imagedestroy($im);
        return base_convert($hash, 16, 2);
    }

    public function HammingDistance($bin1, $bin2)
    {
        $a1 = str_split($bin1);
        $a2 = str_split($bin2);
        $count = count($a1) > count($a2) ? count($a2) : count($a1);
        $dh = 0;
        for ($i = 0; $i < $count; $i++)
            if ($a1[$i] != $a2[$i]) $dh++;
        return $dh;
    }

    public function isValidPhoto($path, $user)
    {
        $isValid = true;
        $currentPhotoByte = $this->dHash($path);
        if ($user->byte_photo != null) {
            $user_photo_byte = $user->byte_photo;
            $percentage = (strlen($user_photo_byte) - $this->HammingDistance($user_photo_byte, $currentPhotoByte)) * 100 / strlen($user_photo_byte);

            if ($percentage > 70) {
                $isValid = false;
            }
        }

        return $isValid;
    }
}
