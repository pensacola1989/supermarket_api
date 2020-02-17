<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 10/31/17
 * Time: 4:21 PM
 */

namespace App\Http\Controllers;


use App\Services\Admin\AdminContract;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;
use Auth;

class AdminController extends Controller
{
    private $validateRule = [
        'mobile' => 'required|digits:11|unique:admins',
        'userName' => 'required|between:1,50'
    ];

    private $_jwt;

    private $_adminRepository;

    public function __construct(AdminContract $adminContract, JWTAuth $jwt)
    {
        $this->middleware('auth:admin', ['except' => ['login']]);
        $this->_adminRepository = $adminContract;
        $this->_jwt = $jwt;
    }

    public function search(Request $request)
    {
        return $this->_adminRepository->search($request);
    }

    public function create(Request $request)
    {
        $this->customerValidate($request, $this->validateRule);
        $admin = $this->_adminRepository->getNew($request->input());
        $ret = $admin->save();

        return $admin;
//        $user = $this->userRepository->createUser($request->input());
//
//        return $user;
    }

    public function update(Request $request, $id)
    {
//        $this->validate($request, $this->validateRule);
//        $user = $this->userRepository->updateUserInfo($id, $request->input());
//
//        return $user;
    }

    public function login(Request $request)
    {
        $credentials = $request->only('userName', 'password');
        if ($token = $this->guard('admin')->attempt($credentials)) {
            return $this->respondWithToken($token);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function all()
    {
        return $this->_adminRepository->getAll();
    }

    public function destroy($id)
    {
        $model = $this->_adminRepository->requireById($id);
        $this->_adminRepository->delete($model);

        return $this->OK();
    }

    public function show($id)
    {
        $model = $this->_adminRepository->requireByExternalId($id);

        return $model;
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->guard('admin')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard('admin')->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard('admin')->factory()->getTTL() * 60
        ]);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard($guard)
    {
        return Auth::guard($guard);
    }

}