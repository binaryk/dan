<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Http\Controllers\Controller;
use App\Events\Frontend\Auth\UserRegistered;
use App\Models\Access\User\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Http\Requests\Frontend\Auth\RegisterRequest;
use App\Repositories\Frontend\Access\User\UserRepository;

/**
 * Class RegisterController.
 */
class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * @var UserRepository
     */
    protected $user;

    /**
     * RegisterController constructor.
     *
     * @param UserRepository $user
     */
    public function __construct(UserRepository $user)
    {
        // Where to redirect users after registering
        $this->redirectTo = route(homeRoute());

        $this->user = $user;
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        return view('frontend.auth.register');
    }

    public function baseToJpeg($baseImg) {
        $imgData1 = substr($baseImg, 1+strrpos($baseImg, ','));
        $path = public_path('uploads/'.generateRandomString().'.jpg');
        file_put_contents($path, base64_decode($imgData1));
        return $path;
    }

    /**
     * @param RegisterRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function register(RegisterRequest $request)
    {

        $path = $this->baseToJpeg($request->get('photo_path'));
        if (config('access.users.confirm_email') || config('access.users.requires_approval')) {
            $data = $request->only('first_name', 'last_name', 'email', 'password');
            $data['photo_path'] = $path;
            $user = $this->user->create($data);
            event(new UserRegistered($user));

            return redirect($this->redirectPath())->withFlashSuccess(
                config('access.users.requires_approval') ?
                    trans('exceptions.frontend.auth.confirmation.created_pending') :
                    trans('exceptions.frontend.auth.confirmation.created_confirm')
            );
        } else {
            $data = $request->only('first_name', 'last_name', 'email', 'password');
            $data['photo_path'] = $path;
            /*convert photo to bytes*/
            $data['byte_photo'] = $this->dHash($data['photo_path']);
            if(! $this->isValidPhoto($data['photo_path'])) {
                return redirect($this->redirectPath())->withFlashWarning('Aceasta poza a fost deja utilizata de catre alt utilizator.');
            };

            $nUser = $this->user->create($data);
            access()->login($nUser);
            event(new UserRegistered(access()->user()));

            return redirect($this->redirectPath());
        }
    }


    public function isValidPhoto($path)
    {
        $users = User::all();
        $isValid = true;
        $currentPhotoByte = $this->dHash($path);

        foreach ($users as $user) {
            if ($user->byte_photo != null) {
                $user_photo_byte = $user->byte_photo;
                $percentage = (strlen($user_photo_byte) - $this->HammingDistance($user_photo_byte, $currentPhotoByte)) * 100 / strlen($user_photo_byte);

                if ($percentage > 70) {
                    $isValid = false;
                    break;
                }
            }
        }

        return $isValid;
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
        $len = count($a1) > count($a2) ? count($a2) : count($a1);
        $dh = 0;
        for ($i = 0; $i < $len; $i++)
            if ($a1[$i] != $a2[$i]) $dh++;
        return $dh;
    }

}
