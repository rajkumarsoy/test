<?php

namespace App\Http\Controllers\Backend\Access\User;

use App\Models\Access\User\User;
use App\Http\Controllers\Controller;
use App\Repositories\Backend\Access\Role\RoleRepository;
use App\Repositories\Backend\Access\User\UserRepository;
use App\Http\Requests\Backend\Access\User\StoreUserRequest;
use App\Http\Requests\Backend\Access\User\ManageUserRequest;
use App\Http\Requests\Backend\Access\User\UpdateUserRequest;
use Config;
use Validator;
use Exception;

/**
 * Class UserController.
 */
class UserController extends Controller
{
    /**
     * @var UserRepository
     */
    protected $users;

    /**
     * @var RoleRepository
     */
    protected $roles;

    /**
     * @param UserRepository $users
     * @param RoleRepository $roles
     */
    public function __construct(UserRepository $users, RoleRepository $roles)
    {
        $this->users = $users;
        $this->roles = $roles;
    }

    /**
     * @param ManageUserRequest $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(ManageUserRequest $request)
    {
        return view('backend.access.index');
    }

    /**
     * @param ManageUserRequest $request
     *
     * @return mixed
     */
    public function create(ManageUserRequest $request)
    {
        return view('backend.access.create')
            ->withRoles($this->roles->getAll());
    }

    /**
     * @param StoreUserRequest $request
     *
     * @return mixed
     */
    public function store(StoreUserRequest $request)
    {   
        try
        {
            $insert_array = [];
            $insert_array = $request->except('assignees_roles');
            $image_name ='';
            if ($request->hasFile('profile_pic'))
            {
                $image_name = $request->file('profile_pic')->getClientOriginalName();
                $ext    = explode('.', $image_name);
                $count  = count($ext);
                $picture = date('His').$image_name;
                $request->file('profile_pic')->move(Config::get('deployment.profile_image_upload') , $picture);
                $insert_array['profile_pic'] = $picture;

            }
            $this->users->create(['data' => $insert_array, 'roles' => $request->only('assignees_roles')]);

            return redirect()->route('admin.access.user.index')->withFlashSuccess(trans('alerts.backend.users.created'));
        } catch(Exception $e)
        {
            print_r($e->getMessage());
        }
        
    }

    /**
     * @param User              $user
     * @param ManageUserRequest $request
     *
     * @return mixed
     */
    public function show(User $user, ManageUserRequest $request)
    {
        return view('backend.access.show')
            ->withUser($user);
    }

    /**
     * @param User              $user
     * @param ManageUserRequest $request
     *
     * @return mixed
     */
    public function edit(User $user, ManageUserRequest $request)
    {
        return view('backend.access.edit')
            ->withUser($user)
            ->withUserRoles($user->roles->pluck('id')->all())
            ->withRoles($this->roles->getAll());
    }

    /**
     * @param User              $user
     * @param UpdateUserRequest $request
     *
     * @return mixed
     */
    public function update(User $user, UpdateUserRequest $request)
    {   
        try
        {
            $update_array = [];
            $update_array = $request->except('assignees_roles');
            $image_name ='';
            if ($request->hasFile('profile_pic'))
            {

                $image_name = $request->file('profile_pic')->getClientOriginalName();
                $ext    = explode('.', $image_name);
                $count  = count($ext);
                $picture = date('His').$image_name;
                $request->file('profile_pic')->move(Config::get('deployment.profile_image_upload') , $picture);

                $update_array['profile_pic'] = $picture;

            }
            $this->users->update($user, ['data' => $update_array, 'roles' => $request->only('assignees_roles')]);

            return redirect()->route('admin.access.user.index')->withFlashSuccess(trans('alerts.backend.users.updated'));   
        } catch(Exception $e)
        {
            print_r($e->getMessage());
        }        
    }

    /**
     * @param User              $user
     * @param ManageUserRequest $request
     *
     * @return mixed
     */
    public function destroy(User $user, ManageUserRequest $request)
    {
        try
        {
            $this->users->delete($user);
            return redirect()->route('admin.access.user.deleted')->withFlashSuccess(trans('alerts.backend.users.deleted'));
        } catch(Exception $e)
        {
            print_r($e->getMessage());
        }
        
    }
}
