<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactCreateRequest;
use App\Http\Requests\ContactUpdateRequest;
use App\Http\Resources\ContactCollection;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function create(ContactCreateRequest $request): JsonResponse
    {
        $user = Auth::user();
        $data = $request->validated();

        $contact = new Contact($data);
        $contact->user_id = $user->id;
        $contact->save();

        return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    public function get(int $id): ContactResource
    {
        $user = Auth::user();
        $contact = $this->getContact($user, $id);

        return new ContactResource($contact);
    }

    public function update(int $id, ContactUpdateRequest $request): ContactResource
    {
        $user = Auth::user();
        $contact = $this->getContact($user, $id);

        $data = $request->validated();
        $contact->fill($data);
        $contact->save();

        return new ContactResource($contact);
    }

    public function delete(int $id): JsonResponse
    {
        $user = Auth::user();
        $contact = $this->getContact($user, $id);

        $contact->delete();
        return response()->json([
            'data' => true,
        ])->setStatusCode(200);
    }

    public function search(Request $request): ContactCollection
    {
        // Mendapatkan pengguna yang sedang terautentikasi
        $user = Auth::user();

        // Mendapatkan parameter halaman dan ukuran dari request, dengan default nilai 1 untuk halaman dan 10 untuk ukuran
        $page = $request->input('page', 1);
        $size = $request->input('size', 10);

        // Memulai query untuk mendapatkan kontak milik pengguna yang sedang terautentikasi
        $contacts = Contact::query()->where('user_id', $user->id);

        // Menambahkan filter tambahan berdasarkan input yang diterima dari request
        $contacts = $contacts->where(function (Builder $builder) use ($request) {
            // Mencari berdasarkan nama depan atau nama belakang
            $name = $request->input('name');
            if ($name) {
                $builder->where(function (Builder $builder) use ($name) {
                    $builder->orWhere('firstname', 'like', '%' . $name . '%');
                    $builder->orWhere('lastname', 'like', '%' . $name . '%');
                });
            }

            // Mencari berdasarkan email
            $email = $request->input('email');
            if ($email) {
                $builder->where('email', 'like', '%' . $email . '%');
            }

            // Mencari berdasarkan nomor telepon
            $phone = $request->input('phone');
            if ($phone) {
                $builder->where('phone', 'like', '%' . $phone . '%');
            }
        });

        // Melakukan pagination pada hasil query
        $contacts = $contacts->paginate(perPage: $size, page: $page);

        // Mengembalikan hasil pencarian dalam bentuk koleksi kontak
        return new ContactCollection($contacts);
    }

    private function getContact(User $user, int $id): Contact
    {
        $contact = Contact::where('id', $id)->where('user_id', $user->id)->first();

        if (!$contact) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        "not found"
                    ]
                ]
            ])->setStatusCode(404));
        }

        return $contact;
    }
}
