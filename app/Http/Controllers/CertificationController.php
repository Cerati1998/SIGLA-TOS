<?php

namespace App\Http\Controllers;

use App\Actions\Certifications\DescribeCertification;
use App\Actions\Certifications\ListPendingInspections;
use App\Enums\CertificationStatus;
use App\Models\Certification;
use App\Models\CertificationPhoto;
use App\Models\User;
use App\Services\PhotoStorage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CertificationController extends Controller
{
    /**
     * The longest side, in px, of each photo in the PDF: three to a row of an A4 page, it still reads when zoomed.
     */
    private const int PDF_PHOTO_SIDE = 1024;

    /**
     * Show the panel of the certifications: the assignment, the internal and external inspections, with how many
     * units each inspection has pending (T58), and the list of assignments, with how many are ready to close.
     */
    public function index(ListPendingInspections $listPendingInspections): View
    {
        return view('pages.certifications.index', [
            'pending' => $listPendingInspections->counts(),
            'readyToClose' => Certification::query()->whereStatus(CertificationStatus::ReadyToClose)->count(),
        ]);
    }

    /**
     * Show the detail of a certification, only to read (H20, criterion 4).
     */
    public function show(Certification $certification): View
    {
        return view('pages.certifications.show', ['certification' => $certification]);
    }

    /**
     * Show the PDF of a closed certification: its header, the devices placed on the drawings of the container and the
     * photos of each section (H20, criteria 5 and 6). Each photo goes shrunk from the whole photo, not from its
     * thumbnail, so it stays sharp when zoomed and the PDF does not carry the photos as they are (T85).
     */
    public function pdf(Request $request, Certification $certification, DescribeCertification $describeCertification, PhotoStorage $photoStorage): Response
    {
        abort_unless($certification->isClosed(), 404);

        /** @var User $user */
        $user = $request->user();

        return Pdf::loadView('pdf.certifications.certification', [
            ...$describeCertification->handle($certification),
            'certification' => $certification,
            'photoData' => fn (CertificationPhoto $photo): string => $photoStorage->resizedDataUri($photo->path, self::PDF_PHOTO_SIDE),
            'generatedBy' => $user->name,
        ])
            ->setPaper('a4')
            ->stream(sprintf('Certificación %s.pdf', $certification->container));
    }
}
