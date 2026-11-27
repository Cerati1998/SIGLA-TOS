{{-- One row of up to three photos of a section. Each photo fits a box 170 px tall, so a portrait photo from the phone goes narrower and centered instead of taking half a page (T88). --}}
<table class="sin-corte">
    <tr>
        @foreach ($row as $photo)
            <td style="width: 33.33%; vertical-align: top; padding: 0 5px 10px; text-align: center;">
                <div style="height: 172px;">
                    <img src="{{ $photoData($photo) }}" alt="" style="max-width: 222px; max-height: 170px; border: 1px solid #c9ced6;">
                </div>
                <div style="font-size: 8px; color: #4b5563; margin-top: 2px; text-align: left;">{{ $photo->uploader->name }} · {{ $lima($photo->created_at)?->format('d/m/Y H:i') }}</div>
            </td>
        @endforeach
        @for ($empty = $row->count(); $empty < 3; $empty++)
            <td style="width: 33.33%;"></td>
        @endfor
    </tr>
</table>
