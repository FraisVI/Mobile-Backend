<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Admin\AdminController;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends ApiController
{
    public function Create(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = (object) $request->all();

        $fb = new Feedback();
        $fb->user_id = $this->session->user_id;
        $fb->message = $data->message;
        $fb->from_user = 1;

        $options = [];
        if ($data->callback == "true") {
            $options['callback'] = true;
        } else if ($data->message == '') {
            return response()->json([
                'message' => 'Невозможно отправить пустое сообщение'
            ], 400);
        }

        if ($request->hasFile('imagefile')) {
            $file = $request->file('imagefile');
            $ext = AdminController::getFileExtension($file->getMimeType());
            $filename = md5_file($file->getPathname()) . $ext;
            $file->storeAs('', $filename, ['disk' => 'private']);

            $options['file'] = $filename;
            $options['filename'] = $file->getClientOriginalName();
        }

        $fb->options = $options;
        $fb->save();

        return response()->json();
    }

    public function Feedbacks(): \Illuminate\Http\JsonResponse
    {
        $lastVisit = $this->counters->getFeedbackLastVisit();
        /** @var Feedback[] $feedbacks */
        $feedbacks = Feedback::where('user_id', $this->session->user_id)->orderBy('created_at', 'ASC')->get();
        foreach ($feedbacks as $f) {
            $f->date = $f->created_at->diffForHumans();
            $f->callback = isset($f->options['callback']);

            if ($lastVisit) {
                if ($f->created_at > $lastVisit) {
                    $f->new = true;
                }
            }
        }

        $this->counters->resetForFeedback();

        return response()->json($feedbacks, 200, [], JSON_UNESCAPED_UNICODE);
    }
}
