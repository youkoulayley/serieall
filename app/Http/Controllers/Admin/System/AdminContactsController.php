<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReplyContactRequest;
use App\Notifications\ReplyContactNotification;
use App\Repositories\ContactRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Class AdminContactsController.
 */
class AdminContactsController extends Controller
{
    protected $nbPerPage = 20;

    protected $contactRepository;

    /**
     * AdminContactsController constructor.
     */
    public function __construct(ContactRepository $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }

    /**
     * Renvoi vers la page admin/system/contacts/index.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $contacts = $this->contactRepository->getAllContacts();

        return view('admin/system/contacts/index', compact('contacts'));
    }

    /**
     * Affiche le contenu d'un contact
     * Renvoi vers la page admin/system/contacts/view.
     *
     * @param $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function view($id)
    {
        $contact = $this->contactRepository->getContactByID($id);

        return view('admin/system/contacts/view', compact('contact'));
    }

    /**
     * Reply to a contact request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function replyContact(ReplyContactRequest $replyContactRequest)
    {
        $contact = $this->contactRepository->getContactByID($replyContactRequest->id);

        $contact->admin_id = $replyContactRequest->admin_id;
        $contact->admin_message = $replyContactRequest->admin_message;
        $contact->save();

        // Envoi de la notification
        Notification::route('mail', $contact->email)
            ->notify(new ReplyContactNotification($contact->user['username'], $contact->admin_message));

        return redirect()->back();
    }

    /**
     * Deletion of a contact request.
     *
     * @param $id
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Exception
     *
     * @internal param $id
     */
    public function destroy($id)
    {
        Log::debug('AdminContactDestroy: Start deletion whit id : '.json_encode($id));

        $contact = $this->contactRepository->getContactByID($id);
        Log::info('AdminContactDestroy: Deletion of this object : '.json_encode($contact));

        $contact->delete();

        Log::debug('AdminContactDestroy: End of deletion whit id : '.json_encode($id));

        return redirect()->back()
            ->with('status_header', 'Suppression de la demande de contact')
            ->with('status', 'Demande de contact supprimée.');
    }
}
