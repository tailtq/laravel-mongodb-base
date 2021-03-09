<?php

namespace Modules\Process\Requests;

use Infrastructure\Requests\BaseCRUDRequest;

class CreateProcessRequest extends BaseCRUDRequest
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
            'video_url' => 'string|max:255',
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
            'min_head_confidence' => 'numeric',
            'min_face_confidence' => 'numeric',
            'min_body_confidence' => 'numeric',
            'write_video_step' => 'numeric',
            'write_data_step' => 'numeric',
            'regions' => 'array',
            'thumbnail' => 'required|string',
            'camera_id' => 'numeric',
        ];
    }
}
