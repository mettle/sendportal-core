<?php

namespace Sendportal\Base\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Campaign extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'subject' => $this->subject,
            'content' => $this->content,
            'status_id' => $this->status_id,
            'template_id' => $this->template_id,
            'email_service_id' => $this->email_service_id,
            'from_name' => $this->from_name,
            'from_email' => $this->from_email,
            'is_open_tracking' => $this->is_open_tracking,
            'is_click_tracking' => $this->is_click_tracking,
            'sent_count' => $this->sent_count,
            'open_count' => $this->open_count,
            'click_count' => $this->click_count,
            'send_to_all' => $this->send_to_all,
            'segments' => $this->whenLoaded('segments', $this->segments->modelKeys()),
            'save_as_draft' => $this->save_as_draft,
            'scheduled_at' => $this->scheduled_at ? $this->scheduled_at->toDateTimeString() : null,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString()
        ];
    }
}
