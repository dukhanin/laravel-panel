<?php
namespace Dukhanin\Panel\Controllers\Concerns;

use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

trait ChecksAccess
{
    protected $policy;

    public function initPolicy()
    {
        $this->policy = Gate::getPolicyFor($this->model());
    }

    public function policy()
    {
        if (is_null($this->policy)) {
            $this->initPolicy();
        }

        return $this->policy;
    }

    public function allows($ability, $arguments = [])
    {
        $policy = $this->policy();

        $user = Auth::user();

        if (is_null($policy)) {
            return false;
        }

        if (is_bool($policy)) {
            return $policy;
        }

        $arguments = $this->validatePolicyArguments($arguments);

        if (starts_with($ability, 'group-')) {
            $ability = preg_replace('/^group-/', '', $ability);

            foreach ($arguments as $model) {
                if ($this->denies($ability, $model)) {
                    return false;
                }
            }

            return true;
        }

        if (! is_null($result = $this->callPolicyBefore($policy, $user, $ability, $arguments))) {
            return $result;
        }

        return $this->callPolicyAccessMethod($policy, $user, $ability, $arguments);
    }

    public function denies($ability, $arguments = [])
    {
        return ! $this->allows($ability, $arguments);
    }

    public function authorize($ability, $arguments = [])
    {
        if ($this->denies($ability, $arguments)) {
            throw new AuthorizationException('This action is unauthorized.');
        }
    }

    protected function validatePolicyArguments($arguments)
    {
        if (empty($arguments)) {
            $arguments = [$this->model()];
        }

        if (! is_array($arguments)) {
            $arguments = [$arguments];
        }

        return $arguments;
    }

    protected function callPolicyBefore($policy, $user, $ability, $arguments)
    {
        if (method_exists($policy, 'before')) {
            return $policy->before($user, $ability, ...$arguments);
        }
    }

    protected function callPolicyAccessMethod($policy, $user, $ability, $arguments)
    {
        if (is_callable([$policy, $ability])) {
            return $policy->$ability($user, ...$arguments);
        }

        return false;
    }
}