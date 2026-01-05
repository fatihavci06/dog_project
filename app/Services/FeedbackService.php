<?php

namespace App\Services;

use App\Mail\FeedbackCreatedMail;
use App\Models\Feedback;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FeedbackService
{
    public function create(array $data, int $userId): Feedback
    {
        $imagePath = null;

        // 🔥 BASE64 IMAGE SAVE
        if (!empty($data['image'])) {
            $imagePath = $this->storeBase64Image($data['image']);
        }

        $feedback= Feedback::create([
            'user_id'   => $userId,
            'category'  => $data['category'],
            'subject'   => $data['subject'],
            'message'   => $data['message'],

            'rating'    => $data['rating'] ?? null,
            'priority'  => $data['priority'] ?? null,
            'image'     => $imagePath,

            'allow_contact'     => data_get($data, 'contact.allow_contact', false),
            'contact_full_name' => data_get($data, 'contact.full_name'),
            'contact_email'     => data_get($data, 'contact.email'),
        ]);
         Mail::to('admin@pupcrwal.com')
        ->queue(new FeedbackCreatedMail($feedback));
        return $feedback;
    }

    protected function storeBase64Image(string $base64): string
    {
        preg_match('/^data:image\/(\w+);base64,/', $base64, $matches);

        $extension = $matches[1]; // png | jpg | jpeg | webp
        $base64 = substr($base64, strpos($base64, ',') + 1);
        $base64 = base64_decode($base64);

        $fileName = 'feedbacks/' . Str::uuid() . '.' . $extension;

        Storage::disk('public')->put($fileName, $base64);

        return Storage::url($fileName);
    }
    public function listUserFeedbacks(
        int $userId,
        int $page = 1,
        int $perPage = 10
    ): array {

        $query = Feedback::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'current_page' => $paginator->currentPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
            'last_page'    => $paginator->lastPage(),
            'data'         => $paginator->items(),
        ];
    }
}
