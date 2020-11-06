<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'video_url' => 'required|string|max:255',
            'description' => 'nullable|string',
            'detection_scale' => 'numeric',
            'frame_drop' => 'numeric',
            'frame_step' => 'numeric',
            'max_pitch' => 'numeric',
            'max_roll' => 'numeric',
            'max_yaw' => 'numeric',
            'min_face_size' => 'numeric',
            'tracking_scale' => 'numeric',
            'biometric_threshold' => 'numeric',
        ];
    }
}
