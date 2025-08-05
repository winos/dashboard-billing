<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Form;
use App\Models\UserWithdrawMethod;

class EditWithdraw extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */

    public $form;
    public $formData;
    public $userData;
    public $finalData;

    public function __construct($withdrawFormId, $userWithdrawMethodId)
    {   
        $this->form = Form::findOrFail($withdrawFormId);
        $this->formData = $this->form->form_data;
    
        $this->method = UserWithdrawMethod::where('user_type', userGuard()['type'])->where('user_id', userGuard()['user']->id)->findOrFail($userWithdrawMethodId);
        $this->userData = $this->method->user_data;
        
        $this->finalData = [];

        foreach($this->formData as $index => $form){
            foreach($this->userData as $user){ 
                if($form->name == $user->name){
                    $form->value = $user->value;
                }
            }
            $this->finalData[$index] = $form;
        }

    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {   
        return view('components.edit-withdraw');
    }
}
